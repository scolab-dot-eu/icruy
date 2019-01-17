<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InterventionFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'departamento'             => 'required',
            'anyo_interv'             => 'required|integer',
            'codigo_camino'             => 'required',
            'tipo_elem'             => 'required|exists:editablelayerdefs,name',
            'id_elem'               => 'nullable|numeric',
            'codigo_camino'             => 'required',
            'longitud'             => 'nullable|between:0,9.99',
            'forma_ejecucion'             => 'nullable',
            'tarea'             => 'nullable',
            'financiacion'             => 'nullable'
            ];
    }
}
