<?php

namespace Database\Seeders;

use App\Models\Content\Content;
use App\Models\Content\Module;
use Illuminate\Database\Seeder;

class ModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $content = Content::create([
            'title' => 'Module1',
            'content_type' => 'module',
            'parent_id' => 1,
            'order' => 1,
        ]);

        $module = Module::create([
            'title' => 'Module1',
            'description' => 'bla bla bla',
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content->id,
        ]);

        $content2 = Content::create([
            'title' => 'Module2',
            'content_type' => 'module',
            'parent_id' => 2,
            'order' => 2,
        ]);

        $module2 = Module::create([
            'title' => 'Module2',
            'description' => 'bla bla bla',
            'status_id' => 1,
            'creator_id' => 1,
            'content_id' => $content2->id,
        ]);

    }
}
