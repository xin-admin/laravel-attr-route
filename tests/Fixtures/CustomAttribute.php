<?php

namespace Xin\AttrRoute\Tests\Fixtures;

use Attribute;

/**
 * A non-route attribute, used to verify RouteRegisterService ignores unrelated attributes.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class CustomAttribute
{
}
