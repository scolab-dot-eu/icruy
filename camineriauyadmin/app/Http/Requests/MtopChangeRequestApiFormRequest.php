<?php

namespace App\Http\Requests;

use App\ChangeRequest;
use Illuminate\Support\Facades\Log;

class MtopChangeRequestApiFormRequest extends JsonRequest
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
        Log::debug('validationData cmi 00');
        Log::debug(json_encode($this->all()));
        Log::debug(json_encode($this));
        $validated = parent::validationData();
        Log::debug(json_encode($validated));
        //Log::debug($validated['layer']);
        $validated['layer'] = ChangeRequest::getTableName($validated['layer']);
        //Log::debug($validated['layer']);
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
            //'layer'             => 'required|exists:editablelayerdefs,name',
            'layer'             => 'required',
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
