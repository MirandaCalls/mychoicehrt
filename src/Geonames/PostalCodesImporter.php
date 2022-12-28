<?php

namespace App\Geonames;

use App\Entity\GeoPostalCode;
use Doctrine\ORM\EntityManagerInterface;

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

    protected function setDataset(): void
    {
        $this->dataset = self::GEONAMES_ZIPCODE_DATASET;
    }

    protected function handleData(string $datasetVersion, array $data, EntityManagerInterface $entityManager): void
    {
        $columnGuide = array_flip(self::COLUMNS);
        $postalCode = new GeoPostalCode();
        $postalCode->setCountryCode($data[$columnGuide['countryCode']]);
        $postalCode->setPostalCode($data[$columnGuide['postalCode']]);
        $postalCode->setPlaceName($data[$columnGuide['placeName']]);
        $postalCode->setState($data[$columnGuide['state']]);
        $postalCode->setLatitude($data[$columnGuide['latitude']]);
        $postalCode->setLongitude($data[$columnGuide['longitude']]);
        $postalCode->setDatasetVersion($datasetVersion);
        $entityManager->persist($postalCode);
    }
}