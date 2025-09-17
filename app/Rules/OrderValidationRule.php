<?php

namespace App\Rules;

use App\Models\Components\Page;
use App\Models\Content\Content;
use App\Models\LearningObjective;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderValidationRule implements ValidationRule
{

    protected $method;
    protected $learning_objective_id;

    public function __construct($method,$learning_objective_id)
    {
        $this->learning_objective_id = $learning_objective_id;
        $this->method = $method;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $siblingRecord = LearningObjective::find($this->learning_objective_id)->zone->pagesOfZone->sortByDesc('order')->first();
        if ($this->method == 'add') {
            if ($siblingRecord) {
                if ($value <= 0 || (int)$value > $siblingRecord->order+1) {
                    $fail("Value of Order can't be negative or greater than 1 + the sibling record's order");
                }
            }
        }else{
            if ($siblingRecord) {
                if ($value <= 0 || (int)$value > $siblingRecord->order) {
                    $fail("Value of Order can't be negative or greater than the sibling record's order");
                }
            }
        }
        if(!(int)$value > 0){
            $fail("Value of Order can't be negative");
        }
        if (!$siblingRecord && (int)$value > 1) {
            $fail("Value of Order can't be negative or greater than 1 because no siblings present");
        }
    }
}
