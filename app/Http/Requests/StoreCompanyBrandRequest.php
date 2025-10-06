<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyBrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('manage settings');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'phone' => ['required', 'string', 'max:50', 'regex:/^(\+?6?0)[0-9]{9,10}$/'],
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'tagline' => 'nullable|string|max:500',
            'color_primary' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'color_secondary' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'brand name',
            'legal_name' => 'legal name',
            'registration_number' => 'registration number',
            'logo' => 'logo',
            'postal_code' => 'postal code',
            'color_primary' => 'primary color',
            'color_secondary' => 'secondary color',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must be a valid Malaysian phone number.',
            'color_primary.regex' => 'The primary color must be a valid hex color code (e.g., #FF5733).',
            'color_secondary.regex' => 'The secondary color must be a valid hex color code (e.g., #FF5733).',
        ];
    }
}
