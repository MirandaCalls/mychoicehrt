<?php

namespace App\Geonames;

use Doctrine\ORM\EntityManagerInterface;

abstract class GeonamesImporterAbstract
{
    public const GEONAMES_EXPORT_URL = 'https://download.geonames.org/export';

    protected GeonamesDatasetHandler $datasetHandler;
    protected EntityManagerInterface $entityManager;
    protected string $importVersion;

    protected string $datasetUrl = '';

    public function __construct(
        GeonamesDatasetHandler $datasetHandler,
        EntityManagerInterface $entityManager,
    ) {
        $this->datasetHandler = $datasetHandler;
        $this->entityManager = $entityManager;
        $this->importVersion = uniqid();
    }

    public function import(): int
    {
        $this->configure();

        $datasetFilepath = $this->datasetHandler->download($this->datasetUrl, true);
        $totalCount = $this->datasetHandler->processData($datasetFilepath, function(array $data, int $recordsCount) {
            if ($recordsCount !== 0 && $recordsCount % 500 === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $this->handleData($data);
        });
        $this->entityManager->flush();

        return $totalCount;
    }

    /**
     * Implementation should set the $dataset property to one of the GEONAMES_*_DATASET values.
     */
    abstract protected function configure(): void;

    abstract protected function handleData(array $data): void;

}
