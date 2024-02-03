<?php

namespace App\DataSource;

use App\Entity\Clinic;
use App\HereMaps\Client as HereClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ErinReedDataSource implements DataSourceInterface
{
    private const MAPS_URL = 'https://www.google.com/maps/d/u/0/kml?mid=1DxyOTw8dI8n96BHFF2JVUMK7bXsRKtzA&lid=YJYSVVdTdb8&forcekml=1';

    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        HereClient $hereClient,
    ) {
        $this->httpClient = $httpClient;
    }

    public function getType(): string
    {
        return self::DATASOURCE__ERIN_REED;
    }

    /**
     * @throws DataSourceException
     */
    public function fetchClinics(): array
    {
        $kml = $this->loadGoogleMapsKml();
        $rawRecords = $this->processKml($kml);

        $clinics = [];
        foreach ($rawRecords as $record) {
            $coords = explode(',', $record['coords']);
            $clinic = new Clinic();
            $clinic->setName($record['name']);
            $clinic->setDescription($record['description']);
            $clinic->setLatitude($coords[1]);
            $clinic->setLongitude($coords[0]);
            $clinic->setDataSource($this->getType());
            $clinic->setPublished(false);
            $clinics[] = $clinic;
        }

        return $clinics;
    }

    /**
     * @throws DataSourceException
     */
    private function loadGoogleMapsKml(): string
    {
        try {
            $res = $this->httpClient->request('GET', self::MAPS_URL);
            $data = $res->getContent();
        } catch (\Throwable) {
            throw new DataSourceException('HTTP request to fetch clinics failed.', $this->getType());
        }

        if ($data === '') {
            throw new DataSourceException('Missing clinics KML in response.', $this->getType());
        }

        return $data;
    }

    /**
     * @throws DataSourceException
     */
    private function processKml(string $kml): array
    {
        $rawRecords = [];
        $document = simplexml_load_string($kml);
        foreach ($document->Document->Placemark as $location) {
            if (empty($location->name)) {
                throw new DataSourceException('Missing clinic attribute in kml data: name', $this->getType());
            } elseif (empty($location->Point->coordinates)) {
                throw new DataSourceException('Missing attribute in kml data: coordinates', $this->getType());
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
        $data = implode('.', $pieces);
        return md5($data);
    }

    public function preImport(Clinic $clinic): void
    {
        // left intentionally blank
    }
}
