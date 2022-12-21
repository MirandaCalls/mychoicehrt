<?php

namespace App\MessageHandler;

use App\Message\FindDuplicatesMessage;
use App\Repository\ClinicRepository;
use Location\Coordinate;
use Location\Distance\Vincenty;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FindDuplicatesMessageHandler
{
    public const MILES_THRESHOLD = 0.5;
    public const METERS_PER_MILE = 1609.344;

    private ClinicRepository $clinics;

    public function __construct(ClinicRepository $clinics)
    {
        $this->clinics = $clinics;
    }

    public function __invoke(FindDuplicatesMessage $message)
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


    }
}