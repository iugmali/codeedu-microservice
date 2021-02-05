<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestUploads;

    private $video;
    private $sendData;
    protected function setUp() : void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);
        $this->sendData = [
            'title' => 'Title',
            'description' => 'O filme mais procurado',
            'year_launched' => 2021,
            'rating' => Video::RATING_LIST[0],
            'duration' => 120
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
        $data = [
            'title' => str_repeat('a', 256),
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
        $data = [
            'duration' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
        $data = [
            'opened' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
        $data = [
            'rating' => 0
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
        $data = [
            'year_launched' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
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
        $data = [
            'genres_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');
        $data = [
            'genres_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
        $genre = factory(Genre::class)->create();
        $genre->delete();
        $data = [
            'genres_id' => [$genre->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidationFiles()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            500,
            'mimetypes',['values' => 'video/mp4']
        );
    }

    public function testSaveWithoutFiles()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);
        $data = [
            [
                'send_data' => $this->sendData + ['categories_id' => [$category->id], 'genres_id' => [$genre->id]],
                'test_data' => $this->sendData + ['opened' => false, 'deleted_at'=> null]
            ],
            [
                'send_data' => $this->sendData + ['opened' => true, 'categories_id' => [$category->id], 'genres_id' => [$genre->id]],
                'test_data' => $this->sendData + ['opened' => true, 'deleted_at'=> null]
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1],'categories_id' => [$category->id], 'genres_id' => [$genre->id]],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1], 'deleted_at'=> null]
            ]
        ];
        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data']);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($category->id, $response->json('id'));
            $this->assertHasGenre($genre->id, $response->json('id'));
            $response = $this->assertUpdate($value['send_data'], $value['test_data']);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($category->id, $response->json('id'));
            $this->assertHasGenre($genre->id, $response->json('id'));
        }
    }

    public function testStoreWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json("POST",
            $this->routeStore(),
            $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id]
            ] + $files);
        $response->assertStatus(201);
        foreach ($files as $file) {
            \Storage::assertExists("{$response->json('id')}/{$file->hashName()}");
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $files = $this->getFiles();
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($category->id);

        $response = $this->json("PUT",
            $this->routeUpdate(),
            $this->sendData + [
                'categories_id' => [$category->id],
                'genres_id' => [$genre->id]
            ] + $files);
        $response->assertStatus(200);
        foreach ($files as $file) {
            \Storage::assertExists("{$response->json('id')}/{$file->hashName()}");
        }
    }


    protected function assertHasCategory($categoryId, $videoId)
    {
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoryId,
            'video_id' => $videoId
        ]);
    }

    protected function assertHasGenre($genreId, $videoId)
    {
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genreId,
            'video_id' => $videoId
        ]);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('findOrFail')->withAnyArgs()->andReturn($this->video);
        $controller->shouldReceive('validate')->withAnyArgs()->andReturn($this->sendData);
        $controller->shouldReceive('rulesUpdate')->withAnyArgs()->andReturn([]);
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('get')->withAnyArgs()->andReturnNull();
        $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());
        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $e) {
            $this->assertCount(1, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->video->id]));
        $response->assertStatus(204);
        $this->assertNull(Video::find($this->video->id));
        $this->assertNotNull(Video::withTrashed()->find($this->video->id));
    }

    protected function getFiles()
    {
        return [
          'video_file' => UploadedFile::fake()->create('video_file.mp4')
        ];
    }
    protected function routeStore()
    {
        return route('videos.store');
    }
    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }
    protected function model()
    {
        return Video::class;
    }

}
