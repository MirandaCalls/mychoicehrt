<?php

namespace App\Geonames;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeonamesDatasetHandler
{
    public const DOWNLOAD_DIR = '/var/geonames';

    private HttpClientInterface $client;
    private string $workingDirectory;

    private string $datasetFilepath = '';

    public function __construct(
        HttpClientInterface $client,
        string $projectDir,
    ) {
        $this->client = $client;
        $this->workingDirectory = $projectDir . self::DOWNLOAD_DIR;
    }

    /**
     * @throws \Exception
     */
    public function download(string $fileUrl, bool $zipped): void
    {
        if (!file_exists($this->workingDirectory)) {
            mkdir($this->workingDirectory);
        }

        $filename = basename($fileUrl);
        $datasetFilepath = $this->workingDirectory . '/' . $filename;
        $this->downloadDataset($fileUrl, $datasetFilepath);

        if ($zipped === true) {
            $this->unzipDataset($datasetFilepath);
            $filename = str_replace('.zip', '.txt', $filename);
        }

        $this->datasetFilepath = $this->workingDirectory . '/' . $filename;
    }

    /**
     * @throws \Exception
     */
    public function processData(callable $handler): int
    {
        if ($this->datasetFilepath === '') {
            throw new \Exception('No dataset loaded!');
        }

        $handle = fopen($this->datasetFilepath, 'r');
        $recordCount = 0;
        while(!feof($handle)) {
            $row = fgets($handle);
            if ($row === false) {
                continue;
            }

            $record = explode("\t", $row);
            $handler($record, $recordCount);
            $recordCount++;
        }
        fclose($handle);

        return $recordCount;
    }

    /**
     * @throws \Exception
     */
    private function downloadDataset(string $downloadUrl, string $destination): void
    {
        $downloadHandle = fopen($destination, mode: 'w');
        if ($downloadHandle === false) {
            throw new \Exception('Unable to create file for writing');
        }

        try {
            $res = $this->client->request('GET', $downloadUrl);
            if (200 !== $statusCode = $res->getStatusCode()) {
                throw new \Exception('Geonames returned failing status code: ' . $statusCode);
            }

            foreach ($this->client->stream($res) as $chunk) {
                fwrite($downloadHandle, $chunk->getContent());
            }
        } catch (\Throwable) {
            throw new \Exception('Failed to download file: ' . $downloadUrl);
        }

        fclose($downloadHandle);
    }

    /**
     * @throws \Exception
     */
    private function unzipDataset(string $zipFilepath): void
    {
        $extractedPath = $this->workingDirectory;
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