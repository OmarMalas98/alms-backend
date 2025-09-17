<?php

namespace App\Rules;

use App\Models\Components\Component;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ComponentOrderValidationRule implements ValidationRule
{
    protected $page_id;
    protected $method;

    public function __construct($page_id,$method)
    {
        $this->page_id = $page_id;
        $this->method = $method;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $siblingRecord = Component::where('page_id', $this->page_id)
        ->orderBy('order', 'desc')->first();
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
    }
}
