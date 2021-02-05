<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);
        $genreKeys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
          'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
        ], $genreKeys);
    }

    public function testCreate()
    {
        $genre = Genre::create([
            'name' => 'Blabla'
        ]);
        $genre->refresh();
        $this->assertEquals('Blabla', $genre->name);
        $this->assertEquals(36, strlen($genre->id));
        $this->assertNull($genre->description);
        $this->assertTrue($genre->is_active);
        $genre = Genre::create([
            'name' => 'Blabla',
            'is_active' => false
        ]);
        $this->assertFalse($genre->is_active);
        $genre = Genre::create([
            'name' => 'Blabla',
            'is_active' => true
        ]);
        $this->assertTrue($genre->is_active);
    }

    public function testUpdate()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ])->first();
        $data = [
            'name' => 'Brasil update',
            'is_active' => true
        ];
        $genre->update($data);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testDelete()
    {
        /** @var Genre $genre */
        $genre = factory(Genre::class)->create();
        $genre->delete();
        $this->assertNull(Genre::find($genre->id));

        $genre->restore();
        $this->assertNotNull(Genre::find($genre->id));
    }
}
