<?php

namespace App\Http\Requests;

use App\Models\Assessment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssessmentPhotoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $assessment = $this->route('assessment');
        
        // Check if user can upload photos to this assessment
        if (!auth()->user()->can('update', $assessment)) {
            return false;
        }

        // Prevent photo uploads to cancelled assessments
        if ($assessment->status === Assessment::STATUS_CANCELLED) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Photo files
            'photos' => [
                'required',
                'array',
                'min:1',
                'max:20', // Maximum 20 photos per upload
            ],
            'photos.*' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,heic,webp',
                'max:10240', // 10MB max per photo
                'dimensions:min_width=200,min_height=200,max_width=8000,max_height=8000',
            ],

            // Photo metadata
            'section_id' => [
                'nullable',
                'exists:assessment_sections,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $assessment = $this->route('assessment');
                        $section = \App\Models\AssessmentSection::find($value);
                        if (!$section || $section->assessment_id !== $assessment->id) {
                            $fail('The selected section does not belong to this assessment.');
                        }
                    }
                },
            ],
            'item_id' => [
                'nullable',
                'exists:assessment_items,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $assessment = $this->route('assessment');
                        $item = \App\Models\AssessmentItem::find($value);
                        if (!$item || $item->assessmentSection->assessment_id !== $assessment->id) {
                            $fail('The selected item does not belong to this assessment.');
                        }
                    }
                },
            ],

            // Photo types and descriptions
            'photo_types' => [
                'nullable',
                'array',
                'size:' . (is_array($this->photos) ? count($this->photos) : 0),
            ],
            'photo_types.*' => [
                'nullable',
                'string',
                Rule::in([
                    'overview',
                    'detail',
                    'problem',
                    'solution',
                    'before',
                    'after',
                    'measurement',
                    'safety',
                    'access',
                    'equipment',
                    'material',
                    'documentation',
                    'compliance',
                    'other',
                ]),
            ],
            'descriptions' => [
                'nullable',
                'array',
                'size:' . (is_array($this->photos) ? count($this->photos) : 0),
            ],
            'descriptions.*' => [
                'nullable',
                'string',
                'max:500',
            ],

            // Geo-location and context
            'capture_location' => [
                'nullable',
                'boolean',
            ],
            'location_descriptions' => [
                'nullable',
                'array',
                'size:' . (is_array($this->photos) ? count($this->photos) : 0),
            ],
            'location_descriptions.*' => [
                'nullable',
                'string',
                'max:200',
            ],

            // Quality and processing options
            'auto_enhance' => [
                'nullable',
                'boolean',
            ],
            'watermark' => [
                'nullable',
                'boolean',
            ],
            'compress_images' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'photos.required' => 'At least one photo must be uploaded.',
            'photos.max' => 'You can upload a maximum of 20 photos at once.',
            'photos.*.required' => 'Each photo file is required.',
            'photos.*.image' => 'All files must be valid images.',
            'photos.*.mimes' => 'Photos must be in JPEG, JPG, PNG, HEIC, or WebP format.',
            'photos.*.max' => 'Each photo must be smaller than 10MB.',
            'photos.*.dimensions' => 'Photos must be at least 200x200 pixels and no larger than 8000x8000 pixels.',
            'photo_types.size' => 'Photo types array must match the number of uploaded photos.',
            'descriptions.size' => 'Descriptions array must match the number of uploaded photos.',
            'location_descriptions.size' => 'Location descriptions array must match the number of uploaded photos.',
            'descriptions.*.max' => 'Each photo description cannot exceed 500 characters.',
            'location_descriptions.*.max' => 'Each location description cannot exceed 200 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'photos' => 'photos',
            'photos.*' => 'photo',
            'section_id' => 'assessment section',
            'item_id' => 'assessment item',
            'photo_types' => 'photo types',
            'photo_types.*' => 'photo type',
            'descriptions' => 'descriptions',
            'descriptions.*' => 'description',
            'location_descriptions' => 'location descriptions',
            'location_descriptions.*' => 'location description',
            'capture_location' => 'capture location',
            'auto_enhance' => 'auto enhance',
            'watermark' => 'watermark',
            'compress_images' => 'compress images',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values for processing options
        $this->merge([
            'auto_enhance' => $this->boolean('auto_enhance', true),
            'watermark' => $this->boolean('watermark', true),
            'compress_images' => $this->boolean('compress_images', true),
            'capture_location' => $this->boolean('capture_location', true),
        ]);

        // Set default photo types if not provided
        if (!$this->filled('photo_types') && $this->filled('photos')) {
            $photoCount = is_array($this->photos) ? count($this->photos) : 0;
            $this->merge([
                'photo_types' => array_fill(0, $photoCount, 'overview'),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate assessment photo limits
            $this->validatePhotoLimits($validator);
            
            // Validate service-specific photo requirements
            $this->validateServiceSpecificRequirements($validator);
            
            // Validate file integrity
            $this->validateFileIntegrity($validator);
        });
    }

    /**
     * Validate photo limits for the assessment.
     */
    protected function validatePhotoLimits($validator): void
    {
        $assessment = $this->route('assessment');
        $currentPhotoCount = $assessment->photos()->count();
        $newPhotoCount = is_array($this->photos) ? count($this->photos) : 0;
        $totalPhotos = $currentPhotoCount + $newPhotoCount;

        // Maximum photos per assessment
        $maxPhotos = 100;
        if ($totalPhotos > $maxPhotos) {
            $validator->errors()->add('photos', "This assessment can have a maximum of {$maxPhotos} photos. Currently has {$currentPhotoCount} photos.");
        }

        // Check storage quota (example: 500MB per assessment)
        $currentStorageSize = $assessment->photos()->sum('file_size') / 1024 / 1024; // Convert to MB
        $maxStorageMB = 500;
        
        if ($currentStorageSize > $maxStorageMB * 0.9) { // Warn at 90% capacity
            $validator->errors()->add('photos', "Assessment photo storage is nearly full ({$currentStorageSize}MB of {$maxStorageMB}MB used).");
        }
    }

    /**
     * Validate service-specific photo requirements.
     */
    protected function validateServiceSpecificRequirements($validator): void
    {
        $assessment = $this->route('assessment');
        $serviceType = $assessment->service_type;
        $photoTypes = $this->input('photo_types', []);

        switch ($serviceType) {
            case 'waterproofing':
                $this->validateWaterproofingPhotos($validator, $photoTypes);
                break;
            case 'painting':
                $this->validatePaintingPhotos($validator, $photoTypes);
                break;
            case 'sports_court':
                $this->validateSportsCourtPhotos($validator, $photoTypes);
                break;
            case 'industrial':
                $this->validateIndustrialPhotos($validator, $photoTypes);
                break;
        }
    }

    /**
     * Validate waterproofing-specific photo requirements.
     */
    protected function validateWaterproofingPhotos($validator, array $photoTypes): void
    {
        $assessment = $this->route('assessment');
        
        // For high-risk waterproofing, require safety photos
        if ($assessment->overall_risk_score >= 7) {
            if (!in_array('safety', $photoTypes) && !$assessment->photos()->where('photo_type', 'safety')->exists()) {
                $validator->errors()->add('photo_types', 'High-risk waterproofing assessments require at least one safety photo.');
            }
        }

        // Require problem photos for emergency assessments
        if ($assessment->urgency_level === 'emergency') {
            if (!in_array('problem', $photoTypes) && !$assessment->photos()->where('photo_type', 'problem')->exists()) {
                $validator->errors()->add('photo_types', 'Emergency waterproofing assessments require problem documentation photos.');
            }
        }
    }

    /**
     * Validate painting-specific photo requirements.
     */
    protected function validatePaintingPhotos($validator, array $photoTypes): void
    {
        // Large painting projects should have overview photos
        $assessment = $this->route('assessment');
        if ($assessment->total_area > 1000) {
            if (!in_array('overview', $photoTypes) && !$assessment->photos()->where('photo_type', 'overview')->exists()) {
                $validator->errors()->add('photo_types', 'Large painting projects require overview photos for proper documentation.');
            }
        }
    }

    /**
     * Validate sports court-specific photo requirements.
     */
    protected function validateSportsCourtPhotos($validator, array $photoTypes): void
    {
        // Sports courts should have measurement photos for accurate quotations
        $assessment = $this->route('assessment');
        $existingMeasurementPhotos = $assessment->photos()->where('photo_type', 'measurement')->exists();
        
        if (!in_array('measurement', $photoTypes) && !$existingMeasurementPhotos) {
            $validator->errors()->add('photo_types', 'Sports court assessments should include measurement photos for accurate quotations.');
        }
    }

    /**
     * Validate industrial-specific photo requirements.
     */
    protected function validateIndustrialPhotos($validator, array $photoTypes): void
    {
        $assessment = $this->route('assessment');
        
        // Industrial assessments must have safety documentation
        if (!in_array('safety', $photoTypes) && !$assessment->photos()->where('photo_type', 'safety')->exists()) {
            $validator->errors()->add('photo_types', 'Industrial assessments require safety documentation photos.');
        }

        // High-risk industrial assessments need compliance photos
        if ($assessment->overall_risk_score >= 7) {
            if (!in_array('compliance', $photoTypes) && !$assessment->photos()->where('photo_type', 'compliance')->exists()) {
                $validator->errors()->add('photo_types', 'High-risk industrial assessments require compliance documentation photos.');
            }
        }
    }

    /**
     * Validate file integrity and security.
     */
    protected function validateFileIntegrity($validator): void
    {
        if (!$this->hasFile('photos')) {
            return;
        }

        $photos = $this->file('photos');
        foreach ($photos as $index => $photo) {
            // Check if file is actually an image
            $imageInfo = @getimagesize($photo->getPathname());
            if ($imageInfo === false) {
                $validator->errors()->add("photos.{$index}", 'File is not a valid image.');
                continue;
            }

            // Check for suspicious file content
            $handle = fopen($photo->getPathname(), 'rb');
            $header = fread($handle, 1024);
            fclose($handle);

            // Check for script content in image files (basic security check)
            if (preg_match('/<\?php|<script|javascript:/i', $header)) {
                $validator->errors()->add("photos.{$index}", 'File contains suspicious content and cannot be uploaded.');
                continue;
            }

            // Validate file extension matches MIME type
            $mimeType = $photo->getMimeType();
            $extension = strtolower($photo->getClientOriginalExtension());
            
            $validMimeExtensions = [
                'image/jpeg' => ['jpg', 'jpeg'],
                'image/png' => ['png'],
                'image/heic' => ['heic'],
                'image/webp' => ['webp'],
            ];

            if (isset($validMimeExtensions[$mimeType]) && !in_array($extension, $validMimeExtensions[$mimeType])) {
                $validator->errors()->add("photos.{$index}", 'File extension does not match the image type.');
            }
        }
    }
}