<?php

namespace App\Http\Requests\Ranger;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompostMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'ranger';
    }

    public function rules(): array
    {
        $materialId = $this->route('compost_material') ? (is_object($this->route('compost_material')) ? $this->route('compost_material')->id : $this->route('compost_material')) : null;

        return [
            'material_code' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'string',
                'max:100',
                Rule::unique('compost_materials', 'material_code')->ignore($materialId),
            ],
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'description' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string'],
            'instructions' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'material_code.required' => 'Kode bahan kompos wajib diisi.',
            'material_code.unique' => 'Kode bahan kompos sudah terdaftar.',
            'name.required' => 'Nama bahan kompos wajib diisi.',
            'description.required' => 'Deskripsi bahan kompos wajib diisi.',
            'instructions.required' => 'Panduan instruksi pembuatan wajib diisi.',
        ];
    }
}
