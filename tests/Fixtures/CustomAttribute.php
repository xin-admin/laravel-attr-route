<?php

namespace Xin\AttrRoute\Tests\Fixtures;

use Attribute;

/**
 * 非路由注解，用于验证 RouteRegisterService 会忽略不相关的注解。
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class CustomAttribute
{
}
