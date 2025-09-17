<?php

namespace App\Http\Requests;

use App\Models\Assessment;
use App\Models\Lead;
use App\Models\ServiceAssessmentTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user can create assessments for their company
        if (!auth()->user()->can('create assessments')) {
            return false;
        }

        // If lead_id is provided, check if user can access that lead
        if ($this->filled('lead_id')) {
            $lead = Lead::find($this->lead_id);
            if (!$lead || !auth()->user()->can('view', $lead)) {
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
                'required',
                'string',
                Rule::in(Assessment::getServiceTypes()),
            ],
            'property_type' => [
                'required',
                'string',
                Rule::in(['residential', 'commercial', 'industrial', 'institutional']),
            ],
            'urgency_level' => [
                'required',
                'string',
                Rule::in(['low', 'medium', 'high', 'emergency']),
            ],
            'status' => [
                'required',
                'string',
                Rule::in(Assessment::getStatuses()),
            ],

            // Client Information
            'client_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-\.\']+$/', // Names only
            ],
            'client_phone' => [
                'required',
                'string',
                'regex:/^(\+?6?0)[0-9]{9,10}$/', // Malaysian phone format
            ],
            'client_email' => [
                'nullable',
                'email',
                'max:100',
            ],

            // Location Information
            'location_address' => [
                'required',
                'string',
                'max:500',
            ],
            'location_city' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'location_state' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\']+$/',
            ],
            'location_postal_code' => [
                'required',
                'string',
                'regex:/^[0-9]{5}$/', // Malaysian postal code
            ],
            'location_coordinates' => [
                'nullable',
                'string',
                'regex:/^-?[0-9]+\.?[0-9]*,-?[0-9]+\.?[0-9]*$/', // lat,lng format
            ],

            // Assessment Details
            'assessment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . now()->addMonths(6)->format('Y-m-d'),
            ],
            'estimated_duration' => [
                'required',
                'integer',
                'min:15', // Minimum 15 minutes
                'max:480', // Maximum 8 hours
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
                'max:10', // Maximum 10 risk factors
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
            'assessment_date.after_or_equal' => 'Assessment date cannot be in the past.',
            'assessment_date.before_or_equal' => 'Assessment date cannot be more than 6 months in the future.',
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

        // Set default status if not provided
        if (!$this->filled('status')) {
            $this->merge(['status' => Assessment::STATUS_DRAFT]);
        }

        // Set default urgency if not provided
        if (!$this->filled('urgency_level')) {
            $this->merge(['urgency_level' => 'medium']);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate service-specific requirements
            $this->validateServiceSpecificRequirements($validator);
            
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
        // Waterproofing assessments require area measurements
        if (!$this->filled('total_area')) {
            $validator->errors()->add('total_area', 'Total area is required for waterproofing assessments.');
        }

        // High urgency waterproofing needs special attention
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
        // Painting assessments should have area measurements
        if ($this->filled('total_area') && $this->input('total_area') > 10000) {
            // Large painting projects need special requirements
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
        // Sports courts must have area measurements
        if (!$this->filled('total_area')) {
            $validator->errors()->add('total_area', 'Total area is required for sports court assessments.');
        }

        // Sports courts need access considerations
        if (!$this->filled('access_restrictions')) {
            $validator->errors()->add('access_restrictions', 'Access restrictions information is required for sports court assessments.');
        }
    }

    /**
     * Validate industrial-specific requirements.
     */
    protected function validateIndustrialRequirements($validator): void
    {
        // Industrial assessments require safety documentation
        if (!$this->filled('safety_concerns')) {
            $validator->errors()->add('safety_concerns', 'Safety concerns must be documented for industrial assessments.');
        }

        // Industrial projects need risk assessment
        if (!$this->filled('overall_risk_score')) {
            $validator->errors()->add('overall_risk_score', 'Risk score assessment is required for industrial projects.');
        } elseif ($this->input('overall_risk_score') >= 7) {
            // High-risk industrial assessments need special approval
            if (!$this->filled('special_requirements')) {
                $validator->errors()->add('special_requirements', 'Special requirements must be specified for high-risk industrial assessments (risk score ≥7).');
            }
        }
    }

    /**
     * Validate business logic constraints.
     */
    protected function validateBusinessLogic($validator): void
    {
        // Check for duplicate assessments at same location on same date
        if ($this->filled(['location_address', 'assessment_date'])) {
            $existingAssessment = Assessment::where('company_id', auth()->user()->company_id)
                ->where('location_address', $this->input('location_address'))
                ->whereDate('assessment_date', $this->input('assessment_date'))
                ->where('status', '!=', Assessment::STATUS_CANCELLED)
                ->first();

            if ($existingAssessment) {
                $validator->errors()->add('assessment_date', 'An assessment is already scheduled at this location on the selected date.');
            }
        }

        // Validate completion percentage vs status
        $status = $this->input('status');
        $completionPercentage = $this->input('completion_percentage', 0);

        if ($status === Assessment::STATUS_COMPLETED && $completionPercentage < 100) {
            $validator->errors()->add('completion_percentage', 'Completion percentage must be 100% for completed assessments.');
        }

        if ($status === Assessment::STATUS_IN_PROGRESS && $completionPercentage >= 100) {
            $validator->errors()->add('status', 'Assessment cannot be in progress if completion percentage is 100%.');
        }

        // Validate template compatibility
        if ($this->filled('service_template_id')) {
            $template = ServiceAssessmentTemplate::find($this->input('service_template_id'));
            if ($template && $template->service_type !== $this->input('service_type')) {
                $validator->errors()->add('service_template_id', 'Selected template is not compatible with the chosen service type.');
            }
        }
    }
}