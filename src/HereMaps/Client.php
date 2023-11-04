<?php

namespace App\HereMaps;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    public const METERS_PER_MILE = 1609.344;

    private HttpClientInterface $httpClient;
    private string $hereApiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter] string $hereApiKey,
    ) {
        $this->httpClient = $httpClient;
        $this->hereApiKey = $hereApiKey;
    }

    /**
     * @throws \Exception
     */
    public function discover(string $query, float $latitude, float $longitude): array
    {
        $url = 'https://discover.search.hereapi.com/v1/discover?' . http_build_query([
            'lang' => 'en-US',
            'limit' => '1',
            'in' => 'circle:' . $latitude . ',' . $longitude . ';r=' . (int) (self::METERS_PER_MILE * 0.5),
            'q' => $query,
            'apiKey' => $this->hereApiKey,
        ]);

        try {
            $res = $this->httpClient->request('GET', $url);
            $data = $res->getContent();
        } catch (\Throwable $e) {
            throw new \Exception('HTTP request to Here Maps failed: ' . $e->getMessage());
        }

        $decoded = json_decode($data, true);
        if ($decoded === false) {
            throw new \Exception('Failed to decode Here Maps response');
        }

        return $decoded;
    }

    /**
     * @throws \Exception
     */
    public function geocode(string $address): array
    {
        $url = 'https://geocode.search.hereapi.com/v1/geocode?' . http_build_query([
            'lang' => 'en-US',
            'q' => $address,
            'apiKey' => $this->hereApiKey,
        ]);

        try {
            $res = $this->httpClient->request('GET', $url);
            $data = $res->getContent();
        } catch (\Throwable $e) {
            throw new \Exception('HTTP request to Here Maps failed: ' . $e->getMessage());
        }

        $decoded = json_decode($data, true);
        if ($decoded === false) {
            throw new \Exception('Failed to decode Here Maps response');
        }

        return $decoded;
    }
}
