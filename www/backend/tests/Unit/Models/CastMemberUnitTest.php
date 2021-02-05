<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CastMemberUnitTest extends TestCase
{
    public function testFillable()
    {
        $fillable = ['name', 'type'];
        $cast_member = new CastMember();
        $this->assertEquals($fillable, $cast_member->getFillable());
    }

    public function testIfUseTraits()
    {
        $traits = [SoftDeletes::class, Uuid::class];
        $castMemberTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($traits, $castMemberTraits);
    }

    public function testCasts()
    {
        $casts = ['id' => 'string'];
        $cast_member = new CastMember();
        $this->assertEquals($casts, $cast_member->getCasts());
    }

    public function testDates()
    {
        $dates = ['created_at', 'updated_at', 'deleted_at'];
        $cast_member = new CastMember();
        $this->assertEqualsCanonicalizing($dates, $cast_member->getDates());
    }

    public function testIncrementing()
    {
        $cast_member = new CastMember();
        $this->assertFalse($cast_member->incrementing);
    }
}
