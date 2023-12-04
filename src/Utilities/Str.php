<?php

namespace Stfn\CircuitBreaker\Utilities;

class Str
{
    public static function camelize(string $input, string $separator = '_'): string
    {
        return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
    }
}
