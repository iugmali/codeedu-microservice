<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(CastMember::class, 1)->create();
        $cast_members = CastMember::all();
        $this->assertCount(1, $cast_members);
        $castMemberKeys = array_keys($cast_members->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
          'id', 'name', 'type', 'created_at', 'updated_at', 'deleted_at'
        ], $castMemberKeys);
    }

    public function testCreate()
    {
        $cast_member = CastMember::create([
            'name' => 'Blabla',
            'type' => CastMember::TYPE_ACTOR
        ]);
        $cast_member->refresh();
        $this->assertEquals('Blabla', $cast_member->name);
        $this->assertEquals(36, strlen($cast_member->id));
        $this->assertEquals(CastMember::TYPE_ACTOR, $cast_member->type);
        $cast_member = CastMember::create([
            'name' => 'Blabla',
            'type' => CastMember::TYPE_DIRECTOR
        ]);
        $this->assertEquals(CastMember::TYPE_DIRECTOR, $cast_member->type);
    }

    public function testUpdate()
    {
        /** @var CastMember $cast_member */
        $cast_member = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ])->first();
        $data = [
            'name' => 'Brasil update',
            'type' => CastMember::TYPE_ACTOR
        ];
        $cast_member->update($data);
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $cast_member->{$key});
        }
    }

    public function testDelete()
    {
        /** @var CastMember $cast_member */
        $cast_member = factory(CastMember::class)->create();
        $cast_member->delete();
        $this->assertNull(CastMember::find($cast_member->id));

        $cast_member->restore();
        $this->assertNotNull(CastMember::find($cast_member->id));
    }
}
