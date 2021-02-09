<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;
    private $cast_member;

    protected function setUp() : void
    {
        parent::setUp();
        $this->cast_member = factory(CastMember::class)->create(['type' => CastMember::TYPE_ACTOR]);

    }

    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->cast_member->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->cast_member->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->cast_member->toArray());
    }

    public function testInvalidationData()
    {
        $data = [
            'name' => '',
            'type' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
        $data = [
            'type' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testStore()
    {
        $data = [
            'name' => 'Tarantino',
            'type' => CastMember::TYPE_DIRECTOR
        ];
        $response = $this->assertStore($data, $data + ['deleted_at'=> null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
        $data = [
            'name' => 'John Travolta',
            'type' => CastMember::TYPE_ACTOR
        ];
        $this->assertStore($data, $data);
    }

    public function testUpdate()
    {
        $data = [
            'name' => 'Guilhermo Del Toro',
            'type' => CastMember::TYPE_DIRECTOR
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at'=> null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testDestroy()
    {
        $response = $this->json('DELETE', route('cast_members.destroy', ['cast_member' => $this->cast_member->id]));
        $response->assertStatus(204);
        $this->assertNull(CastMember::find($this->cast_member->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->cast_member->id));
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->cast_member->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }

}
