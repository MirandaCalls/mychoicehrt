<?php

namespace App\DataSource;

use App\Entity\Clinic;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface DataSourceInterface
{
    public const DATASOURCE__ERIN_REED = 'erinReed';
    public const DATASOURCE__MANUAL_ENTRY = 'manualEntry';

    public function __construct(HttpClientInterface $httpClient);
    public function getType(): string;
    public function fetchClinics(): array;
    public function hash(Clinic $clinic): string;
}