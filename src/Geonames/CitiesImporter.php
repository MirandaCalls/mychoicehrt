<?php

namespace App\Geonames;

use App\Entity\GeoCity;

class CitiesImporter extends GeonamesImporterAbstract
{
    public const GEONAMES_CITY_DATASET = 'dump/cities500.zip';
    //public const ADMIN_1_CODES_DATASET = 'dump/admin1CodesASCII.txt';
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

    protected function configure(): void
    {
        $this->datasetUrl = self::GEONAMES_EXPORT_URL . '/' . self::GEONAMES_CITY_DATASET;
    }

    protected function handleData(array $data): void
    {
        $columnGuide = array_flip(self::COLUMNS);
        $city = new GeoCity();
        $city->setName($data[$columnGuide['name']]);
        $city->setAsciiName($data[$columnGuide['asciiName']]);
        $city->setAlternateNames($data[$columnGuide['alternateNames']]);
        $city->setLatitude($data[$columnGuide['latitude']]);
        $city->setLongitude($data[$columnGuide['longitude']]);
        $city->setCountryCode($data[$columnGuide['countryCode']]);
        $city->setDatasetVersion($this->importVersion);
        $this->entityManager->persist($city);
    }
}
