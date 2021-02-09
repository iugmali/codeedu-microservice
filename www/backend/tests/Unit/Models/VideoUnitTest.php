<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Traits\Uuid;
use App\Models\Video;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoUnitTest extends TestCase
{
    public function testFillable()
    {
        $fillable = ['title', 'description', 'year_launched', 'opened', 'rating', 'duration'];
        $video = new Video();
        $this->assertEquals($fillable, $video->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [SoftDeletes::class, Uuid::class];
        $videoTraits = array_keys(class_uses(Video::class));
        $this->assertEquals($traits, $videoTraits);
    }

    public function testCasts()
    {
        $casts = ['id' => 'string', 'opened' => 'boolean', 'year_launched' => 'integer', 'duration' => 'integer'];
        $video = new Video();
        $this->assertEquals($casts, $video->getCasts());
    }

    public function testDates()
    {
        $dates = ['created_at', 'updated_at', 'deleted_at'];
        $video = new Video();
        $this->assertEqualsCanonicalizing($dates, $video->getDates());
    }

    public function testIncrementing()
    {
        $video = new Video();
        $this->assertFalse($video->incrementing);
    }
}
