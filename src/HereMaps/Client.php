<?php

namespace App\HereMaps;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client {

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
            'at' => $latitude . ',' . $longitude,
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
}