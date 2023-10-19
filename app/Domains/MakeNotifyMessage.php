<?php

namespace App\Domains;

class MakeNotifyMessage
{
    public function __invoke(array $params): string
    {
        return "From: " . $params['from'] . "\nSubject: " . $params['subject'] . "\n\n" . $params['message'];
    }
}
