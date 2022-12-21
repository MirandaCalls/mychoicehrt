<?php

namespace App\Message;

class FindDuplicatesMessage
{
    private int $clinicId;

    public function __construct(int $clinicId)
    {
        $this->clinicId = $clinicId;
    }

    public function getClinicId(): Int
    {
        return $this->clinicId;
    }
}
