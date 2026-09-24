<?php

declare(strict_types=1);

namespace Tests\Unit\Category;

use InvalidArgumentException;
use Modules\Category\Domain\Models\Category;
use PHPUnit\Framework\TestCase;

final class CategoryDomainTest extends TestCase
{
    public function test_can_instantiate_category_with_valid_attributes(): void
    {
        $category = Category::create(
            name: 'Servidores & Redes',
            description: 'Infraestructura principal',
            icon: 'server',
            color: '#10b981',
            active: true,
            id: 10
        );

        $this->assertSame(10, $category->id());
        $this->assertSame('Servidores & Redes', $category->name());
        $this->assertSame('Infraestructura principal', $category->description());
        $this->assertSame('server', $category->icon());
        $this->assertSame('#10b981', $category->color());
        $this->assertTrue($category->isActive());
    }

    public function test_throws_exception_on_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Category::create(name: '   ');
    }

    public function test_defaults_invalid_hex_color(): void
    {
        $category = Category::create(
            name: 'Periféricos',
            color: 'not-a-color'
        );

        $this->assertSame('#0d6efd', $category->color());
    }

    public function test_can_deactivate_and_activate(): void
    {
        $category = Category::create(name: 'Laptops');
        $this->assertTrue($category->isActive());

        $deactivated = $category->deactivate();
        $this->assertFalse($deactivated->isActive());

        $reactivated = $deactivated->activate();
        $this->assertTrue($reactivated->isActive());
    }

    public function test_can_update_attributes(): void
    {
        $category = Category::create(
            name: 'Impresoras',
            description: 'Toner e inyección',
            icon: 'printer',
            color: '#ef4444',
            id: 5
        );

        $updated = $category->update(
            name: 'Impresoras y Escáneres',
            description: 'Multifuncionales',
            icon: 'printer',
            color: '#8b5cf6'
        );

        $this->assertSame(5, $updated->id());
        $this->assertSame('Impresoras y Escáneres', $updated->name());
        $this->assertSame('Multifuncionales', $updated->description());
        $this->assertSame('#8b5cf6', $updated->color());
    }
}
