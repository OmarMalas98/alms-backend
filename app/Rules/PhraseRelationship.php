<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

use function PHPUnit\Framework\isNull;

class PhraseRelationship implements ValidationRule
{
    protected $request;
    public function __construct($request)
    {
        $this->request = $request;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // if (collect($this->rightOptions)->where('id', $leftOptionId)->count() != 1) {
        //     $fail("each option must have one and only one option accocciated with it");
        // }

    }
}
