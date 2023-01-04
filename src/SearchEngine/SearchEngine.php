<?php

namespace App\SearchEngine;

use App\Geonames\Geocoder;
use App\Geonames\SearchResult;
use App\Repository\ClinicRepository;

class SearchEngine
{
    public const RECORDS_LIMIT = 10;

    private ClinicRepository $clinics;
    private Geocoder $geocoder;

    public function __construct(
        ClinicRepository $clinics,
        Geocoder $geocoder,
    ) {
        $this->clinics = $clinics;
        $this->geocoder = $geocoder;
    }

    public function search(SearchEngineParams $params): SearchEngineResults
    {
        $this->geocoder->setCountry($params->getCountryCode());

        if (($location = $params->getLocation()) !== null) {
            $locationResults = [
                new SearchResult(
                    $location['title'] ?? 'Manually Entered Coordinates',
                    $location['latitude'] ?? 0,
                    $location['longitude'] ?? 0,
                )
            ];
        } elseif ($params->getSearchType() === SearchEngineParams::SEARCH_TYPE_POSTAL) {
            $locationResults = $this->geocoder->searchPostalCodes($params->getSearchText());
        } elseif ($params->getSearchType() === SearchEngineParams::SEARCH_TYPE_CITY) {
            $locationResults = $this->geocoder->searchCities($params->getSearchText());
        }

        if (empty($locationResults)) {
            return new SearchEngineResults(
                results: [],
                matchedLocation: null,
                searchRadius: 0,
                totalResults: 0,
                totalPages: 0,
            );
        }

        $matchedLocation = $locationResults[0];
        $title = $matchedLocation->title;
        $latitude = $matchedLocation->latitude;
        $longitude = $matchedLocation->longitude;
        $radius = $params->getSearchRadius();
        if ($radius === null) {
            list($radius, $totalResults) = $this->calculateOptimalSearchRadius($latitude, $longitude);
        } else {
            $totalResults = $this->clinics->countClinicsWithinRadius($latitude, $longitude, $radius);
        }

        $page = $params->getPage();
        $maxPages = ceil($totalResults/self::RECORDS_LIMIT);

        $page = max(1, $page);
        $page = min($page, $maxPages);
        $offset = ($page - 1) * self::RECORDS_LIMIT;
        $clinics = $this->clinics->findClinicsWithinRadius(
            $latitude,
            $longitude,
            $radius,
            limit: self::RECORDS_LIMIT,
            offset: $offset,
        );

        return new SearchEngineResults(
            results: $clinics,
            matchedLocation: [
                'title' => $title,
                'coordinates' => $latitude . ',' . $longitude,
            ],
            searchRadius: $radius,
            totalResults: $totalResults,
            currentPage: $page,
            totalPages: $maxPages,
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