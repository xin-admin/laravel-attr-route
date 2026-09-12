<?php

namespace Xin\AttrRoute\Contracts;

interface AttrRoute
{

    /**
     * Scan controllers from the given paths and register attribute routes
     *
     * @param string|array $path Controller directories (multiple allowed)
     * @return void
     */
    public function register(string|array $path): void;

}
