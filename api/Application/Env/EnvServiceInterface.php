<?php

namespace Application\Env;

interface EnvServiceInterface
{
    /**
     * Get the value of the environment variable
     * @param string $key
     * @return mixed
     */
    public function get(string $key);
}