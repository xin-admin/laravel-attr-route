<?php

namespace Xin\AttrRoute\Tests\Fixtures;

/**
 * A controller without any attributes; no routes should be registered.
 */
class BareController
{
    public function index(): string
    {
        return 'bare';
    }
}
