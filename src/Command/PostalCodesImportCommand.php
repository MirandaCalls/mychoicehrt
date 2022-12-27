<?php

namespace App\Command;

use App\Repository\PostalCodeRepository;
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
    public const GEONAMES_POSTAL_CODES_URL = 'https://download.geonames.org/export/zip/allCountries.zip';
    public const DOWNLOAD_DIR = '/var/geonames';
    public const DATASET_NAME = 'allCountries';

    private PostalCodeRepository $postalCodes;
    private HttpClientInterface $client;
    private string $workingDirectory;

    public function __construct(
        PostalCodeRepository $postalCodes,
        HttpClientInterface $client,
        string $projectDir,
    ) {
        $this->postalCodes = $postalCodes;
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

        $zipFilepath = $this->workingDirectory . '/' . self::DATASET_NAME . '.zip';
        $this->downloadPostalCodes($zipFilepath);
        $this->unzipPostalCodes($zipFilepath);

        $datasetFilepath = $this->workingDirectory . '/' . self::DATASET_NAME .'/' . self::DATASET_NAME . '.txt';

        return Command::SUCCESS;
    }

    /**
     * @throws \Exception
     */
    private function downloadPostalCodes(string $zipFilepath)
    {
        $zipFileHandle = fopen($zipFilepath, mode: 'w');
        if ($zipFileHandle === false) {
            throw new \Exception('Unable to create zip file for writing');
        }

        try {
            $res = $this->client->request('GET', self::GEONAMES_POSTAL_CODES_URL);
            if (200 !== $statusCode = $res->getStatusCode()) {
                throw new \Exception('Geonames returned failing status code: ' . $statusCode);
            }

            foreach ($this->client->stream($res) as $chunk) {
                fwrite($zipFileHandle, $chunk->getContent());
            }
        } catch (\Throwable $e) {
            throw new \Exception('HTTP request to fetch postal codes failed.');
        }

        fclose($zipFileHandle);
    }

    private function unzipPostalCodes(string $zipFilepath)
    {
        $extractedPath = $this->workingDirectory . '/' . self::DATASET_NAME;

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
