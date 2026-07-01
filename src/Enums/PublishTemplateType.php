<?php

namespace OiLab\OiLaravelPublish\Enums;

/**
 * The kind of object a PublishTemplate describes: a whole page, or a single
 * block within a page.
 */
enum PublishTemplateType: string
{
    case Page = 'page';
    case Block = 'block';
}
