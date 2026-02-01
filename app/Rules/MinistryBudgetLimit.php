<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Services\MinistryBudgetValidationService;

class MinistryBudgetLimit implements ValidationRule
{
    protected $fiscalYearId;
    protected $rpoUnitId;
    protected $budgetTypeId;
    protected $economicCodeId;

    public function __construct($fiscalYearId, $rpoUnitId, $budgetTypeId, $economicCodeId)
    {
        $this->fiscalYearId = $fiscalYearId;
        $this->rpoUnitId = $rpoUnitId;
        $this->budgetTypeId = $budgetTypeId;
        $this->economicCodeId = $economicCodeId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Don't validate if value is empty/zero to allow clearing fields
        if (empty($value) || $value <= 0) {
            return;
        }

        $validator = new MinistryBudgetValidationService();
        $result = $validator->validateRelease(
            $this->fiscalYearId,
            $this->rpoUnitId,
            $this->budgetTypeId,
            $this->economicCodeId,
            (float) $value
        );

        if (!$result['valid']) {
            $fail($result['message']);
        }
    }
}
