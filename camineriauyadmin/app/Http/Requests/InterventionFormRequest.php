<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use App\ChangeRequest;

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
            'departamento'    => ['required', function ($attribute, $value, $fail)
                {
                    if (!$this->user()->isAdmin()) {
                        if (!$this->user()->isManager()) {
                            $fail('El usuario no tiene permisos para editar el departamento: '.$value);
                        }
                        if ($this->user()->departments()->where('code', $value)->count()==0) {
                            $fail('El usuario no tiene permisos para editar el departamento: '.$value);
                        }
                    }
                }
             ],
            'nombre' => 'sometimes|nullable',
            'fecha_interv'             => 'required|date',
            'codigo_camino'             => 'required',
            'tipo_elem'             => 'required|exists:editablelayerdefs,name',
            'id_elem'               => 'nullable|numeric',
            'codigo_camino'    => ['required', function ($attribute, $value, $fail)
            {
                $codigo_dep = $this->input('departamento');
                if (!ChangeRequest::comprobarEstructuraCodigoCamino($value, $codigo_dep)) {
                    $fail('El código de camino no es válido: '.$value);
                }
            }],
            'longitud'             => 'nullable|between:0,9.99',
            'monto'             => 'required|between:0,9999999999.99',
            'forma_ejecucion'             => 'nullable',
            'tarea'             => 'nullable',
            'financiacion'             => 'nullable'
            ];
    }
}
