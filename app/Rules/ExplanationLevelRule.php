<?php

namespace App\Rules;

use App\Models\Components\Page;
use App\Models\LearningObjective;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ExplanationLevelRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    protected $method;
    protected $request;

    public function __construct($method, $request)
    {
        $this->request = $request;
        $this->method = $method;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //
        if ($this->method == 'add') {


            $prevPage = Page::where('order', $this->request->order - 1)->where('learning_objective_id', $this->request->learning_objective_id)->first();
            $nextPage = Page::where('order', $this->request->order)->where('learning_objective_id', $this->request->learning_objective_id)->first();
            while ($prevPage != null && $prevPage->is_question == 1) {
                $prevPage = Page::where('order', $prevPage->order - 1)->where('learning_objective_id', $this->request->learning_objective_id)->first();
            }
            while ($nextPage != null && $nextPage->is_question == 1) {
                $nextPage = Page::where('order', $nextPage->order - 1)->where('learning_objective_id', $this->request->learning_objective_id)->first();
            }
            if (!$prevPage && !$nextPage) {
                if ($this->request->explanation_level != 1)
                    $fail("new page explanation level must be 1");
            } else if (!$prevPage) {
                if ($nextPage->explanation_level < $this->request->explanation_level)
                    $fail("new page explanation level should be smaller or equal to the next page");
            } else if (!$nextPage) {
                if ($prevPage->explanation_level > $this->request->explanation_level)
                    $fail("new page explanation level should be bigger or equal to the prev page");
            } else
                if ($prevPage->explanation_level != $this->request->explanation_level && $nextPage->explanation_level != $this->request->explanation_level)
                $fail("new page explanation level should be equal to the prev page or to the next page");
        }

        if ($this->method == 'update') {


            $prevPage = Page::where('order', $this->request->order - 1)->where('learning_objective_id', $this->request->learning_objective_id)->first();
            $nextPage = Page::where('order', $this->request->order + 1)->where('learning_objective_id', $this->request->learning_objective_id)->first();
            while ($prevPage->is_question == 1) {
                $prevPage = Page::where('order', $prevPage->order - 1)->where('learning_objective_id', $this->request->learning_objective_id)->first();
            }
            while ($nextPage->is_question == 1) {
                $nextPage = Page::where('order', $nextPage->order + 1)->where('learning_objective_id', $this->request->learning_objective_id)->first();
            }
            if (!$prevPage && !$nextPage) {
                if ($this->request->explanation_level != 1)
                    $fail("new page explanation level must be 1");
            } else if (!$prevPage) {
                if ($nextPage->explanation_level < $this->request->explanation_level)
                    $fail("new page explanation level should be smaller or equal to the next page");
            } else if (!$nextPage) {
                if ($prevPage->explanation_level > $this->request->explanation_level)
                    $fail("new page explanation level should be bigger or equal to the prev page");
            } else
                if ($prevPage->explanation_level != $this->request->explanation_level && $nextPage->explanation_level != $this->request->explanation_level)
                $fail("new page explanation level should be equal to the prev page or to the next page");
        }
    }
}
