<?php

namespace App\Geonames;

class SearchResult
{
    public function __construct(
        public string $title,
        public float $latitude,
        public float $longitude,
    ) {}

}
