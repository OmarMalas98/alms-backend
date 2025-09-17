<?php

namespace Database\Factories\Component;

use App\Models\Components\Component;
use App\Models\Components\TextArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class TextAreaFactory extends Factory
{
    protected $model = TextArea::class;

    public function definition()
    {
        $faker = \Faker\Factory::create();

        $paragraph = $faker->sentence;

        $listItems = [];
        for ($i = 0; $i < 4; $i++) {
            $listItems[] = $faker->sentence;
        }

        $body = "<p>{$paragraph}</p>\n<ul>\n";
        foreach ($listItems as $item) {
            $body .= "<li>{$item}</li>\n";
        }
        $body .= "</ul>";

        return [
            'component_id' => function () {
                return Component::factory()->create([
                    'page_id' => 1,
                    'order' => 2,
                    'type' => 'textarea',
                ])->id;
            },
            'body' => $body,
        ];
    }

    public function withPageAndOrder(int $pageId, int $order): self
    {
        return $this->state(function (array $attributes) use ($pageId, $order) {
            return [
                'component_id' => Component::factory()->create([
                    'page_id' => $pageId,
                    'order' => $order,
                    'type' => 'textarea',
                ])->id,
            ];
        });
    }
}
