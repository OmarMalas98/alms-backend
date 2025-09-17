<?php

namespace App\Models;

use App\Models\Components\Page;
use App\Models\Components\Question\MultiChoice\MultiChoiceQuestion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use phpDocumentor\Reflection\Types\Boolean;

class LearningObjective extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'zone_id'];


    /**
     * Get the course associated with the LearningObjective
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function achievedObjectives()
    {
        return $this->hasMany(AchievedObjective::class);
    }

    public function getUserScore()
    {
        // Get the authenticated user's ID.
        $userId = Auth()->user()->id;

        // Check if there is an AchievedObjective for the current user and this learning objective.
        $achievedObjective = $this->achievedObjectives()->where('user_id', $userId)->first();

        // If an AchievedObjective exists, return the score; otherwise, return null.
        return $achievedObjective ? $achievedObjective->score : null;
    }

    public function updateScoreForUser($score)
    {
        // Get the authenticated user's ID.
        $userId = Auth()->user()->id;

        // Find the AchievedObjective for the current user and this learning objective.
        $achievedObjective = $this->achievedObjectives()->where('user_id', $userId)->first();

        // If an AchievedObjective exists, update the score; otherwise, create a new one.
        if ($achievedObjective) {
            $achievedObjective->update(['score' => $score]);
        } else {
            $this->achievedObjectives()->create(['user_id' => $userId, 'score' => $score]);
        }
    }

    public function available(): bool
    {
        $parents = $this->parents;
        if ($parents->isEmpty()) {
            // If there are no parents, consider it as achieved.
            return true;
        }

        foreach ($parents as $parent) {
            $achieved = $parent->achievedObjectives->where('score','>',75)->contains('user_id', auth()->user()->id);

            if (!$achieved) {
                // If any parent is not achieved, return false.
                return false;
            }
        }

        // If all parents are achieved, return true.
        return true;
    }

    /**
     * Get the zone associated with the LearningObjective
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function parents()
    {
        return $this->belongsToMany(self::class, 'objective_dependencies', 'objective_id', 'parent_objective_id');
    }
    /**
     * Get all of the dependencies for the LearningObjective
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dependencies(): HasMany
    {
        return $this->hasMany(ObjectivesDependency::class, 'objective_id');
    }





    public function children()
    {
        return $this->belongsToMany(self::class, 'objective_dependencies', 'parent_objective_id', 'objective_id');
    }

    public function recursiveChildren()
    {
        return $this->belongsToMany(self::class, 'objective_dependencies', 'parent_objective_id', 'objective_id')->with('parents.zone')->with('recursiveChildren')->with('parents');
    }

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function questions()
    {
        return $this->hasMany(Page::class)->where('is_question', 1);
    }

    public function firstPage(bool $asc)
    {
        $attemptsCount = QuestionAttempt::where('user_id', auth()->user()->id)
            ->where('learning_objective_id', $this->id)
            ->count();

        $maxExplanationLevel = Page::where('learning_objective_id', $this->id)
            ->max('explanation_level');

        if ($asc){
            $explanationLevel = Page::where('explanation_level', $attemptsCount + 1)
                ->where('learning_objective_id', $this->id)
                ->exists();

            if (!$explanationLevel) {
                $explanationLevel = $maxExplanationLevel;
            }else{
                $explanationLevel = $attemptsCount+1;
            }
        }
        else{
            $explanationLevel = Page::where('explanation_level', $maxExplanationLevel - $attemptsCount)
                ->where('learning_objective_id', $this->id)
                ->exists();

            if (!$explanationLevel) {
                $explanationLevel = 1;
            }else{
                $explanationLevel = $maxExplanationLevel - $attemptsCount;
            }
        }


    // Now you can get the first page based on the determined explanation level.
        $firstPage = Page::where('explanation_level', $explanationLevel)
            ->where('learning_objective_id', $this->id)
            ->whereHas('components', function ($query) {
                $query->where('is_suggested', false);
            })
            ->orderBy('order', 'asc')
            ->first();

        if (!$firstPage)
            return null;
        $firstPage->load('components');
        $firstPage->components->each(function ($component) {
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

        return $firstPage;
    }
    public function isParent($id){

        foreach ($this->children as $child)
        {
            if ($child->id==$id)
                return true;
        }
        return false;
    }


    public function firstQuestion(){
        $learningObjectiveId = $this->id;
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
                            $shuffledItems = $reorderingQuestion->items->shuffle();
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

    public function fail($score){
        $parents=$this->parents()->get();
        $finalScore = max(1, $score);
        foreach ($parents as $parent) {
            $parent->updateScoreForUser($parent->getUserScore() - $finalScore);
            $parent->fail($finalScore-1);
        }
    }

    public function isConnectedToObjective($targetObjectiveId, $visitedObjectives = [])
    {
        // Check if the current objective has already been visited to avoid infinite loops.
        if (in_array($this->id, $visitedObjectives)) {
            return false;
        }

        // Add the current objective to the list of visited objectives.
        $visitedObjectives[] = $this->id;

        // Check if the current objective's ID is the same as the target objective's ID.
        if ($this->id === $targetObjectiveId) {
            return true;
        }

        // Check if any of the parents are connected to the target objective.
        foreach ($this->parents as $parentObjective) {
            if ($parentObjective->isConnectedToObjective($targetObjectiveId, $visitedObjectives)) {
                return true;
            }
        }

        // If no parent is connected to the target objective, return false.
        return false;
    }
    public function getParent($parentObjective){
        $myParents = $this->parents;
        $parents=$parentObjective->parents;
        foreach ($myParents as $myParent){
            if ($myParent->id==$parentObjective->id)
                continue;
            foreach ($parents as $parent) {
                if ($parent->id == $myParent->id) {
                    $this->parents()->detach($parent->id);
                    break;
                }
                }

        }
    }
    public function linkChildrenWithParents() {
        $children = $this->children;
        $parents = $this->parents;
        foreach ($children as $child) {
            $child->dependencies()->delete();
            foreach ($parents as $parent) {
                $child->parents()->attach($parent);
                $child->save();
            }
        }
    }
    public function lastPage()
    {
        $page = $this->pages->sortByDesc('order')->first();
        return $page;
    }

}









