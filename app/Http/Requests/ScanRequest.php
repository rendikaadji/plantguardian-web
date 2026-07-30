<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScanRequest extends FormRequest
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
            'image' => ['required_without:image_base64', 'nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'image_base64' => ['required_without:image', 'nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'common_name' => ['nullable', 'string', 'max:255'],
            'scientific_name' => ['nullable', 'string', 'max:255'],
            'conservation_status' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'care_instructions' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom Indonesian validation error messages.
     */
    public function messages(): array
    {
        return [
            'image.required_without' => 'File gambar atau data gambar base64 wajib diunggah untuk pemindaian.',
            'image.file' => 'File yang diunggah tidak valid.',
            'image.image' => 'File yang diunggah harus berupa gambar.',
            'image.mimes' => 'Format gambar harus berupa jpeg, png, jpg, atau webp.',
            'image.max' => 'Ukuran gambar maksimal adalah 10 MB.',
            'image_base64.required_without' => 'File gambar atau data gambar base64 wajib diunggah untuk pemindaian.',
            'latitude.numeric' => 'Koordinat latitude harus berupa angka.',
            'latitude.between' => 'Koordinat latitude harus di antara -90 dan 90.',
            'longitude.numeric' => 'Koordinat longitude harus berupa angka.',
            'longitude.between' => 'Koordinat longitude harus di antara -180 dan 180.',
        ];
    }
}
