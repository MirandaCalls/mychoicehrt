<?php

namespace App\SearchEngine;

readonly class SearchEngineResults
{
    public function __construct(
        public array $results,
        public ?array $matchedLocation,
        public int $searchRadius,
        public int $totalResults,
        public int $totalPages,
    ) {}
}