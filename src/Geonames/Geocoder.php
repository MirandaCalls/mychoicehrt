<?php

namespace App\Geonames;

use App\Entity\GeoCity;
use App\Entity\GeoPostalCode;
use App\Repository\GeoCityRepository;
use App\Repository\GeoPostalCodeRepository;

class Geocoder
{
    private GeoCityRepository $cities;
    private GeoPostalCodeRepository $postalCodes;
    private string $countryCode = 'US';

    public function __construct(
        GeoCityRepository $cities,
        GeoPostalCodeRepository $postalCodes,
    ) {
        $this->cities = $cities;
        $this->postalCodes = $postalCodes;
    }

    public function setCountry(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return SearchResult[]
     */
    public function searchCities(string $searchText): array
    {
        $cities = $this->cities->search($this->countryCode, $searchText);
        return $this->convertToResults($cities);
    }

    /**
     * @return SearchResult[]
     */
    public function searchPostalCodes(string $searchText): array
    {
        $postalCodes = $this->postalCodes->search($this->countryCode, $searchText);
        return $this->convertToResults($postalCodes);
    }

    /**
     * @param GeoCity[]|GeoPostalCode[] $records
     * @return SearchResult[]
     */
    private function convertToResults(array $records): array
    {
        return array_map(function($record) {
            return new SearchResult($record->toString(), $record->getLatitude(), $record->getLongitude());
        }, $records);
    }

}