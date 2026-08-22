<?php

namespace PlatformConfig\Business\Dtos;

use Shared\Contracts;

class SetDto
{
    public string $setting;
    public string $value;

    public function __construct(string $setting, string $value)
    {
        Contracts::requiresNotNullOrEmpty($setting, 'Setting');
        Contracts::requiresNotNullOrEmpty($value, 'Value');

        $this->setting = $setting;
        $this->value = $value;
    }
}
