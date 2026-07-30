<?php

namespace App\Http\Requests\Ranger;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpeciesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'ranger';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $speciesId = $this->route('species') ? (is_object($this->route('species')) ? $this->route('species')->id : $this->route('species')) : null;

        return [
            'species_code' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:100',
                Rule::unique('plant_species', 'species_code')->ignore($speciesId),
            ],
            'common_name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'scientific_name' => ['nullable', 'string', 'max:255'],
            'description' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string'],
            'care_instructions' => ['nullable', 'string'],
            'conservation_status' => ['nullable', 'string', 'max:100'],
            'reference_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'reference_image_path' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom Indonesian validation error messages.
     */
    public function messages(): array
    {
        return [
            'species_code.required' => 'Kode spesies wajib diisi.',
            'species_code.unique' => 'Kode spesies sudah terdaftar di sistem.',
            'common_name.required' => 'Nama umum tumbuhan wajib diisi.',
            'description.required' => 'Deskripsi tumbuhan wajib diisi.',
            'reference_image.image' => 'File referensi harus berupa gambar.',
            'reference_image.mimes' => 'Format gambar referensi harus jpeg, png, jpg, atau webp.',
            'reference_image.max' => 'Ukuran gambar referensi maksimal 10 MB.',
        ];
    }
}
