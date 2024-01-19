<?php

namespace Stfn\CircuitBreaker;

enum CircuitState: string
{
    case Open = 'open';
    case Closed = 'closed';
    case HalfOpen = 'half_open';
    case ForceOpen = 'force_open';
}
