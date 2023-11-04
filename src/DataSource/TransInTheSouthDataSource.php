<?php

namespace App\DataSource;

use App\Entity\Clinic;
use App\HereMaps\Client as HereClient;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TransInTheSouthDataSource implements DataSourceInterface
{
    private HttpClientInterface $httpClient;
    private HereClient $hereClient;

    public function __construct(
        HttpClientInterface $httpClient,
        HereClient $hereClient,
    ) {
        $this->httpClient = $httpClient;
        $this->hereClient = $hereClient;
    }

    public function getType(): string
    {
        return self::DATASOURCE__TRANS_IN_THE_SOUTH;
    }

    /**
     * @throws DataSourceException
     */
    public function fetchClinics(): array
    {
        $tisProvidersId = $this->_scrapeTisProvidersId();
        $html = $this->_loadSearchResults($tisProvidersId);
        $rawRecords = $this->_scrapeData($html);

        $clinics = [];
        foreach ($rawRecords as $record) {
            $clinic = new Clinic();
            $clinic->setName($record['name']);
            $clinic->setDescription($record['description']);
            $clinic->setAddress($record['address']);
            $clinic->setDataSource($this->getType());
            $clinic->setPublished(false);
            $clinics[] = $clinic;
        }

        return $clinics;
    }

    private function _scrapeTisProvidersId(): string
    {
        try {
            $res = $this->httpClient->request('GET', 'https://southernequality.org/resources/transinthesouth/');
            $html = $res->getContent();
        } catch (\Throwable) {
            throw new DataSourceException('HTTP request to fetch search form failed.', $this->getType());
        }

        if ($html === '') {
            throw new DataSourceException('Missing web page content in response.', $this->getType());
        }

        $crawler = new Crawler($html);
        return $crawler->filter('#filter-tis-providers')->first()->attr('value');
    }

    /**
     * @throws DataSourceException
     */
    private function _loadSearchResults(string $tisProvidersId): string
    {
        try {
            $res = $this->httpClient->request('POST', 'https://southernequality.org/resources/transinthesouth/', [
                'body' => 'tis-name-search=&states=&services%5B%5D=Informed+Consent&services%5B%5D=Offers+Hormone+Replacement+Therapy+%28HRT%29&filter-tis-providers=' . $tisProvidersId . '&_wp_http_referer=%2Fresources%2Ftransinthesouth%2F&filter_providers=Search',
            ]);
            $html = $res->getContent();
        } catch (\Throwable) {
            throw new DataSourceException('HTTP request to fetch search results failed.', $this->getType());
        }

        if ($html === '') {
            throw new DataSourceException('Missing web page content in response.', $this->getType());
        }

        return $html;
    }

    /**
     * @throws DataSourceException
     */
    private function _scrapeData(string $html): array
    {
        $rawRecords = [];

        $crawler = new Crawler($html);
        $crawler->filter('.provider')->each(function (Crawler $crawler) use (&$rawRecords) {
            $services = [];
            foreach ($crawler->filter('.accordion-header') as $service) {
                $services[] = $service->textContent;
            }

            if (!in_array('Offers Hormone Replacement Therapy (HRT)', $services) || !in_array('Informed Consent', $services)) {
                return;
            }

            $name = $crawler->filter('.provider--title')->text();
            if (!$name) {
                throw new DataSourceException('Missing clinic attribute in scraped data: name', $this->getType());
            }

            $practice = $crawler->filter('.provider--practice-name')->text();
            if ($practice && ($name !== $practice)) {
                $name .= ' - ' . $practice;
            }

            $description = $crawler->filter('.provider--summary')->text();

            $address = $crawler->filter('.provider--address')->text();
            if (!$address) {
                throw new DataSourceException('Missing clinic attribute in scraped data: address', $this->getType());
            }

            $rawRecords[] = [
                'name' => $name,
                'description' => $description,
                'address' => $address,
            ];
        });

        return $rawRecords;
    }

    public function hash(Clinic $clinic): string
    {
        $pieces = [
            $clinic->getName(),
            $clinic->getDescription(),
            $clinic->getAddress(),
        ];
        $data = implode('.', $pieces);
        return md5($data);
    }

    /**
     * @throws \Exception
     * @throws DataSourceException
     */
    public function preImport(Clinic $clinic): void
    {
        $items = $this->hereClient->geocode($clinic->getAddress())['items'];
        if (count($items) === 0) {
            throw new DataSourceException('No coordinates found for address: ' . $clinic->getAddress(), $this->getType());
        }

        $clinic->setLatitude($items[0]['position']['lat']);
        $clinic->setLongitude($items[0]['position']['lng']);
    }
}
