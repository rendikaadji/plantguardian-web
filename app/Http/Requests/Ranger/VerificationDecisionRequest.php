<?php

namespace App\Http\Requests\Ranger;

use Illuminate\Foundation\Http\FormRequest;

class VerificationDecisionRequest extends FormRequest
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
        return [
            'status' => ['required_without:decision', 'nullable', 'string', 'in:verified,rejected'],
            'decision' => ['required_without:status', 'nullable', 'string', 'in:verified,rejected'],
        ];
    }

    /**
     * Custom Indonesian validation error messages.
     */
    public function messages(): array
    {
        return [
            'status.required_without' => 'Keputusan verifikasi (verified/rejected) wajib dipilih.',
            'status.in' => 'Keputusan verifikasi harus berupa verified atau rejected.',
            'decision.required_without' => 'Keputusan verifikasi (verified/rejected) wajib dipilih.',
            'decision.in' => 'Keputusan verifikasi harus berupa verified atau rejected.',
        ];
    }
}
