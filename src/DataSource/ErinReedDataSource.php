<?php

namespace App\DataSource;

use App\Entity\Clinic;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ErinReedDataSource implements DataSourceInterface
{
    private const MAPS_URL = 'https://www.google.com/maps/d/u/0/kml?mid=1DxyOTw8dI8n96BHFF2JVUMK7bXsRKtzA&lid=YJYSVVdTdb8&forcekml=1';

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getType(): string
    {
        return self::DATASOURCE__ERIN_REED;
    }

    public function fetchClinics(): array
    {
        $kml = $this->loadGoogleMapsKml();
        $rawRecords = $this->processKml($kml);

        $clinics = [];
        foreach($rawRecords as $record) {
            $coords = explode(',', $record['coords']);
            $clinic = new Clinic();
            $clinic->setName($record['name']);
            $clinic->setDescription($record['description']);
            $clinic->setLatitude($coords[1]);
            $clinic->setLongitude($coords[0]);
            $clinics[] = $clinic;
        }

        return $clinics;
    }

    private function loadGoogleMapsKml(): string
    {
        try {
            $res = $this->httpClient->request( 'GET', self::MAPS_URL );
            $data = $res->getContent();
        } catch (\Exception $e) {
            throw new DataSourceException('HTTP request to fetch clinics failed.');
        }

        if ($data === '') {
            throw new DataSourceException('Missing clinics KML in response.');
        }

        return $data;
    }

    private function processKml(string $kml): array
    {
        $rawRecords = [];
        $document = simplexml_load_string($kml);
        foreach ($document->Document->Placemark as $location) {
            if (empty($location->name)) {
                throw new \Exception('Missing clinic attribute in kml data: name');
            } elseif (empty($location->Point->coordinates)) {
                throw new \Exception('Missing attribute in kml data: coordinates');
            }

            $description = '';
            if (!empty($location->description)) {
                $description = (string) $location->description;
            }

            $rawRecords[] = [
                'name' => trim((string) $location->name),
                'description' => trim($description),
                'coords' => trim((string) $location->Point->coordinates),
            ];
        }
        return $rawRecords;
    }

    public function hash(Clinic $clinic): string
    {
        $pieces = [
            $clinic->getName(),
            $clinic->getDescription(),
            $clinic->getLatitude(),
            $clinic->getLongitude()
        ];
        $data = implode( '.', $pieces );
        return md5( $data );
    }
}