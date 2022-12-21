<?php

namespace App\DataSource;

class DataSourceException extends \Exception {

    private string $type;

    public function __construct(string $message, string $type)
    {
        $this->type = $type;
        parent::__construct($message);
    }

    public function __toString(): string
    {
        return $this->type . ': ' . $this->getMessage();
    }
}