<?php

namespace App;

readonly class SearchEngineResults
{
    public function __construct(
        public readonly int $totalResults,
        public readonly int $totalPages,
        public readonly array $results,
        public readonly int $searchRadius,
        public readonly string $matchedLocation,
    ) {}
}