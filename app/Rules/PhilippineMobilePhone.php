<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhilippineMobilePhone implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $number = trim((string) $value);

        if (! preg_match('/^(?:\+639\d{9}|09\d{9})$/', $number)) {
            $fail('The :attribute field must be a valid Philippine mobile number in 09XXXXXXXXX or +639XXXXXXXXX format.');
        }
    }
}
