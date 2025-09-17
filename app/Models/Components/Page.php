<?php

namespace App\Models\Components;

use App\Models\Components\Question\MultiChoice\MultiChoiceQuestion;
use App\Models\Content\Lesson;
use App\Models\CrossQuestion;
use App\Models\LearningObjective;
use App\Models\QuestionAttempt;
use App\Models\ReorderingQuestion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class Page extends Model
{
    use HasFactory;
    protected $fillable = ['learning_objective_id','explanation_level','order','is_question'];


    /**
     * Get the expLevel that owns the Page
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    /**
     * Get all of the components for the Page
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function components(): HasMany
    {
        return $this->hasMany(Component::class)->where('is_suggested', false);
    }
    public function suggestedComponents(): HasMany
    {
        return $this->hasMany(Component::class)->where('is_suggested', true);
    }
    /**
     * Get the expLevel that owns the Page
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function learning_objective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class,);
    }
    public function nextPage(){
        $nextPage = Page::where('explanation_level', $this->explanation_level)
            ->where('order', '>', $this->order)
            ->where('learning_objective_id', $this->learning_objective_id)
            ->where('is_question', 0) // Ensuring the pages are after the current page
            ->whereHas('components', function ($query) {
                $query->where('is_suggested', false);
            })
            ->orderBy('order', 'asc')
            ->first();

        if (!$nextPage){
            return null;
        }
        $nextPage->load('components');
        $nextPage->components->each(function ($component) {
            switch ($component->type) {
                case 'video':
                    $component->load('video');
                    $component->video->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                case 'textarea':
                    $component->load('textarea');
                    $component->textarea->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                case 'title':
                    $component->load('title');
                    $component->title->makeHidden(['component_id', 'created_at', 'updated_at']);
                    break;
                // Add more cases for other types if needed
            }
            unset($component['page_id']);
            unset($component['created_at']);
            unset($component['updated_at']);
        });
        return $nextPage;
    }

    // Add this method to the Page model
    public function nextQuestion()
    {
        $learningObjectiveId = $this->learning_objective_id;
        $userId = auth()->user()->id;

        // Get all the question pages for the learning objective.
        $questionPages = Page::where('learning_objective_id', $learningObjectiveId)
            ->where('is_question', 1)
            ->whereHas('components', function ($query) {
                $query->where('is_suggested', false);
            })
            ->orderBy('order', 'asc')
            ->get();

        // Get the IDs of attempted questions for the user.
        $attemptedQuestionIds = QuestionAttempt::where('user_id', $userId)
            ->where('learning_objective_id', $learningObjectiveId)
            ->pluck('question_id');

        // Separate attempted and unattempted question pages.
        $attemptedQuestionPages = $questionPages->filter(function ($page) use ($attemptedQuestionIds) {
            $questionId = $page->components->first()->question->id;
            return $attemptedQuestionIds->contains($questionId);
        });

        $unattemptedQuestionPages = $questionPages->diff($attemptedQuestionPages);

        // Sort attempted question pages by the number of attempts and then the oldest attempt's timestamp.
        $attemptedQuestionPages = $attemptedQuestionPages->sortBy(function ($page) use ($userId) {
            $questionId = $page->components->first()->question->id;
            $attemptsCount = QuestionAttempt::where('user_id', $userId)
                ->where('learning_objective_id', $page->learning_objective_id)
                ->where('question_id', $questionId)
                ->count();

            $oldestAttempt = QuestionAttempt::where('user_id', $userId)
                ->where('learning_objective_id', $page->learning_objective_id)
                ->where('question_id', $questionId)
                ->orderBy('created_at', 'asc')
                ->first();

            return [$attemptsCount, $oldestAttempt->created_at];
        });

        // Get the first question page with the least attempts and oldest attempt timestamp.
        $nextQuestionPage = $unattemptedQuestionPages->merge($attemptedQuestionPages)->first();
        if (!$nextQuestionPage)
            return null;
        $nextQuestionPage->load('components');
        $nextQuestionPage->components->each(function ($component) {
            switch ($component->type) {
                case 'question':
                    $component->load('question');
                    $question = $component->question->makeHidden(['id','component_id', 'created_at', 'updated_at']);
                    switch ($question->type){
                        case 'multi-choice':
                            $multiquestion = $question->multiChoiceQuestion;
                            $options = $multiquestion->options->map(function ($option) {
                                return collect($option)->except('is_correct', 'updated_at', 'created_at')->toArray();
                            })->toArray();
                            shuffle($options);
                            $shuffledMultiQuestion = new MultiChoiceQuestion();
                            $shuffledMultiQuestion->id = $multiquestion->id;
                            $shuffledMultiQuestion->question_id = $multiquestion->question_id;
                            $shuffledMultiQuestion->text = $multiquestion->text;
                            $shuffledMultiQuestion->options = $options;
                            $question->multiChoiceQuestion = $shuffledMultiQuestion;
                            break;
                        case 'blank-question':
                            $question->blankQuestion;
                            break;
                        case 'cross-question':
                            $crossQuestion = $question->crossQuestion;
                            $crossQuestion->right = Arr::shuffle($crossQuestion->rightOptions->toArray());
                            $crossQuestion->left = Arr::shuffle($crossQuestion->leftOptions->makeHidden('right_option_id')->toArray());
                            $crossQuestion->makeHidden('rightOptions');
                            $crossQuestion->makeHidden('leftOptions');
                            $question->crossQuestion = $crossQuestion;
                            break;
                        case 'reorder-question':
                            $reorderingQuestion=$question->reorderQuestion;
                            // Hide specific attributes from the "question" object
                            $reorderingQuestion->makeHidden(['id', 'created_at', 'updated_at']);
                            $shuffledItems = $reorderingQuestion->first()->items->shuffle();
                            $shuffledItems->each(function ($item) {
                                $item->makeHidden(['reordering_question_id', 'created_at', 'updated_at','order']);
                            });
                            $reorderingQuestion->makeHidden('items');
                            $reorderingQuestion->lines = $shuffledItems;
                            break;
                    }
                    break;
                // Add more cases for other types if needed
            }
            unset($component['page_id']);
            unset($component['created_at']);
            unset($component['updated_at']);
        });
        return $nextQuestionPage;
    }


}
