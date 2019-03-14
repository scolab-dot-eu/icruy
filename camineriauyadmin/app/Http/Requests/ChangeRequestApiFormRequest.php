<?php

namespace App\Http\Requests;

use App\ChangeRequest;
use Illuminate\Support\Facades\Log;

class ChangeRequestApiFormRequest extends JsonRequest
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
    
    protected function validationData()
    {
        $validated = parent::validationData();;
        $validated['layer'] = ChangeRequest::getTableName($validated['layer']);
        return $validated;
    }
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'operation'             => 'required|in:create,update,delete',
            'layer'             => 'required|exists:editablelayerdefs,name',
            //'departamento'    => 'required',
            'feature'    => 'required',
            'feature.properties'    => 'required',
            'feature.properties.departamento'    => ['required', function ($attribute, $value, $fail)
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
            ]
            ];
    }
}
