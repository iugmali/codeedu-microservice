<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'title' => 'O Filme',
            'description' => 'O filme mais rápido do Brasil',
            'year_launched' => 2001,
            'rating' => 'L',
            'duration' => 120
        ];
    }

    public function testList()
    {
        factory(Video::class, 1)->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);
        $videoKeys = array_keys($videos->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'title', 'description', 'year_launched', 'id', 'opened', 'rating', 'duration', 'created_at', 'updated_at', 'deleted_at'
        ], $videoKeys);
    }

    public function testCreateWithBasicFields()
    {
        $video = Video::create($this->data);
        $video->refresh();
        $this->assertEquals('O Filme', $video->title);
        $this->assertEquals(36, strlen($video->id));
        $video = Video::create([
            'title' => 'Blabla',
            'description' => 'ble ble',
            'year_launched' => 2001,
            'rating' => 'L',
            'duration' => 120
        ]);
        $this->assertEquals('ble ble', $video->description);
        $video = Video::create([
            'title' => 'Blabla',
            'description' => 'ble ble',
            'year_launched' => 2001,
            'rating' => 'L',
            'duration' => 120
        ]);
        $video->refresh();
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', [
            'title' => 'Blabla',
            'description' => 'ble ble',
            'year_launched' => 2001,
            'rating' => 'L',
            'duration' => 120,
            'opened' => false
        ]);
        $video = Video::create([
            'title' => 'Blabla',
            'description' => 'ble ble',
            'year_launched' => 2001,
            'opened' => true,
            'rating' => 'L',
            'duration' => 120
        ]);
        $this->assertTrue($video->opened);
    }

    public function testCreateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = Video::create($this->data + [
            'categories_id' => $category->id,
                'genres_id' => $genre->id
            ]);
        $this->assertDatabaseHas('category_video', [
           'category_id' => $category->id,
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
           'genre_id' => $genre->id,
            'video_id' => $video->id
        ]);
    }

    public function testCreateWithFiles()
    {
        \Storage::fake();
        $video = Video::create($this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'banner_file' => UploadedFile::fake()->image('banner.jpg'),
                'trailer_file' => UploadedFile::fake()->image('trailer.jpg'),
                'video_file' => UploadedFile::fake()->image('video.mp4')
            ]);
        \Storage::assertExists("{$video->id}/{$video->thumb_file}");
        \Storage::assertExists("{$video->id}/{$video->trailer_file}");
        \Storage::assertExists("{$video->id}/{$video->banner_file}");
        \Storage::assertExists("{$video->id}/{$video->video_file}");
    }

    public function testRollbackCreateFiles()
    {
        \Storage::fake();
        \Event::listen(TransactionCommitted::class, function (){
           throw new TestException();
        });
        $hasError = false;
        try {
            $video = Video::create($this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'banner_file' => UploadedFile::fake()->image('banner.jpg'),
                    'trailer_file' => UploadedFile::fake()->image('trailer.jpg'),
                    'video_file' => UploadedFile::fake()->image('video.mp4')
                ]);
        } catch (TestException $e) {
            $this->assertCount(0, \Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testFileUrlWithLocalDriver()
    {
        $fileFields = [];
        foreach (Video::$fileFields as $field) {
            $fileFields[$field] = "$field.test";
        }
        $video = factory(Video::class)->create($fileFields);
        $localDriver = config('filesystems.default');
        $baseUrl = config('filesystems.disks'.$localDriver)['url'];
        dump($baseUrl);
        foreach ($fileFields as $field => $value) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testFileUrlWithGcsDriver()
    {
        $fileFields = [];
        foreach (Video::$fileFields as $field) {
            $fileFields[$field] = "$field.test";
        }
        $video = factory(Video::class)->create($fileFields);
        $baseUrl = config('filesystems.disks.storage_api_uri');
        dump($baseUrl);

        \Config::set('filesystems.default', 'gcs');
        foreach ($fileFields as $field => $value) {
            $fileUrl = $video->{"{$field}_url"};
            $this->assertEquals("{$baseUrl}/$video->id/$value", $fileUrl);
        }
    }

    public function testRollbackCreate()
    {
        $hasError = false;
        try {
            Video::create([
                'title' => 'Blabla',
                'description' => 'ble ble',
                'year_launched' => 2001,
                'rating' => 'L',
                'duration' => 120,
                'categories_id' => [0,1,2]
            ]);
        } catch (QueryException $e) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testUpdateWithBasicFields()
    {
        /** @var Video $video */
        $video = factory(Video::class)->create([
            'opened' => true
        ]);
        $data = [
            'title' => 'Blabla',
            'description' => 'ble ble',
            'year_launched' => 2001,
            'opened' => false,
            'rating' => 'L',
            'duration' => 120
        ];
        $video->update($data);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $video->{$key});
        }
    }

    public function testUpdateWithFiles()
    {
        \Storage::fake();
        $oldFiles = [
            'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
            'banner_file' => UploadedFile::fake()->image('banner.jpg'),
            'trailer_file' => UploadedFile::fake()->image('trailer.jpg'),
            'video_file' => UploadedFile::fake()->image('video.mp4')
        ];
        $newFiles = [
            'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
            'banner_file' => UploadedFile::fake()->image('banner.jpg'),
            'trailer_file' => UploadedFile::fake()->image('trailer.jpg'),
            'video_file' => UploadedFile::fake()->image('video.mp4')
        ];
        $video = Video::create($this->data + $oldFiles);
        foreach ($oldFiles as $oldFile) {
            \Storage::assertExists("{$video->id}/{$oldFile->hashName()}");
        }
        $video->update($this->data + $newFiles);
        foreach ($oldFiles as $oldFile) {
            \Storage::assertMissing("{$video->id}/{$oldFile->hashName()}");
        }
        foreach ($newFiles as $newFile) {
            \Storage::assertExists("{$video->id}/{$newFile->hashName()}");
        }
    }

    public function testRollbackUpdateFiles()
    {
        \Storage::fake();
        $hasError = false;
        $oldFiles = [
            'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
            'banner_file' => UploadedFile::fake()->image('banner.jpg'),
            'trailer_file' => UploadedFile::fake()->image('trailer.jpg'),
            'video_file' => UploadedFile::fake()->image('video.mp4')
        ];
        $newFiles = [
            'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
            'banner_file' => UploadedFile::fake()->image('banner.jpg'),
            'trailer_file' => UploadedFile::fake()->image('trailer.jpg'),
            'video_file' => UploadedFile::fake()->image('video.mp4')
        ];
        $video = Video::create($this->data + $oldFiles);
        \Event::listen(TransactionCommitted::class, function (){
            throw new TestException();
        });
        try {
            $video->update($this->data + $newFiles);
        } catch (TestException $e) {
            $this->assertCount(4, \Storage::allFiles());
            foreach ($oldFiles as $oldFile) {
                \Storage::assertExists("{$video->id}/{$oldFile->hashName()}");
            }
            foreach ($newFiles as $newFile) {
                \Storage::assertMissing("{$video->id}/{$newFile->hashName()}");
            }
            $hasError = true;
        }
        $this->assertTrue($hasError);

    }

    public function testUpdateWithRelations()
    {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = factory(Video::class)->create();
        $video->update($this->data + [
                'categories_id' => $category->id,
                'genres_id' => $genre->id
            ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $category->id,
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genre->id,
            'video_id' => $video->id
        ]);
    }

    public function testRollbackUpdate()
    {
        $video = factory(Video::class)->create();
        $oldTitle = $video->title;
        $hasError = false;
        try {
            $video->update([
                'title' => 'Blabla',
                'description' => 'ble ble',
                'year_launched' => 2001,
                'rating' => 'L',
                'duration' => 120,
                'categories_id' => [0,1,2]
            ]);
        } catch (QueryException $e) {
            $this->assertDatabaseHas('videos', [
                'title' => $oldTitle
            ]);
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testHandleRelations()
    {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);
        $category = factory(Category::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$category->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);
        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, [
            'genres_id' => [$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->genres);
        Video::handleRelations($video, [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->genres);
    }

    public function testSyncCategories()
    {
        $categoriesId = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[0]],
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);
        Video::handleRelations($video, [
            'categories_id' => [$categoriesId[1],$categoriesId[2]],
        ]);
        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesId[2],
            'video_id' => $video->id
        ]);
    }

    public function testSyncGenres()
    {
        $genresId = factory(Genre::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, [
            'genres_id' => [$genresId[0]],
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);
        Video::handleRelations($video, [
            'genres_id' => [$genresId[1],$genresId[2]],
        ]);
        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresId[0],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[1],
            'video_id' => $video->id
        ]);
        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresId[2],
            'video_id' => $video->id
        ]);
    }

    public function testDelete()
    {
        /** @var Video $video */
        $video = factory(Video::class)->create();
        $video->delete();
        $this->assertNull(Video::find($video->id));

        $video->restore();
        $this->assertNotNull(Video::find($video->id));
    }
}
