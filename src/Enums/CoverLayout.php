<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * How a block arranges its `cover` attachment relative to its content:
 * as a full background, stacked before or after the content, or floated to
 * one side.
 */
enum CoverLayout: string
{
    case Background = 'background';
    case Before = 'before';
    case After = 'after';
    case Left = 'left';
    case Right = 'right';
}
