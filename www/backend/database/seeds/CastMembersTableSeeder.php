<?php


class CastMembersTableSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        factory(\App\Models\CastMember::class, 100)->create();

    }
}
