<?php

declare(strict_types=1);

namespace Stfn\CircuitBreaker;

use Stfn\CircuitBreaker\Utilities\Str;

class Config
{
    /**
     * @var int
     */
    public int $failureThreshold = 5;

    /**
     * @var int
     */
    public int $recoveryTime = 60;

    /**
     * @param array $config
     * @return Config
     */
    public static function make(array $config = []): Config
    {
        $object = new self();

        foreach ($config as $property => $value) {
            $property = Str::camelize($property);
            if (property_exists($object, $property)) {
                $object->{$property} = $value;
            }
        }

        return $object;
    }
}
