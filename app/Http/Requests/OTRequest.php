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
        if($this->has('workOrderAdded')){
            return [
                'workOrderAdded' => 'required|unique:orden_trabajo,id',
                'moldingSelected' => 'required',
            ];
        }else{
            return [
                'workOrderSelected' => 'required',
            ];
        }
    }
    public function messages()
    {
        return [
            'workOrderAdded.required' => 'El campo orden de trabajo es obligatorio',
            'workOrderAdded.unique' => 'La orden de trabajo ingresada ya existe',
            'moldingSelected.required' => 'El campo moldura es obligatorio',
            'workOrderSelected.required' => 'Tienes que elegir una orden de trabajo',
        ];
        
    }
}