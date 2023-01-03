<?php

namespace App\SearchEngine;

class SearchEngineParams
{
    public const SEARCH_TYPE_CITY = 'city';
    public const SEARCH_TYPE_POSTAL = 'postal';

    private string $searchText = '';

    private string $searchType = self::SEARCH_TYPE_CITY;

    private string $countryCode = 'US';

    private ?int $searchRadius = null;

    private int $page = 1;

    private ?array $location = null;

    public function getSearchText(): string
    {
        return $this->searchText;
    }

    public function setSearchText(string $searchText): void
    {
        $this->searchText = $searchText;
    }

    public function getSearchType(): string
    {
        return $this->searchType;
    }

    public function setSearchType(string $searchType): void
    {
        if ($searchType !== self::SEARCH_TYPE_CITY && $searchType !== self::SEARCH_TYPE_POSTAL) {
            throw new \Exception('Unsupported search type!');
        }

        $this->searchType = $searchType;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getSearchRadius(): ?int
    {
        return $this->searchRadius;
    }

    public function setSearchRadius(?int $searchRadius): self
    {
        $this->searchRadius = $searchRadius;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function getLocation(): ?array
    {
        return $this->location;
    }

    /**
     * @param array $location - Details of a specific location to perform a search on
     * $location = [
     *      'title' => 'Some place',
     *      'latitude' => -11.22,
     *      'longitude' => 22.22,
     * ];
     */
    public function setLocation(array $location): void
    {
        $this->location = $location;
    }

}