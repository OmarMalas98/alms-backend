<?php

namespace Database\Seeders;

use App\Models\Components\Component;
use App\Models\Components\Page;
use App\Models\Components\TextArea;
use App\Models\Content\Content;
use App\Models\ExplanationLevel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $content=Content::find(5);

        $simpleExplanationLevel = ExplanationLevel::where('lesson_id', $content->lesson->id)
            ->where('level', 'simple')
            ->first();

        $mediumExplanationLevel = ExplanationLevel::where('lesson_id', $content->lesson->id)
            ->where('level', 'medium')
            ->first();

        $moreExplanationLevel = ExplanationLevel::where('lesson_id', $content->lesson->id)
            ->where('level', 'more explanation')
            ->first();

        $page1=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $simpleExplanationLevel->id,
            'order'=>1
        ]);
            $page1->save();
        $component1=Component::create([
                'page_id' => $page1->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component1->id,
            'body'=> 'Title 1',
        ]);

        $page2=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $simpleExplanationLevel->id,
            'order'=>2
        ]);
        $component2=Component::create([
                'page_id' => $page2->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component2->id,
            'body'=> 'Title 2',
        ]);

             $page3=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $simpleExplanationLevel->id,
            'order'=>3
        ]);
        $component3=Component::create([
                'page_id' => $page3->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component3->id,
            'body'=> 'Title 3',
        ]);


        $page4=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $mediumExplanationLevel->id,
            'order'=>1
        ]);
        $component4=Component::create([
                'page_id' => $page4->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component4->id,
            'body'=> 'Title 4',
        ]);

        $page5=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $mediumExplanationLevel->id,
            'order'=>2
        ]);
        $component5=Component::create([
                'page_id' => $page5->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component5->id,
            'body'=> 'Title 5',
        ]);

        $page6=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $mediumExplanationLevel->id,
            'order'=>3
        ]);
        $component6=Component::create([
                'page_id' => $page6->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component6->id,
            'body'=> 'Title 6',
        ]);

        $page7=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $mediumExplanationLevel->id,
            'order'=>4
        ]);
        $component7=Component::create([
                'page_id' => $page7->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component7->id,
            'body'=> 'Title 7',
        ]);


        $page8=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $mediumExplanationLevel->id,
            'order'=>5
        ]);
        $component8=Component::create([
                'page_id' => $page8->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component8->id,
            'body'=> 'Title 8',
        ]);

        $page9=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $moreExplanationLevel->id,
            'order'=>1
        ]);
        $component9=Component::create([
                'page_id' => $page9->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component9->id,
            'body'=> 'Title 9',
        ]);

        $page10=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $moreExplanationLevel->id,
            'order'=>2
        ]);
        $component10=Component::create([
                'page_id' => $page10->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component10->id,
            'body'=> 'Title 10',
        ]);

        $page11=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $moreExplanationLevel->id,
            'order'=>3
        ]);
        $component11=Component::create([
                'page_id' => $page11->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component11->id,
            'body'=> 'Title 11',
        ]);

        $page12=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $moreExplanationLevel->id,
            'order'=>4
        ]);
        $component12=Component::create([
                'page_id' => $page12->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component12->id,
            'body'=> 'Title 12',
        ]);

        $page13=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $moreExplanationLevel->id,
            'order'=>5
        ]);
        $component13=Component::create([
                'page_id' => $page13->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component13->id,
            'body'=> 'Title 13',
        ]);

        $page14=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $moreExplanationLevel->id,
            'order'=>6
        ]);
        $component14=Component::create([
                'page_id' => $page14->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component14->id,
            'body'=> 'Title 14',
        ]);

        $page15=Page::create([
            'lesson_id'=>$content->lesson->id,
            'explanation_level_id' => $moreExplanationLevel->id,
            'order'=>7
        ]);
        $component15=Component::create([
                'page_id' => $page15->id,
                'order' => 1,
                'type' => 'textarea'
            ]
        );
        TextArea::create([
            'component_id' => $component15->id,
            'body'=> 'Title 15',
        ]);

    }
}
