<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreImportRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bank_account_id' => [
                'required',
                'integer',
                Rule::exists('bank_accounts', 'id')
                    ->where('user_id', $this->user()->id)
            ],
            'file' => [
                'required',
                File::types(['text/plain', 'text/csv'])
                    ->extensions(['csv'])
                    ->max('10mb')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'bank_account_id.required' => 'Wybierz konto bankowe.',
            'bank_account_id.integer' => 'Wybrane konto ma nieprawidłowy identyfikator.',
            'bank_account_id.exists' => 'Wybrane konto nie istnieje lub nie masz do niego dostępu.',
            'file.required' => 'Wybierz plik CSV do importu.',
            'file.file' => 'Przesłana wartość musi być plikiem.',
            'file.mimetypes' => 'Wybierz plik w formacie CSV.',
            'file.extensions' => 'Plik musi mieć rozszerzenie .csv.',
            'file.max' => 'Plik CSV może mieć maksymalnie 10 MB.',
        ];
    }
}
