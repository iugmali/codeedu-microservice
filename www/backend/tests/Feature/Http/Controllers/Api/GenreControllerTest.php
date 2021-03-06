<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Resources\GenreResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;
    private $genre;
    private $serializedFields = [
        'id',
        'name',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ]
    ];

    protected function setUp() : void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();

    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'meta' => [],
                'links' => []
            ]);
        $this->assertResource($response, GenreResource::collection(collect([$this->genre])));
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
            ->assertJsonFragment($this->genre->toArray());
        $this->assertResource($response, new GenreResource($this->genre));
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'categories_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
        $data = [
            'name' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');
        $data = [
            'categories_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testStore()
    {
        $categoryId = factory(Category::class)->create()->id;
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore($data + ['categories_id' => [$categoryId]], $data + ['is_active' => true, 'deleted_at'=> null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertHasCategory($this->getIdFromResponse($response), $categoryId);
        $data = [
            'name' => 'test',
            'is_active' => false
        ];
        $response = $this->assertStore($data + ['categories_id' => [$categoryId]], $data + ['is_active' => false]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertHasCategory($this->getIdFromResponse($response), $categoryId);
    }

    public function testUpdate()
    {
        $categoryId = factory(Category::class)->create()->id;
        $this->genre = factory(Genre::class)->create([
            'is_active' => false
        ]);
        $data = [
            'name' => 'test',
            'is_active' => true
        ];
        $response = $this->assertUpdate($data + ['categories_id' => [$categoryId]], $data + ['deleted_at'=> null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertHasCategory($this->getIdFromResponse($response), $categoryId);
    }


    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $data = [
            'name' => 'test',
            'categories_id' => [$categoriesId[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $data);
        $this->assertHasCategory($this->getIdFromResponse($response), $categoriesId[0]);
        $data = [
            'name' => 'test',
            'categories_id' => [$categoriesId[1],$categoriesId[2]]
        ];
        $response = $this->json('PUT', route('genres.update', ['genre' => $response->json('id')]), $data);
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesId[0],
            'genre_id' => $response->json('id')
        ]);
        $this->assertHasCategory($this->getIdFromResponse($response), $categoriesId[1]);
        $this->assertHasCategory($this->getIdFromResponse($response), $categoriesId[2]);
    }

    protected function assertHasCategory($genreId, $categoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            'category_id' => $categoryId,
            'genre_id' => $genreId
        ]);
    }

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('validate')->withAnyArgs()->andReturn(['name' => 'test']);
        $controller->shouldReceive('rulesStore')->withAnyArgs()->andReturn([]);
        $request = \Mockery::mock(Request::class);
        $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());
        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('findOrFail')->withAnyArgs()->andReturn($this->genre);
        $controller->shouldReceive('validate')->withAnyArgs()->andReturn(['name' => 'test']);
        $controller->shouldReceive('rulesUpdate')->withAnyArgs()->andReturn([]);
        $request = \Mockery::mock(Request::class);
        $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());
        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->genre->id]));
        $response->assertStatus(204);
        $this->assertNull(Genre::find($this->genre->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->genre->id));
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model()
    {
        return Genre::class;
    }

}
