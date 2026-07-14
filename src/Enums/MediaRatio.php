<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The aspect ratio a block's media is displayed at: a square, a 16:9 widescreen,
 * a portrait or a landscape basis — or `Inherit` to defer to the theme's default.
 *
 * Shared by the cover blocks (`cover_ratio`) and the slides carousel
 * (`media_ratio`); the host maps each case onto its own CSS ratio classes.
 */
enum MediaRatio: string
{
    case Inherit = 'inherit';
    case Square = 'square';
    case Widescreen = 'widescreen';
    case BasisPortrait = 'basis-portrait';
    case BasisLandscape = 'basis-landscape';
}
