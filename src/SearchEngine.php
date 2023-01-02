<?php

namespace App;

use App\Geonames\Geocoder;
use App\Repository\ClinicRepository;

class SearchEngine
{
    public const RECORDS_LIMIT = 10;
    public const SEARCH_TYPE_CITY = 'city';
    public const SEARCH_TYPE_POSTAL = 'postal';

    private ClinicRepository $clinics;
    private Geocoder $geocoder;

    public function __construct(
        ClinicRepository $clinics,
        Geocoder $geocoder,
    ) {
        $this->clinics = $clinics;
        $this->geocoder = $geocoder;
    }

    public function search(string $searchText, string $searchType, SearchEngineParams $params): SearchEngineResults
    {
        if ($searchType === self::SEARCH_TYPE_POSTAL) {
            $locationResults = $this->geocoder->searchPostalCodes($searchText);
        } elseif ($searchType === self::SEARCH_TYPE_CITY) {
            $locationResults = $this->geocoder->searchCities($searchText);
        } else {
            throw new \Exception('Unsupported search type!');
        }

        if (empty($locationResults)) {
            return new SearchEngineResults(
                0, 0, [], 0, ''
            );
        }

        $matchedLocation = $locationResults[0];
        $latitude = $matchedLocation->latitude;
        $longitude = $matchedLocation->longitude;
        $radius = $params->getSearchRadius();
        if ($radius === null) {
            list($radius, $totalResults) = $this->calculateOptimalSearchRadius($latitude, $longitude);
        } else {
            $totalResults = $this->clinics->countClinicsWithinRadius($latitude, $longitude, $radius);
        }

        $page = $params->getPage();
        $offset = ($page - 1) * self::RECORDS_LIMIT;
        $clinics = $this->clinics->findClinicsWithinRadius(
            $latitude,
            $longitude,
            $radius,
            limit: self::RECORDS_LIMIT,
            offset: $offset,
        );

        return new SearchEngineResults(
            totalResults: $totalResults,
            totalPages: ceil($totalResults / self::RECORDS_LIMIT),
            results: $clinics,
            searchRadius: $radius,
            matchedLocation: $matchedLocation->title
        );
    }

    /**
     * Attempts to find a search radius that will allow us to show at least 10 records to the user.
     */
    private function calculateOptimalSearchRadius(float $latitude, float $longitude): array
    {
        $radius = 5;
        $count = 0;
        $attempts = 0;
        while($count < self::RECORDS_LIMIT) {
            if ($attempts === 5) {
                // Double the search radius 5 times before giving up
                break;
            }

            $radius = $radius * 2;
            $count = $this->clinics->countClinicsWithinRadius(
                $latitude,
                $longitude,
                $radius
            );
            $attempts++;
        }

        return [$radius, $count];
    }
}