<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OTRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'unique:orden_trabajo',
            'id_moldura' => 'required',
        ];
        [
            'id.required' => 'El campo orden de trabajo es obligatorio',
            'id.unique' => 'La orden de trabajo ya existe',
            'id_moldura.required' => 'El campo moldura es obligatorio',
        ];
    }
}
