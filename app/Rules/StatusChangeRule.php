<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StatusChangeRule implements ValidationRule
{
    protected $type;
    protected $content;

    public function __construct($type,$content)
    {
        $this->type = $type;
        $this->content = $content;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
           $children = $this->content->content->children;
           if (!$children->contains('content_type','lesson') && $value == 2) {
            $fail("can't publish course with no lessons");
           
        }
    }
}
