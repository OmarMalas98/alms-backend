<?php

namespace Database\Factories\Content;

use App\Models\Content\Assessment;
use App\Models\Content\Content;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        $title = $this->faker->sentence;
        $duration = $this->faker->numberBetween(5, 30);

        return [
            'title' => $title,
            'description' => $this->faker->paragraph,
            'duration'=> $duration,
            'status_id' => 1,
            'creator_id' => 1,
        ];
    }

    public function withPrentAndOrder(int $parentId, int $order): self
    {
        return $this->state(function (array $attributes) use ($parentId, $order) {
            return [
                'content_id' => Content::factory()->create([
                        'title' => $attributes['title'],
                        'content_type' => 'assessment',
                        'parent_id' => $parentId,
                        'order' => $order,
                ])->id,
            ];
        });
    }

    public function withObjective(int $objectiveId): self
    {
        return $this->state(function (array $attributes) use ($objectiveId) {
            return [
                'learning_objective_id' => $objectiveId
            ];
        });
    }

    public function configure()
    {
        return $this->afterCreating(function (Assessment $assessment) {
            $title = $assessment->title;
            $assessment->content->update(['title' => $title]);
        });
    }
}
