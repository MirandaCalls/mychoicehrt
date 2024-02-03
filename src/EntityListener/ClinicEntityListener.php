<?php

namespace App\EntityListener;

use App\Entity\Clinic;
use App\Message\FindDuplicatesMessage;
use App\Repository\DuplicateLinkRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

class ClinicEntityListener
{
    private MessageBusInterface $bus;
    private DuplicateLinkRepository $duplicates;

    public function __construct(
        MessageBusInterface $bus,
        DuplicateLinkRepository $duplicates,
    ) {
        $this->bus = $bus;
        $this->duplicates = $duplicates;
    }

    public function postPersist(Clinic $clinic, LifecycleEventArgs $args): void
    {
        $this->bus->dispatch(new FindDuplicatesMessage($clinic->getId()));
    }

    public function postUpdate(Clinic $clinic, LifecycleEventArgs $args): void
    {
        $this->bus->dispatch(new FindDuplicatesMessage($clinic->getId()));
    }

    public function preRemove(Clinic $clinic, LifecycleEventArgs $args): void
    {
        $this->duplicates->deleteForClinicId($clinic->getId());
    }
}
