<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use App\ChangeRequest;
use Illuminate\Support\Facades\Log;

class SearchApiFormRequest extends FormRequest
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
        $validated = parent::validationData();
        //$validated['layer'] = ChangeRequest::getTableName($validated['layer']);
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
            'q'             => 'required',
            //'layer'             => 'required|exists:editablelayerdefs,name',
            //'layer'             => 'required|exists:editablelayerdefs,name',
            //'departamento'    => 'required',
            //'bbox'??,
            ];
    }
}
