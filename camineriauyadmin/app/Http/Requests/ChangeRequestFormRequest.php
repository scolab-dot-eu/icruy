<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeRequestFormRequest extends FormRequest
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
            'operation'             => 'required|in:create,update,delete',
            'layer'             => 'required|exists:editablelayerdefs,name',
            'status'             => 'required',
            //'feature' => 'sometimes'
            //'departamento'    => 'required',
            ];
    }
}
