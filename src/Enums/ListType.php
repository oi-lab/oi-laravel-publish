<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * Whether a list of items is ordered (`ol`) or not (`ul`).
 */
enum ListType: string
{
    case Ordered = 'ordered';
    case Unordered = 'unordered';
}
