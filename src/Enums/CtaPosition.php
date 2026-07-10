<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * Which slot of a block a call to action renders in. Null means the component
 * decides — the case of a slide, which has a single, unpositioned CTA.
 */
enum CtaPosition: string
{
    case Header = 'header';
    case Body = 'body';
    case Footer = 'footer';
}
