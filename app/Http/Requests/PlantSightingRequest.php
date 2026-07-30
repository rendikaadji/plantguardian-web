<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlantSightingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'saved_to_gallery' => ['sometimes', 'boolean'],
            'plant_species_id' => ['nullable', 'exists:plant_species,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'photo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }

    /**
     * Custom Indonesian validation error messages.
     */
    public function messages(): array
    {
        return [
            'saved_to_gallery.boolean' => 'Status simpan ke galeri harus bernilai true atau false.',
            'plant_species_id.exists' => 'Spesies tumbuhan yang dipilih tidak ditemukan di katalog.',
            'latitude.numeric' => 'Koordinat latitude harus berupa angka.',
            'latitude.between' => 'Koordinat latitude harus di antara -90 dan 90.',
            'longitude.numeric' => 'Koordinat longitude harus berupa angka.',
            'longitude.between' => 'Koordinat longitude harus di antara -180 dan 180.',
            'photo.file' => 'File foto yang diunggah tidak valid.',
            'photo.image' => 'File yang diunggah harus berupa gambar.',
            'photo.mimes' => 'Format foto harus berupa jpeg, png, jpg, atau webp.',
            'photo.max' => 'Ukuran foto maksimal adalah 10 MB.',
        ];
    }
}
