<?php

namespace Teksite\Extralaravel\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordRule implements ValidationRule
{
    private readonly int $minLength;

    private readonly int $maxLength;


    public function __construct(?int $minLength = null, ?int $maxLength = null)
    {
        $this->minLength = max(1, $minLength ?? (int) config('extralaravel.password.min_length', 8));
        $this->maxLength = max($this->minLength, $maxLength ?? (int) config('extralaravel.password.max_length', 128));
    }


    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('validation.string')->translate();
            return;
        }

        $length = mb_strlen($value);

        if ($length > $this->maxLength) {
            $fail('The :attribute must not be greater than :max characters.')->translate(['max' => $this->maxLength]);
            return;
        }

        if ($length < $this->minLength) {
            $fail('The :attribute must be at least :min characters.')->translate(['min' => $this->minLength]);
        }

        if (preg_match('/[a-z]/', $value) !== 1) {
            $fail('The :attribute must contain at least one lowercase letter.')->translate();
        }

        if (preg_match('/[A-Z]/', $value) !== 1) {
            $fail('The :attribute must contain at least one uppercase letter.')->translate();
        }

        if (preg_match('/[0-9]/', $value) !== 1) {
            $fail('The :attribute must contain at least one number.')->translate();
        }

        if (preg_match('/[^\p{L}\p{N}]/u', $value) !== 1) {
            $fail('The :attribute must contain at least one symbol.')->translate();
        }

    }


}
