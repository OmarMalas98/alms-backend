<?php

namespace Database\Factories\Content;

use App\Models\Content\Content;
use App\Models\Content\Lesson;
use App\Models\ExplanationLevel;
use App\Models\LearningObjective;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition()
    {
        $title = $this->faker->sentence;
        $duration = $this->faker->numberBetween(5, 30);

        return [
            'title' => $title,
            'description' => $this->faker->paragraph,
            'duration' => $duration,
            'status_id' => 1,
            'creator_id' => 1,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Lesson $lesson) {
            $levels = ['simple', 'medium', 'more explanation'];
            foreach ($levels as $level) {
                ExplanationLevel::create([
                    'lesson_id' => $lesson->id,
                    'level' => $level,
                ]);
            }

            $title = $lesson->title;
            $lesson->content->update(['title' => $title]);

        });
    }

    public function withPrentAndOrder(int $parentId, int $order): self
    {
        return $this->state(function (array $attributes) use ($parentId, $order) {
            return [
                'content_id' => Content::factory()->create([
                    'title' => $attributes['title'],
                    'content_type' => 'lesson',
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
}
