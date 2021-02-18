<?php

use Illuminate\Database\Seeder;
use App\Models\Genre;
use Illuminate\Database\Eloquent\Model;
use App\Models\Video;

class VideosTableSeeder extends Seeder
{
    private $allGenres;
    private $relations = [
        'genres_id' => [],
        'categories_id' => []
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);
        $self = $this;
        $this->allGenres = Genre::all();
        Model::reguard();
        factory(Video::class, 100)
            ->make()
            ->each(function (Video $video) use($self) {
                $self->fetchRelations();
                Video::create(array_merge(
                    $video->toArray(),
                    [
                        'thumb_file' => $self->getThumbFile(),
                        'banner_file' => $self->getBannerFile(),
                        'trailer_file' => $self->getTrailerFile(),
                        'video_file' => $self->getVideoFile()
                    ],
                    $this->relations
                ));
        });
        Model::unguard();
    }

    protected function fetchRelations()
    {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesId = [];
        foreach ($subGenres as $genre) {
            array_push($categoriesId, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesId = array_unique($categoriesId);
        $genresId = $subGenres->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesId;
        $this->relations['genres_id'] = $genresId;
    }

    public function getThumbFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/images/thumb.jpg'),
            'Thumb'
        );
    }
    public function getBannerFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/images/banner.jpg'),
            'Banner'
        );
    }
    public function getTrailerFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/videos/trailer.mp4'),
            'Trailer'
        );
    }
    public function getVideoFile()
    {
        return new \Illuminate\Http\UploadedFile(
            storage_path('faker/videos/video.mp4'),
            'Video'
        );
    }
}
