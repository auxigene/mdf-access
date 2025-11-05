<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExcelRequest extends FormRequest
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
            'data' => 'required|array|min:1',
            'data.*.colonne_excel' => 'required|string',
            'data.*.champ_kizeo' => 'required|string',
            'data.*.valeur' => 'nullable',
            'data.*.rang' => 'required|integer|min:1',
            'fichier_excel' => 'nullable|string', // Nom du fichier Excel à mettre à jour
        ];
    }
}
