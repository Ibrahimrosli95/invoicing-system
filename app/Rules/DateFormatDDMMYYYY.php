<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Helpers\DateHelper;

class DateFormatDDMMYYYY implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Let required validation handle empty values
        }

        if (!DateHelper::validateFormat($value)) {
            $fail('The :attribute must be a valid date in DD/MM/YYYY format.');
        }
    }
}
