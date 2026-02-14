<?php

namespace App\Services\Channels;

interface ChannelInterface
{
    public function send(string $title, string $summary, string $originalContent): bool;

    public function getName(): string;
}
