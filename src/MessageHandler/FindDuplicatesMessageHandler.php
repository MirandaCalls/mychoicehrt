<?php

namespace App\MessageHandler;

use App\Entity\DuplicateLink;
use App\Message\FindDuplicatesMessage;
use App\Repository\ClinicRepository;
use App\Repository\DuplicateLinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oefenweb\DamerauLevenshtein\DamerauLevenshtein as Levenshtein;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FindDuplicatesMessageHandler
{
    public const SIMILARITY_THRESHOLD = 0.6;

    private ClinicRepository $clinics;
    private DuplicateLinkRepository $duplicates;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ClinicRepository $clinics,
        DuplicateLinkRepository $duplicates,
        EntityManagerInterface $entityManager,
    ) {
        $this->clinics = $clinics;
        $this->duplicates = $duplicates;
        $this->entityManager = $entityManager;
    }

    public function __invoke(FindDuplicatesMessage $message): void
    {
        $origin = $this->clinics->find($message->getClinicId());
        if ($origin === null) {
            // The record may be been deleted in the admin interface
            return;
        }

        $nearbyClinics = $this->clinics->findClinicsNearby(to: $origin);
        foreach ($nearbyClinics as $clinic) {
            if ($origin->getId() === $clinic->getId()) {
                continue;
            }

            $levenshtein = new Levenshtein($origin->getName(), $clinic->getName());
            if (($similarity = $levenshtein->getRelativeDistance()) < self::SIMILARITY_THRESHOLD) {
                continue;
            }

            $existingDup = $this->duplicates->findForClinicPair($origin, $clinic);
            if ($existingDup !== null) {
                // This duplicate has already been recorded
                continue;
            }

            $duplicate = new DuplicateLink();
            $duplicate->setClinicA($origin);
            $duplicate->setClinicB($clinic);
            $duplicate->setSimilarity($similarity);
            $duplicate->setDismissed(false);
            $this->duplicates->save($duplicate);
        }

        $this->entityManager->flush();
    }
}
