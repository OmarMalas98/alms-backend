<?php

namespace Database\Seeders;

use App\Http\Controllers\ComponentControllers\ComponentController;
use App\Models\Components\Component;
use App\Models\Components\TextArea;
use App\Models\Components\Title;
use App\Models\Components\Video;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $component=Component::create([
                'page_id' => 2,
                'order' => 1,
                'type' => 'title'
            ]
        );
        $title = Title::create([
            'component_id' => $component->id,
            'body'=> 'Title 1',
        ]);
        $title->save();

        $component=Component::create([
                'page_id' => 2,
                'order' => 2,
                'type' => 'video'
            ]
        );
        $video = Video::create([
            'component_id' => $component->id,
            'url'=> 'https://www.youtube.com/watch?v=kgwziP8m0vc'
        ]);
        $video->save();


        $component=Component::create([
                'page_id' => 2,
                'order' => 3,
                'type' => 'textarea'
            ]
        );
        $textArea = TextArea::create([
            'component_id' => $component->id,
            'body'=> 'Text Area',
        ]);
        $textArea->save();
    }
}
