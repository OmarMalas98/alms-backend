<?php

namespace Database\Factories\Component;

use App\Models\Components\Component;
use App\Models\Components\Title;
use Illuminate\Database\Eloquent\Factories\Factory;

class TitleFactory extends Factory
{
    protected $model = Title::class;

    public function definition()
    {
        return [
            'body' => $this->faker->sentence,
        ];
    }

    public function withPageAndOrder(int $pageId, int $order): self
    {
        return $this->state(function (array $attributes) use ($pageId, $order) {
            return [
                'component_id' => Component::factory()->create([
                    'page_id' => $pageId,
                    'order' => $order,
                    'type' => 'title',
                ])->id,
            ];
        });
    }
}
