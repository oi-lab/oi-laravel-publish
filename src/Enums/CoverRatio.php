<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * How a block arranges its `cover` attachment relative to its content:
 * as a full background, stacked before or after the content, or floated to
 * one side.
 */
enum CoverRatio: string
{
    case Inherit = 'inherit';
    case Square = 'square';
    case Widescreen = 'widescreen';
    case BasisPortrait = 'basis-portrait';
    case BasisLandscape = 'basis-landscape';
}
