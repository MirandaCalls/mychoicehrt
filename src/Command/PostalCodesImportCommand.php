<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:postal-codes:import',
    description: 'Imports the postal codes dataset from geonames.org',
)]
class PostalCodesImportCommand extends Command
{
    public const GEONAMES_EXPORT_URL = 'https://download.geonames.org/export';
    public const GEONAMES_CITY_DATASET = 'dump/cities500.zip';
    public const GEONAMES_ZIPCODE_DATASET = 'zip/allCountries.zip';

    public const DOWNLOAD_DIR = '/var/geonames';

    private HttpClientInterface $client;
    private string $workingDirectory;

    public function __construct(
        HttpClientInterface $client,
        string $projectDir,
    ) {
        $this->client = $client;
        $this->workingDirectory = $projectDir . self::DOWNLOAD_DIR;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!file_exists($this->workingDirectory)) {
            mkdir($this->workingDirectory);
        }

        $citiesFilepath = $this->getGeonamesDataset(self::GEONAMES_CITY_DATASET);
        $postalCodesFilepath = $this->getGeonamesDataset(self::GEONAMES_ZIPCODE_DATASET);

        $handle = fopen($postalCodesFilepath, 'r');
        while(!feof($handle)) {
            $line = stream_get_line($handle, 1000000, "\n");
            $io->text($line);
            sleep(1);
        }
        fclose($handle);

        foreach (file($citiesFilepath) as $line) {

        }

        return Command::SUCCESS;
    }

    /**
     * @param string $datasetFile GEONAMES_*_DATASET class constant value
     * @return string Path to geonames dataset file
     * @throws \Exception
     */
    private function getGeonamesDataset(string $datasetFile): string
    {
        $zipName = basename($datasetFile);
        $datasetName = str_replace('.zip', '', $zipName);
        $datasetFilepath = $this->workingDirectory . '/' . $datasetName . '/' . $datasetName . '.txt';
        if (file_exists($datasetFilepath)) {
            return $datasetFilepath;
        }

        $downloadUrl = self::GEONAMES_EXPORT_URL . '/' . $datasetFile;
        $zipFilepath = $this->workingDirectory . '/' . $zipName;
        $this->downloadDataset($downloadUrl, $zipFilepath);
        $this->unzipDataset($zipFilepath);

        return $datasetFilepath;
    }

    /**
     * @throws \Exception
     */
    private function downloadDataset(string $downloadUrl, string $zipFilepath)
    {
        $zipFileHandle = fopen($zipFilepath, mode: 'w');
        if ($zipFileHandle === false) {
            throw new \Exception('Unable to create zip file for writing');
        }

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
        $datasetName = str_replace('.zip', '', basename($zipFilepath));
        $extractedPath = $this->workingDirectory . '/' . $datasetName;

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
