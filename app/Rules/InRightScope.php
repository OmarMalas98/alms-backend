<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class InRightScope implements ValidationRule
{
    protected $request;
    public function __construct(Request $request) {
        $this->request = $request;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $items = count($this->request->right_options);
        if ($value > $items) {
            $fail('the given index of the right option must not be greater than the given array length');
        }
    }
}
