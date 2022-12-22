<?php

namespace App\MessageHandler;

use App\Entity\DuplicateLink;
use App\Message\FindDuplicatesMessage;
use App\Repository\ClinicRepository;
use App\Repository\DuplicateLinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Location\Coordinate;
use Location\Distance\Vincenty;
use Oefenweb\DamerauLevenshtein\DamerauLevenshtein as Levenshtein;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FindDuplicatesMessageHandler
{
    public const MILES_THRESHOLD = 0.5;
    public const SIMILARITY_THRESHOLD = 0.6;
    public const METERS_PER_MILE = 1609.344;

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

        $nearbyClinics = $this->clinics->filterAllWithCallable(function($clinic) use ($origin) {
            $originCoords = new Coordinate($origin->getLatitude(), $origin->getLongitude());
            $coords = new Coordinate($clinic->getLatitude(), $clinic->getLongitude());

            $vincenty = new Vincenty();
            $distance = $vincenty->getDistance($originCoords, $coords);
            return $distance < (self::MILES_THRESHOLD * self::METERS_PER_MILE);
        });

        foreach ($nearbyClinics as $clinic) {
            if ($origin->getId() === $clinic->getId()) {
                // We didn't originally filter out the origin from the db load since that would have detached the entity
                //    ->filterAllWithCallable() detaches all entities it doesn't return
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