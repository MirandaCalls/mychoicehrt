<?php

namespace App\SearchEngine;

use App\Entity\Clinic;

class SearchEngineResults
{
    public function __construct(
        /**
         * @var Clinic[]
         */
        public array $results,
        public ?array $matchedLocation,
        public int $searchRadius,
        public int $totalResults,
        public int $currentPage,
        public int $totalPages,
    ) {}
}