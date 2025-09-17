<?php

namespace Database\Seeders;

use App\Models\Other\Role;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'teacher']);
        Role::create(['name' => 'student']);
    }
}
