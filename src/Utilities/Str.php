<?php

namespace Stfn\CircuitBreaker\Utilities;

class Str
{
    /**
     * @param string $input
     * @param string $separator
     * @return string
     */
    public static function camelize(string $input, string $separator = '_'): string
    {
        return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
    }
}
