<?php

namespace Xin\AttrRoute\Contracts;

interface AttrRoute
{

    /**
     * 从指定路径扫描控制器并注册注解路由
     *
     * @param string|array $path 控制器目录（可传多个）
     * @return void
     */
    public function register(string|array $path): void;

}
