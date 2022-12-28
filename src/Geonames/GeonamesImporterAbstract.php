<?php

namespace App\Geonames;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class GeonamesImporterAbstract
{
    public const GEONAMES_EXPORT_URL = 'https://download.geonames.org/export';
    public const DOWNLOAD_DIR = '/var/geonames';

    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private string $workingDirectory;

    protected string $dataset = '';
    protected string $datasetName = '';

    public function __construct(
        HttpClientInterface $client,
        EntityManagerInterface $entityManager,
        string $projectDir,
    ) {
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->workingDirectory = $projectDir . self::DOWNLOAD_DIR;
    }

    public function import(): int
    {
        $version = uniqid();
        if (!file_exists($this->workingDirectory)) {
            mkdir($this->workingDirectory);
        }

        $this->setDataset();
        $zipName = basename($this->dataset);
        $this->datasetName = str_replace('.zip', '', $zipName);

        $zipFilepath = $this->workingDirectory . '/' . $zipName;
        $this->downloadDataset($zipFilepath);
        $this->unzipDataset($zipFilepath);

        $datasetFilepath = $this->workingDirectory . '/' . $this->datasetName . '/' . $this->datasetName . '.txt';
        $handle = fopen($datasetFilepath, 'r');
        $totalCount = 0;
        $batchCount = 0;
        while(!feof($handle)) {
            if ($batchCount === 500) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $batchCount = 0;
            }

            $row = fgets($handle);
            if ($row === false) {
                continue;
            }

            $contents = explode("\t", $row);
            $this->handleData($version, $contents, $this->entityManager);
            $totalCount++;
            $batchCount++;
        }
        $this->entityManager->flush();
        fclose($handle);

        return $totalCount;
    }

    /**
     * Implementation should set the $dataset property to one of the GEONAMES_*_DATASET values.
     */
    abstract protected function setDataset(): void;

    abstract protected function handleData(string $datasetVersion, array $data, EntityManagerInterface $entityManager): void;

    /**
     * @throws \Exception
     */
    private function downloadDataset(string $zipFilepath)
    {
        $zipFileHandle = fopen($zipFilepath, mode: 'w');
        if ($zipFileHandle === false) {
            throw new \Exception('Unable to create zip file for writing');
        }

        $downloadUrl = self::GEONAMES_EXPORT_URL . '/' . $this->dataset;
        try {
            $res = $this->client->request('GET', $downloadUrl);
            if (200 !== $statusCode = $res->getStatusCode()) {
                throw new \Exception('Geonames returned failing status code: ' . $statusCode);
            }

            foreach ($this->client->stream($res) as $chunk) {
                fwrite($zipFileHandle, $chunk->getContent());
            }
        } catch (\Throwable) {
            throw new \Exception('Failed to download file: ' . $downloadUrl);
        }

        fclose($zipFileHandle);
    }

    /**
     * @throws \Exception
     */
    private function unzipDataset(string $zipFilepath)
    {
        $extractedPath = $this->workingDirectory . '/' . $this->datasetName;

        $zip = new \ZipArchive();
        $res = $zip->open($zipFilepath);
        if (true === $res) {
            $zip->extractTo($extractedPath);
            $zip->close();
        } else {
            throw new \Exception('Failed to unzip ' . $zipFilepath);
        }
    }

}
