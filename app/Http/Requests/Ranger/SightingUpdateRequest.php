<?php

namespace App\Http\Requests\Ranger;

use Illuminate\Foundation\Http\FormRequest;

class SightingUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['ranger', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plant_species_id' => 'nullable|exists:plant_species,id',
            'common_name' => 'nullable|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'conservation_status' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'care_instructions' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'plant_species_id.required' => 'Spesies tumbuhan wajib dipilih.',
            'plant_species_id.exists' => 'Spesies tumbuhan yang dipilih tidak valid.',
            'latitude.between' => 'Koordinat latitude tidak valid.',
            'longitude.between' => 'Koordinat longitude tidak valid.',
        ];
    }
}
