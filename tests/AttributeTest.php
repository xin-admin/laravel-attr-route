<?php

namespace Xin\AttrRoute\Tests;

use Attribute;
use ReflectionClass;
use Xin\AttrRoute\Attribute\AnyRoute;
use Xin\AttrRoute\Attribute\DeleteRoute;
use Xin\AttrRoute\Attribute\GetRoute;
use Xin\AttrRoute\Attribute\PatchRoute;
use Xin\AttrRoute\Attribute\PostRoute;
use Xin\AttrRoute\Attribute\PutRoute;
use Xin\AttrRoute\Attribute\RequestAttribute;

class AttributeTest extends TestCase
{
    public function test_get_route_defaults(): void
    {
        $attr = new GetRoute();

        $this->assertSame('', $attr->route);
        $this->assertTrue($attr->authorize);
        $this->assertSame('', $attr->middleware);
        $this->assertSame([], $attr->where);
        $this->assertSame('GET', $attr->httpMethod);
    }

    public function test_constructor_assigns_properties(): void
    {
        $attr = new GetRoute('/users', 'user.list', ['web'], ['id' => '[0-9]+']);

        $this->assertSame('/users', $attr->route);
        $this->assertSame('user.list', $attr->authorize);
        $this->assertSame(['web'], $attr->middleware);
        $this->assertSame(['id' => '[0-9]+'], $attr->where);
    }

    public function test_http_method_of_each_route_attribute(): void
    {
        $this->assertSame('GET', (new GetRoute())->httpMethod);
        $this->assertSame('POST', (new PostRoute())->httpMethod);
        $this->assertSame('PUT', (new PutRoute())->httpMethod);
        $this->assertSame('PATCH', (new PatchRoute())->httpMethod);
        $this->assertSame('DELETE', (new DeleteRoute())->httpMethod);
        $this->assertSame('ANY', (new AnyRoute())->httpMethod);
    }

    public function test_request_attribute_defaults(): void
    {
        $attr = new RequestAttribute();

        $this->assertSame('', $attr->routePrefix);
        $this->assertSame('', $attr->abilitiesPrefix);
        $this->assertSame('', $attr->middleware);
        $this->assertSame('', $attr->authModel);
    }

    public function test_request_attribute_constructor_assigns_properties(): void
    {
        $attr = new RequestAttribute('/admin', 'admin', ['web'], 'admin');

        $this->assertSame('/admin', $attr->routePrefix);
        $this->assertSame('admin', $attr->abilitiesPrefix);
        $this->assertSame(['web'], $attr->middleware);
        $this->assertSame('admin', $attr->authModel);
    }

    public function test_route_attributes_target_methods(): void
    {
        $routeAttributes = [
            GetRoute::class,
            PostRoute::class,
            PutRoute::class,
            PatchRoute::class,
            DeleteRoute::class,
            AnyRoute::class,
        ];

        foreach ($routeAttributes as $class) {
            $flags = (new ReflectionClass($class))
                ->getAttributes(Attribute::class)[0]
                ->newInstance()
                ->flags;

            $this->assertSame(Attribute::TARGET_METHOD, $flags, "{$class} 应声明为方法级注解");
        }
    }

    public function test_request_attribute_targets_class(): void
    {
        $flags = (new ReflectionClass(RequestAttribute::class))
            ->getAttributes(Attribute::class)[0]
            ->newInstance()
            ->flags;

        $this->assertSame(Attribute::TARGET_CLASS, $flags);
    }
}
