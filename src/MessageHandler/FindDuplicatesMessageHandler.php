<?php

namespace App\MessageHandler;

use App\Message\FindDuplicatesMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FindDuplicatesMessageHandler
{
    public function __invoke(FindDuplicatesMessage $message)
    {
    }
}