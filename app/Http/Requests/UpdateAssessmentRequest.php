<?php

namespace App\Http\Requests;

use App\Models\Assessment;
use App\Models\Lead;
use App\Models\ServiceAssessmentTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $assessment = $this->route('assessment');
        
        // Check if user can update assessments
        if (!auth()->user()->can('update', $assessment)) {
            return false;
        }

        // Prevent editing of completed or cancelled assessments
        if (in_array($assessment->status, [Assessment::STATUS_COMPLETED, Assessment::STATUS_CANCELLED])) {
            // Only allow limited updates for completed/cancelled assessments
            $allowedFields = ['notes', 'recommendations', 'follow_up_required', 'follow_up_date'];
            $requestFields = array_keys($this->all());
            $restrictedFields = array_diff($requestFields, $allowedFields);
            
            if (!empty($restrictedFields)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $assessment = $this->route('assessment');
        $isCompleted = $assessment && in_array($assessment->status, [Assessment::STATUS_COMPLETED, Assessment::STATUS_CANCELLED]);

        // Limited rules for completed/cancelled assessments
        if ($isCompleted) {
            return [
                'notes' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
                'recommendations' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
                'follow_up_required' => [
                    'nullable',
                    'boolean',
                ],
                'follow_up_date' => [
                    'nullable',
                    'date',
                    'after:today',
                    Rule::requiredIf(fn() => $this->boolean('follow_up_required')),
                ],
            ];
        }

        // Full validation rules for active assessments
        return [
            // Basic Assessment Information
            'lead_id' => [
                'nullable',
                'exists:leads,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $lead = Lead::find($value);
                        if (!$lead || $lead->company_id !== auth()->user()->company_id) {
                            $fail('The selected lead is invalid or not accessible.');
                        }
                    }
                },
            ],
            'service_template_id' => [
                'nullable',
                'exists:service_assessment_templates,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $template = ServiceAssessmentTemplate::find($value);
                        if (!$template || $template->company_id !== auth()->user()->company_id) {
                            $fail('The selected service template is invalid or not accessible.');
                        }
                        if (!$template->is_active) {
                            $fail('The selected service template is not active.');
                        }
                    }
                },
            ],
            'service_type' => [
                'sometimes',
                'required',
                'string',
                Rule::in(Assessment::getServiceTypes()),
            ],
            'property_type' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['residential', 'commercial', 'industrial', 'institutional']),
            ],
            'urgency_level' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['low', 'medium', 'high', 'emergency']),
            ],
            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(Assessment::getStatuses()),
                function ($attribute, $value, $fail) use ($assessment) {
                    // Validate status transitions
                    if ($assessment && !$this->isValidStatusTransition($assessment->status, $value)) {
                        $fail('Invalid status transition from ' . $assessment->status . ' to ' . $value . '.');
                    }
                },
            ],

            // Client Information
            'client_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.\']+$/',
            ],
            'client_phone' => [
                'sometimes',
                'required',
                'string',
                'regex:/^(\+?6?0)[0-9]{9,10}$/',
            ],
            'client_email' => [
                'nullable',
                'email',
                'max:100',
            ],

            // Location Information
            'location_address' => [
                'sometimes',
                'required',
                'string',
                'max:500',
            ],
            'location_city' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'location_state' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'location_postal_code' => [
                'sometimes',
                'required',
                'string',
                'regex:/^[0-9]{5}$/',
            ],
            'location_coordinates' => [
                'nullable',
                'string',
                'regex:/^-?[0-9]+\.?[0-9]*,-?[0-9]+\.?[0-9]*$/',
            ],

            // Assessment Details
            'assessment_date' => [
                'sometimes',
                'required',
                'date',
                function ($attribute, $value, $fail) use ($assessment) {
                    // Only allow past dates for completed assessments
                    if ($assessment && $assessment->status === Assessment::STATUS_COMPLETED) {
                        if (strtotime($value) > time()) {
                            $fail('Assessment date cannot be in the future for completed assessments.');
                        }
                    } else {
                        // For active assessments, date can be today or future (within 6 months)
                        if (strtotime($value) < strtotime('today')) {
                            $fail('Assessment date cannot be in the past for active assessments.');
                        }
                        if (strtotime($value) > strtotime('+6 months')) {
                            $fail('Assessment date cannot be more than 6 months in the future.');
                        }
                    }
                },
            ],
            'estimated_duration' => [
                'sometimes',
                'required',
                'integer',
                'min:15',
                'max:480',
            ],
            'total_area' => [
                'nullable',
                'numeric',
                'min:0.1',
                'max:999999.99',
            ],
            'area_unit' => [
                'nullable',
                Rule::requiredIf(fn() => $this->filled('total_area')),
                'string',
                Rule::in(['sqft', 'sqm', 'acres', 'hectares']),
            ],

            // Risk Assessment
            'overall_risk_score' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'risk_factors' => [
                'nullable',
                'array',
                'max:10',
            ],
            'risk_factors.*' => [
                'required_with:risk_factors',
                'string',
                'max:100',
            ],

            // Notes and Observations
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'special_requirements' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'client_requirements' => [
                'nullable',
                'string',
                'max:1000',
            ],

            // Completion Data
            'completion_percentage' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'recommendations' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'follow_up_required' => [
                'nullable',
                'boolean',
            ],
            'follow_up_date' => [
                'nullable',
                'date',
                'after:today',
                Rule::requiredIf(fn() => $this->boolean('follow_up_required')),
            ],

            // Metadata
            'weather_conditions' => [
                'nullable',
                'string',
                'max:100',
            ],
            'access_restrictions' => [
                'nullable',
                'string',
                'max:500',
            ],
            'safety_concerns' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'client_phone.regex' => 'Please enter a valid Malaysian phone number (e.g., 012-345-6789 or +60123456789).',
            'location_postal_code.regex' => 'Please enter a valid Malaysian postal code (5 digits).',
            'location_coordinates.regex' => 'Coordinates must be in latitude,longitude format (e.g., 3.1390,101.6869).',
            'estimated_duration.min' => 'Assessment duration must be at least 15 minutes.',
            'estimated_duration.max' => 'Assessment duration cannot exceed 8 hours.',
            'client_name.regex' => 'Client name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            'location_city.regex' => 'City name can only contain letters, spaces, hyphens, and apostrophes.',
            'location_state.regex' => 'State name can only contain letters, spaces, hyphens, and apostrophes.',
            'area_unit.required_if' => 'Area unit is required when total area is specified.',
            'follow_up_date.required_if' => 'Follow-up date is required when follow-up is marked as required.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'client_name' => 'client name',
            'client_phone' => 'client phone number',
            'client_email' => 'client email',
            'location_address' => 'location address',
            'location_city' => 'city',
            'location_state' => 'state',
            'location_postal_code' => 'postal code',
            'location_coordinates' => 'GPS coordinates',
            'assessment_date' => 'assessment date',
            'estimated_duration' => 'estimated duration',
            'total_area' => 'total area',
            'area_unit' => 'area unit',
            'overall_risk_score' => 'overall risk score',
            'risk_factors' => 'risk factors',
            'completion_percentage' => 'completion percentage',
            'follow_up_required' => 'follow-up required',
            'follow_up_date' => 'follow-up date',
            'weather_conditions' => 'weather conditions',
            'access_restrictions' => 'access restrictions',
            'safety_concerns' => 'safety concerns',
            'service_template_id' => 'service template',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format phone number
        if ($this->filled('client_phone')) {
            $phone = preg_replace('/[^0-9+]/', '', $this->client_phone);
            $this->merge(['client_phone' => $phone]);
        }

        // Clean and format postal code
        if ($this->filled('location_postal_code')) {
            $postalCode = preg_replace('/[^0-9]/', '', $this->location_postal_code);
            $this->merge(['location_postal_code' => $postalCode]);
        }

        // Format coordinates
        if ($this->filled('location_coordinates')) {
            $coordinates = preg_replace('/\s+/', '', $this->location_coordinates);
            $this->merge(['location_coordinates' => $coordinates]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate service-specific requirements
            if ($this->filled('service_type')) {
                $this->validateServiceSpecificRequirements($validator);
            }
            
            // Validate business logic constraints
            $this->validateBusinessLogic($validator);
        });
    }

    /**
     * Validate service-specific requirements.
     */
    protected function validateServiceSpecificRequirements($validator): void
    {
        $serviceType = $this->input('service_type');

        switch ($serviceType) {
            case 'waterproofing':
                $this->validateWaterproofingRequirements($validator);
                break;
            case 'painting':
                $this->validatePaintingRequirements($validator);
                break;
            case 'sports_court':
                $this->validateSportsCourtRequirements($validator);
                break;
            case 'industrial':
                $this->validateIndustrialRequirements($validator);
                break;
        }
    }

    /**
     * Validate waterproofing-specific requirements.
     */
    protected function validateWaterproofingRequirements($validator): void
    {
        if ($this->input('urgency_level') === 'emergency') {
            if (!$this->filled('safety_concerns')) {
                $validator->errors()->add('safety_concerns', 'Safety concerns must be documented for emergency waterproofing assessments.');
            }
        }
    }

    /**
     * Validate painting-specific requirements.
     */
    protected function validatePaintingRequirements($validator): void
    {
        if ($this->filled('total_area') && $this->input('total_area') > 10000) {
            if (!$this->filled('special_requirements')) {
                $validator->errors()->add('special_requirements', 'Special requirements must be specified for large painting projects (>10,000 area units).');
            }
        }
    }

    /**
     * Validate sports court-specific requirements.
     */
    protected function validateSportsCourtRequirements($validator): void
    {
        // Sports courts need access considerations
        if (!$this->filled('access_restrictions') && !$this->route('assessment')->access_restrictions) {
            $validator->errors()->add('access_restrictions', 'Access restrictions information is required for sports court assessments.');
        }
    }

    /**
     * Validate industrial-specific requirements.
     */
    protected function validateIndustrialRequirements($validator): void
    {
        // Industrial assessments require safety documentation
        if (!$this->filled('safety_concerns') && !$this->route('assessment')->safety_concerns) {
            $validator->errors()->add('safety_concerns', 'Safety concerns must be documented for industrial assessments.');
        }

        // High-risk industrial assessments need special requirements
        if ($this->filled('overall_risk_score') && $this->input('overall_risk_score') >= 7) {
            if (!$this->filled('special_requirements') && !$this->route('assessment')->special_requirements) {
                $validator->errors()->add('special_requirements', 'Special requirements must be specified for high-risk industrial assessments (risk score ≥7).');
            }
        }
    }

    /**
     * Validate business logic constraints.
     */
    protected function validateBusinessLogic($validator): void
    {
        $assessment = $this->route('assessment');

        // Check for duplicate assessments at same location on same date (excluding current assessment)
        if ($this->filled(['location_address', 'assessment_date'])) {
            $existingAssessment = Assessment::where('company_id', auth()->user()->company_id)
                ->where('location_address', $this->input('location_address'))
                ->whereDate('assessment_date', $this->input('assessment_date'))
                ->where('status', '!=', Assessment::STATUS_CANCELLED)
                ->where('id', '!=', $assessment->id)
                ->first();

            if ($existingAssessment) {
                $validator->errors()->add('assessment_date', 'Another assessment is already scheduled at this location on the selected date.');
            }
        }

        // Validate completion percentage vs status
        $status = $this->input('status', $assessment->status);
        $completionPercentage = $this->input('completion_percentage', $assessment->completion_percentage ?? 0);

        if ($status === Assessment::STATUS_COMPLETED && $completionPercentage < 100) {
            $validator->errors()->add('completion_percentage', 'Completion percentage must be 100% for completed assessments.');
        }

        if ($status === Assessment::STATUS_IN_PROGRESS && $completionPercentage >= 100) {
            $validator->errors()->add('status', 'Assessment cannot be in progress if completion percentage is 100%.');
        }

        // Validate template compatibility
        if ($this->filled('service_template_id')) {
            $template = ServiceAssessmentTemplate::find($this->input('service_template_id'));
            $serviceType = $this->input('service_type', $assessment->service_type);
            
            if ($template && $template->service_type !== $serviceType) {
                $validator->errors()->add('service_template_id', 'Selected template is not compatible with the chosen service type.');
            }
        }

        // Prevent backdating for certain statuses
        if ($this->filled('assessment_date') && $status === Assessment::STATUS_SCHEDULED) {
            if (strtotime($this->input('assessment_date')) < strtotime('today')) {
                $validator->errors()->add('assessment_date', 'Scheduled assessments cannot have a past date.');
            }
        }
    }

    /**
     * Check if status transition is valid.
     */
    protected function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        // Define allowed status transitions
        $allowedTransitions = [
            Assessment::STATUS_DRAFT => [
                Assessment::STATUS_SCHEDULED,
                Assessment::STATUS_CANCELLED,
            ],
            Assessment::STATUS_SCHEDULED => [
                Assessment::STATUS_IN_PROGRESS,
                Assessment::STATUS_CANCELLED,
                Assessment::STATUS_RESCHEDULED,
            ],
            Assessment::STATUS_IN_PROGRESS => [
                Assessment::STATUS_COMPLETED,
                Assessment::STATUS_CANCELLED,
                Assessment::STATUS_PAUSED,
            ],
            Assessment::STATUS_PAUSED => [
                Assessment::STATUS_IN_PROGRESS,
                Assessment::STATUS_CANCELLED,
            ],
            Assessment::STATUS_RESCHEDULED => [
                Assessment::STATUS_SCHEDULED,
                Assessment::STATUS_CANCELLED,
            ],
            Assessment::STATUS_COMPLETED => [
                // Completed assessments generally cannot change status
                // except in exceptional circumstances
            ],
            Assessment::STATUS_CANCELLED => [
                // Cancelled assessments generally cannot change status
                // except to be rescheduled as new assessment
            ],
        ];

        // Same status is always allowed
        if ($currentStatus === $newStatus) {
            return true;
        }

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }
}