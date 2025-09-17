<?php

namespace Database\Factories\Component;

use App\Models\Components\Component;
use App\Models\Components\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'url'=>'https://youtu.be/GIbD5seHH-E'
        ];
    }

    public function withPageAndOrder(int $pageId, int $order): self
    {
        return $this->state(function (array $attributes) use ($pageId, $order) {
            return [
                'component_id' => Component::factory()->create([
                    'page_id' => $pageId,
                    'order' => $order,
                    'type' => 'video',
                ])->id,
            ];
        });
    }
}
