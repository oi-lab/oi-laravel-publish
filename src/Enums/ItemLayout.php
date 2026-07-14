<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * How a single item (a feature or a slide) arranges its own cover relative to
 * its text: stacked before or after the text, or floated to one side. Unlike
 * {@see CoverLayout}, an item has no full-background option.
 */
enum ItemLayout: string
{
    case Before = 'before';
    case After = 'after';
    case Left = 'left';
    case Right = 'right';
}
