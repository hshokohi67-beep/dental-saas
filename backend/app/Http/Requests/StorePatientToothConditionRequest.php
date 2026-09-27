<?php

namespace App\Http\Requests;

use App\Domain\Dental\Support\DentalChartAccessPolicy;
use App\Domain\Dental\Support\ToothNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class StorePatientToothConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return DentalChartAccessPolicy::canManage($this->user(), $this->route('patient'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dental_condition_catalog_id' => ['required', 'string', Rule::exists('dental_condition_catalog', 'id')],
            'scope_type' => ['required', Rule::in(['tooth', 'quadrant', 'arch', 'whole_mouth'])],
            'tooth_number' => [
                'required_if:scope_type,tooth', 'integer',
                function ($attribute, $value, $fail) {
                    if ($value === null) {
                        return;
                    }

                    try {
                        ToothNumber::fromFdi((int) $value);
                    } catch (InvalidArgumentException $e) {
                        $fail($e->getMessage());
                    }
                },
            ],
            // Legacy only ever used quadrants 1-4 (the permanent numbering) for half-arch findings.
            'quadrant' => ['required_if:scope_type,quadrant', Rule::in([1, 2, 3, 4])],
            'arch' => ['required_if:scope_type,arch', Rule::in(['upper', 'lower'])],
            'surfaces' => ['nullable', 'array'],
            'surfaces.*' => [Rule::in(['mesial', 'distal', 'occlusal', 'incisal', 'buccal', 'lingual'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
