<?php

namespace App\DataSource;

use App\Entity\Clinic;
use App\HereMaps\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface DataSourceInterface
{
    public const DATASOURCE__ERIN_REED = 'erinReed';
    public const DATASOURCE__TRANS_IN_THE_SOUTH = 'transInTheSouth';
    public const DATASOURCE__MANUAL_ENTRY = 'manualEntry';

    public function __construct(HttpClientInterface $httpClient, Client $hereClient);
    public function getType(): string;
    /* @throws DataSourceException */
    public function fetchClinics(): array;
    public function hash(Clinic $clinic): string;
    public function preImport(Clinic $clinic): void;
}