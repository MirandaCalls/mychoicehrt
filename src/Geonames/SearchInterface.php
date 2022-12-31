<?php

namespace App\Geonames;

interface SearchInterface
{
    /**
     * @return SearchResult[]
     */
    public function search(string $searchText): array;
}