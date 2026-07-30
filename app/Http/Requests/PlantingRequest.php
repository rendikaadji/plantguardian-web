<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlantingRequest extends FormRequest
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
            'garden_plot_id' => ['required_without:planting_id', 'nullable', 'exists:garden_plots,id'],
            'planting_id' => ['required_without:garden_plot_id', 'nullable', 'exists:plantings,id'],
            'seed_code' => ['required_with:garden_plot_id', 'nullable', 'string', 'max:50'],
            'plant_species_id' => ['nullable', 'exists:plant_species,id'],
        ];
    }

    /**
     * Custom Indonesian validation error messages.
     */
    public function messages(): array
    {
        return [
            'garden_plot_id.required_without' => 'ID lahan tanam atau ID penanaman wajib diisi.',
            'garden_plot_id.exists' => 'Lahan tanam yang dipilih tidak ditemukan.',
            'planting_id.required_without' => 'ID lahan tanam atau ID penanaman wajib diisi.',
            'planting_id.exists' => 'Data penanaman tidak ditemukan.',
            'seed_code.required_with' => 'Kode benih wajib diisi saat melakukan penanaman.',
            'seed_code.string' => 'Kode benih harus berupa format teks valid.',
            'plant_species_id.exists' => 'Spesies tanaman yang dipilih tidak ditemukan di katalog.',
        ];
    }
}
