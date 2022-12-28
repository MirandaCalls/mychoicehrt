<?php

namespace App\Geonames;

use App\Entity\GeoCity;
use Doctrine\ORM\EntityManagerInterface;

class CitiesImporter extends GeonamesImporterAbstract
{
    public const GEONAMES_CITY_DATASET = 'dump/cities500.zip';
    public const COLUMNS = [
        'geonameId',
        'name',
        'asciiName',
        'alternateNames',
        'latitude',
        'longitude',
        'featureClass',
        'featureCode',
        'countryCode',
        'cc2',
        'admin1Code',
        'admin2Code',
        'admin3Code',
        'admin4Code',
        'population',
        'elevation',
        'dem',
        'timezone',
        'modificationDate'
    ];

    protected function setDataset(): void
    {
        $this->dataset = self::GEONAMES_CITY_DATASET;
    }

    protected function handleData(string $datasetVersion, array $data, EntityManagerInterface $entityManager): void
    {
        $columnGuide = array_flip(self::COLUMNS);
        $city = new GeoCity();
        $city->setName($data[$columnGuide['name']]);
        $city->setAsciiName($data[$columnGuide['asciiName']]);
        $city->setAlternateNames($data[$columnGuide['alternateNames']]);
        $city->setLatitude($data[$columnGuide['latitude']]);
        $city->setLongitude($data[$columnGuide['longitude']]);
        $city->setCountryCode($data[$columnGuide['countryCode']]);
        $city->setDatasetVersion($datasetVersion);
        $entityManager->persist($city);
    }
}
