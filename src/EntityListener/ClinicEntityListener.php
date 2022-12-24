<?php

namespace App\EntityListener;

use App\Entity\Clinic;
use App\Repository\DuplicateLinkRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ClinicEntityListener
{
    private DuplicateLinkRepository $duplicates;

    public function __construct(DuplicateLinkRepository $duplicates)
    {
        $this->duplicates = $duplicates;
    }

    public function preRemove(Clinic $clinic, LifecycleEventArgs $event)
    {
        $this->duplicates->deleteForClinicId($clinic->getId());
    }
}