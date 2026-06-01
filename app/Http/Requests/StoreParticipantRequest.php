<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreParticipantRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $documentNumber = strtoupper((string) $this->input('document_number'));

        $this->merge([
            'document_number' => preg_replace('/[^A-Z0-9]/', '', $documentNumber),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'document_number' => ['required', 'string', 'min:5', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:80'],
            'institution_role' => ['nullable', 'string', 'max:255'],
            'consent_accepted' => ['accepted'],
        ];
    }
}
