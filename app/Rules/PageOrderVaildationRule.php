<?php

namespace App\Rules;

use App\Models\Components\Page;
use App\Models\Content\Content;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PageOrderVaildationRule implements ValidationRule
{
    protected $parent_id;
    protected $method;

    public function __construct($parent_id,$explanationLevel,$method)
    {
        $this->parent_id = $parent_id;
        $this->method = $method;
        $this->explanationLevel = $explanationLevel;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $siblingRecord = Page::where('lesson_id', Content::find($this->parent_id)->lesson->id)
            ->where('explanation_level_id', $this->explanationLevel->id)
            ->orderByDesc('order')->first();

        if ($this->method == 'add') {
            if ($siblingRecord) {
                if ($value <= 0 || $value > $siblingRecord->order+1) {
                    $fail("Value of Order can't be negative or greater than 1 + the sibling record's order");
                }
            }
        }else{
            if ($siblingRecord) {
                if ($value <= 0 || $value > $siblingRecord->order) {
                    $fail("Value of Order can't be negative or greater than the sibling record's order");
                }
            }
        }
        if(!$value > 0){
            $fail("Value of Order can't be negative");
        }
        if (!$siblingRecord && $value > 1) {
            $fail("Value of Order can't be greater than 1 because no siblings present");
        }

    }

}
