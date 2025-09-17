<?php

namespace App\Http\Requests;

use App\Models\Assessment;
use App\Models\AssessmentSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentSectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $section = $this->route('section');
        $assessment = $section->assessment;
        
        // Check if user can update the assessment
        if (!auth()->user()->can('update', $assessment)) {
            return false;
        }

        // Prevent editing of completed or cancelled assessments
        if (in_array($assessment->status, [Assessment::STATUS_COMPLETED, Assessment::STATUS_CANCELLED])) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $section = $this->route('section');
        
        return [
            // Basic Section Information
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('assessment_sections')
                    ->where('assessment_id', $section->assessment_id)
                    ->ignore($section->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'section_type' => [
                'sometimes',
                'required',
                'string',
                Rule::in([
                    'overview',
                    'structural',
                    'surface',
                    'environmental',
                    'safety',
                    'equipment',
                    'materials',
                    'measurements',
                    'documentation',
                    'recommendations',
                    'custom',
                ]),
            ],
            'sort_order' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            // Scoring and Assessment
            'max_score' => [
                'nullable',
                'numeric',
                'min:1',
                'max:100',
                function ($attribute, $value, $fail) {
                    // Ensure max_score is greater than current_score
                    $currentScore = $this->input('current_score', $this->route('section')->current_score);
                    if ($value && $currentScore && $value < $currentScore) {
                        $fail('Maximum score cannot be less than the current score.');
                    }
                },
            ],
            'current_score' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    // Ensure current_score doesn't exceed max_score
                    $maxScore = $this->input('max_score', $this->route('section')->max_score);
                    if ($value && $maxScore && $value > $maxScore) {
                        $fail('Current score cannot exceed the maximum score.');
                    }
                },
            ],
            'weight' => [
                'nullable',
                'numeric',
                'min:0.1',
                'max:10.0',
            ],

            // Status and Completion
            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['pending', 'in_progress', 'completed', 'skipped', 'failed']),
            ],
            'completion_percentage' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
                function ($attribute, $value, $fail) {
                    $status = $this->input('status', $this->route('section')->status);
                    
                    // Validate completion percentage vs status
                    if ($status === 'completed' && $value < 100) {
                        $fail('Completion percentage must be 100% for completed sections.');
                    }
                    
                    if ($status === 'pending' && $value > 0) {
                        $fail('Pending sections should have 0% completion.');
                    }
                },
            ],

            // Quality and Risk Assessment
            'quality_rating' => [
                'nullable',
                'string',
                Rule::in(['excellent', 'good', 'fair', 'poor', 'critical']),
            ],
            'risk_level' => [
                'nullable',
                'string',
                Rule::in(['low', 'medium', 'high', 'critical']),
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],

            // Documentation and Notes
            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'observations' => [
                'nullable',
                'string',
                'max:1500',
            ],
            'recommendations' => [
                'nullable',
                'string',
                'max:1500',
            ],

            // Technical Specifications
            'required_certifications' => [
                'nullable',
                'array',
                'max:10',
            ],
            'required_certifications.*' => [
                'required_with:required_certifications',
                'string',
                'max:100',
            ],
            'compliance_standards' => [
                'nullable',
                'array',
                'max:10',
            ],
            'compliance_standards.*' => [
                'required_with:compliance_standards',
                'string',
                'max:100',
            ],

            // Time Tracking
            'estimated_time' => [
                'nullable',
                'integer',
                'min:5', // Minimum 5 minutes
                'max:480', // Maximum 8 hours
            ],
            'actual_time_spent' => [
                'nullable',
                'integer',
                'min:0',
                'max:480',
            ],

            // Requirements and Dependencies
            'is_required' => [
                'nullable',
                'boolean',
            ],
            'depends_on_sections' => [
                'nullable',
                'array',
                'max:10',
            ],
            'depends_on_sections.*' => [
                'required_with:depends_on_sections',
                'exists:assessment_sections,id',
                function ($attribute, $value, $fail) {
                    $section = $this->route('section');
                    
                    // Prevent circular dependencies
                    if ($value == $section->id) {
                        $fail('A section cannot depend on itself.');
                    }
                    
                    // Ensure dependency belongs to same assessment
                    $dependencySection = AssessmentSection::find($value);
                    if ($dependencySection && $dependencySection->assessment_id !== $section->assessment_id) {
                        $fail('Section dependencies must be from the same assessment.');
                    }
                },
            ],

            // Metadata
            'metadata' => [
                'nullable',
                'array',
                'max:20', // Maximum 20 metadata fields
            ],
            'metadata.*' => [
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
            'name.unique' => 'A section with this name already exists in this assessment.',
            'name.max' => 'Section name cannot exceed 100 characters.',
            'description.max' => 'Section description cannot exceed 1000 characters.',
            'max_score.min' => 'Maximum score must be at least 1.',
            'max_score.max' => 'Maximum score cannot exceed 100.',
            'current_score.min' => 'Current score cannot be negative.',
            'weight.min' => 'Section weight must be at least 0.1.',
            'weight.max' => 'Section weight cannot exceed 10.0.',
            'completion_percentage.min' => 'Completion percentage cannot be negative.',
            'completion_percentage.max' => 'Completion percentage cannot exceed 100.',
            'notes.max' => 'Notes cannot exceed 2000 characters.',
            'observations.max' => 'Observations cannot exceed 1500 characters.',
            'recommendations.max' => 'Recommendations cannot exceed 1500 characters.',
            'estimated_time.min' => 'Estimated time must be at least 5 minutes.',
            'estimated_time.max' => 'Estimated time cannot exceed 8 hours.',
            'actual_time_spent.max' => 'Actual time spent cannot exceed 8 hours.',
            'required_certifications.max' => 'Cannot have more than 10 required certifications.',
            'compliance_standards.max' => 'Cannot have more than 10 compliance standards.',
            'depends_on_sections.max' => 'Cannot depend on more than 10 sections.',
            'metadata.max' => 'Cannot have more than 20 metadata fields.',
            'metadata.*.max' => 'Each metadata value cannot exceed 500 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'section name',
            'description' => 'section description',
            'section_type' => 'section type',
            'sort_order' => 'sort order',
            'max_score' => 'maximum score',
            'current_score' => 'current score',
            'weight' => 'section weight',
            'status' => 'section status',
            'completion_percentage' => 'completion percentage',
            'quality_rating' => 'quality rating',
            'risk_level' => 'risk level',
            'priority' => 'priority level',
            'notes' => 'notes',
            'observations' => 'observations',
            'recommendations' => 'recommendations',
            'required_certifications' => 'required certifications',
            'compliance_standards' => 'compliance standards',
            'estimated_time' => 'estimated time',
            'actual_time_spent' => 'actual time spent',
            'is_required' => 'required section',
            'depends_on_sections' => 'section dependencies',
            'metadata' => 'metadata',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert boolean flags
        if ($this->has('is_required')) {
            $this->merge(['is_required' => $this->boolean('is_required')]);
        }

        // Clean up arrays
        if ($this->filled('required_certifications')) {
            $certifications = array_filter($this->input('required_certifications'), fn($cert) => !empty(trim($cert)));
            $this->merge(['required_certifications' => array_values($certifications)]);
        }

        if ($this->filled('compliance_standards')) {
            $standards = array_filter($this->input('compliance_standards'), fn($std) => !empty(trim($std)));
            $this->merge(['compliance_standards' => array_values($standards)]);
        }

        if ($this->filled('depends_on_sections')) {
            $dependencies = array_filter($this->input('depends_on_sections'), fn($dep) => !empty($dep));
            $this->merge(['depends_on_sections' => array_values($dependencies)]);
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
            
            // Validate scoring consistency
            $this->validateScoringLogic($validator);
        });
    }

    /**
     * Validate service-specific requirements.
     */
    protected function validateServiceSpecificRequirements($validator): void
    {
        $section = $this->route('section');
        $assessment = $section->assessment;
        $sectionType = $this->input('section_type', $section->section_type);

        switch ($assessment->service_type) {
            case 'waterproofing':
                $this->validateWaterproofingSection($validator, $sectionType);
                break;
            case 'painting':
                $this->validatePaintingSection($validator, $sectionType);
                break;
            case 'sports_court':
                $this->validateSportsCourtSection($validator, $sectionType);
                break;
            case 'industrial':
                $this->validateIndustrialSection($validator, $sectionType);
                break;
        }
    }

    /**
     * Validate waterproofing-specific section requirements.
     */
    protected function validateWaterproofingSection($validator, string $sectionType): void
    {
        // Safety sections are required for waterproofing
        if ($sectionType === 'safety') {
            if (!$this->filled('risk_level')) {
                $validator->errors()->add('risk_level', 'Risk level is required for safety sections in waterproofing assessments.');
            }
        }

        // Structural sections need quality ratings
        if ($sectionType === 'structural') {
            if (!$this->filled('quality_rating')) {
                $validator->errors()->add('quality_rating', 'Quality rating is required for structural sections in waterproofing assessments.');
            }
        }
    }

    /**
     * Validate painting-specific section requirements.
     */
    protected function validatePaintingSection($validator, string $sectionType): void
    {
        // Surface sections need quality ratings for painting
        if ($sectionType === 'surface') {
            if (!$this->filled('quality_rating')) {
                $validator->errors()->add('quality_rating', 'Quality rating is required for surface sections in painting assessments.');
            }
        }
    }

    /**
     * Validate sports court-specific section requirements.
     */
    protected function validateSportsCourtSection($validator, string $sectionType): void
    {
        // Measurements are critical for sports courts
        if ($sectionType === 'measurements') {
            if (!$this->filled('compliance_standards')) {
                $validator->errors()->add('compliance_standards', 'Compliance standards are required for measurement sections in sports court assessments.');
            }
        }
    }

    /**
     * Validate industrial-specific section requirements.
     */
    protected function validateIndustrialSection($validator, string $sectionType): void
    {
        // Safety sections are mandatory for industrial assessments
        if ($sectionType === 'safety') {
            if (!$this->filled('required_certifications')) {
                $validator->errors()->add('required_certifications', 'Required certifications must be specified for safety sections in industrial assessments.');
            }
            
            if (!$this->filled('compliance_standards')) {
                $validator->errors()->add('compliance_standards', 'Compliance standards are required for safety sections in industrial assessments.');
            }
        }
    }

    /**
     * Validate business logic constraints.
     */
    protected function validateBusinessLogic($validator): void
    {
        $section = $this->route('section');
        $assessment = $section->assessment;
        
        // Check if section has dependent sections that would create conflicts
        if ($this->filled('status')) {
            $newStatus = $this->input('status');
            
            // Cannot mark section as completed if it has incomplete required dependencies
            if ($newStatus === 'completed' && $this->filled('depends_on_sections')) {
                $dependencies = AssessmentSection::whereIn('id', $this->input('depends_on_sections'))
                    ->where('status', '!=', 'completed')
                    ->exists();
                    
                if ($dependencies) {
                    $validator->errors()->add('status', 'Cannot complete section while dependent sections are incomplete.');
                }
            }
        }

        // Validate that required sections cannot be skipped
        if ($this->input('status') === 'skipped' && $this->boolean('is_required', $section->is_required)) {
            $validator->errors()->add('status', 'Required sections cannot be skipped.');
        }

        // Validate sort order uniqueness within assessment
        if ($this->filled('sort_order')) {
            $duplicateOrder = AssessmentSection::where('assessment_id', $assessment->id)
                ->where('sort_order', $this->input('sort_order'))
                ->where('id', '!=', $section->id)
                ->exists();
                
            if ($duplicateOrder) {
                $validator->errors()->add('sort_order', 'Sort order must be unique within the assessment.');
            }
        }
    }

    /**
     * Validate scoring logic and consistency.
     */
    protected function validateScoringLogic($validator): void
    {
        // Ensure scoring is consistent with section completion
        $currentScore = $this->input('current_score');
        $maxScore = $this->input('max_score');
        $status = $this->input('status', $this->route('section')->status);

        if ($currentScore && $maxScore && $status === 'completed') {
            $scorePercentage = ($currentScore / $maxScore) * 100;
            $quality = $this->input('quality_rating');
            
            // Validate quality rating consistency with score
            if ($quality) {
                $expectedQuality = $this->getExpectedQualityFromScore($scorePercentage);
                if ($quality !== $expectedQuality) {
                    $validator->errors()->add('quality_rating', 
                        "Quality rating '{$quality}' is inconsistent with score percentage ({$scorePercentage}%). Expected: {$expectedQuality}."
                    );
                }
            }
        }
    }

    /**
     * Get expected quality rating based on score percentage.
     */
    protected function getExpectedQualityFromScore(float $scorePercentage): string
    {
        if ($scorePercentage >= 90) return 'excellent';
        if ($scorePercentage >= 75) return 'good';
        if ($scorePercentage >= 60) return 'fair';
        if ($scorePercentage >= 40) return 'poor';
        return 'critical';
    }
}