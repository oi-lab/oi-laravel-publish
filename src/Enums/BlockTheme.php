<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The colour scheme a block renders under. `System` hands the decision to the
 * host application, which reads its own class or CSS variables.
 */
enum BlockTheme: string
{
    case Light = 'light';
    case Dark = 'dark';
    case System = 'system';
}
