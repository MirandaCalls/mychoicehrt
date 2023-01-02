<?php

namespace App;

class SearchEngineParams
{
    private ?int $searchRadius = null;
    private int $page = 1;

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
}