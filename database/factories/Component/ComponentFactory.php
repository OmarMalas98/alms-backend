<?php

namespace Database\Factories\Component;

use App\Models\Components\Component;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ComponentFactory extends Factory
{
    protected $model = Component::class;

    public function definition(): array
    {
        return [
            'page_id' => 1, // Replace with your logic to get the page ID
            'order' => 1, // Replace with your logic to set the order
            'type' => 'title',
        ];
    }
}
