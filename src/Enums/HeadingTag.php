<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The HTML tag a heading is rendered with. Lets an editor place a block in the
 * document outline without changing its visual scale, which is carried by the
 * style classes instead.
 */
enum HeadingTag: string
{
    case H1 = 'h1';
    case H2 = 'h2';
    case H3 = 'h3';
    case H4 = 'h4';
    case H5 = 'h5';
    case H6 = 'h6';
}
