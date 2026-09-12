<?php

namespace Xin\AttrRoute\Tests\Fixtures;

use Xin\AttrRoute\Attribute\GetRoute;

/**
 * A controller whose class attribute is not RequestAttribute; no routes should be registered.
 */
#[CustomAttribute]
class PlainController
{
    #[GetRoute('/plain', authorize: false)]
    public function index(): string
    {
        return 'plain';
    }
}
