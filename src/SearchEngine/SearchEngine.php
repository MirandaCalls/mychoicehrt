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
                currentPage: 0,
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
            $totalResults = $this->clinics->countClinicsWithinRadius(
                $latitude,
                $longitude,
                $radius,
                published: true,
            );
        }

        $page = $params->getPage();
        $maxPages = ceil($totalResults / self::RECORDS_LIMIT);

        $page = max(1, $page);
        $page = min($page, $maxPages);

        $clinics = [];
        if ($totalResults > 0) {
            $offset = ($page - 1) * self::RECORDS_LIMIT;
            $clinics = $this->clinics->findClinicsWithinRadius(
                $latitude,
                $longitude,
                $radius,
                limit: self::RECORDS_LIMIT,
                offset: $offset,
                published: true,
            );
        }

        return new SearchEngineResults(
            results: $clinics,
            matchedLocation: [
                'title' => $title,
                'latitude' => $latitude,
                'longitude' => $longitude,
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
        while ($count < self::RECORDS_LIMIT) {
            if ($radius >= 500) {
                break;
            }

            $radius = min($radius * 2, 500);
            $count = $this->clinics->countClinicsWithinRadius(
                $latitude,
                $longitude,
                $radius,
                published: true,
            );
        }

        return [$radius, $count];
    }
}
