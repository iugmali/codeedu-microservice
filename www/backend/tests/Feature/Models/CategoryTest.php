<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
          'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $categoryKeys);
    }

    public function testCreate()
    {
        $category = Category::create([
            'name' => 'Blabla'
        ]);
        $category->refresh();
        $this->assertEquals('Blabla', $category->name);
        $this->assertEquals(36, strlen($category->id));
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);
        $category = Category::create([
            'name' => 'Blabla',
            'description' => 'ble ble'
        ]);
        $this->assertEquals('ble ble', $category->description);
        $category = Category::create([
            'name' => 'Blabla',
            'description' => null
        ]);
        $this->assertNull($category->description);
        $category = Category::create([
            'name' => 'Blabla',
            'is_active' => false
        ]);
        $this->assertFalse($category->is_active);
        $category = Category::create([
            'name' => 'Blabla',
            'is_active' => true
        ]);
        $this->assertTrue($category->is_active);
    }

    public function testUpdate()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create([
            'description' => 'bleble',
            'is_active' => false
        ]);
        $data = [
            'name' => 'Blabla update',
            'description' => 'bleble update',
            'is_active' => true
        ];
        $category->update($data);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }
    public function testDelete()
    {
        /** @var Category $category */
        $category = factory(Category::class)->create();
        $category->delete();
        $this->assertNull(Category::find($category->id));

        $category->restore();
        $this->assertNotNull(Category::find($category->id));
    }
}
