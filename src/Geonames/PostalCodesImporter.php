<?php

namespace App\Geonames;

use App\Entity\GeoPostalCode;

class PostalCodesImporter extends GeonamesImporterAbstract
{
    public const GEONAMES_ZIPCODE_DATASET = 'zip/allCountries.zip';
    public const COLUMNS = [
        'countryCode',
        'postalCode',
        'placeName',
        'state',
        'stateCode',
        'countyProvince',
        'countyProvinceCode',
        'community',
        'communityCode',
        'latitude',
        'longitude',
        'accuracy',
    ];

    protected function configure(): void
    {
        $this->datasetUrl = self::GEONAMES_EXPORT_URL . '/' . self::GEONAMES_ZIPCODE_DATASET;
    }

    protected function handleData(array $data): void
    {
        $columnGuide = array_flip(self::COLUMNS);
        $postalCode = new GeoPostalCode();
        $postalCode->setCountryCode($data[$columnGuide['countryCode']]);
        $postalCode->setPostalCode($data[$columnGuide['postalCode']]);
        $postalCode->setPlaceName($data[$columnGuide['placeName']]);
        $postalCode->setState($data[$columnGuide['state']]);
        $postalCode->setLatitude($data[$columnGuide['latitude']]);
        $postalCode->setLongitude($data[$columnGuide['longitude']]);
        $postalCode->setDatasetVersion($this->importVersion);
        $this->entityManager->persist($postalCode);
    }
}