<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

function var_dump_ret($mixed = null) {
    ob_start();
    var_dump($mixed);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

class EditableLayerDefUpdateFormRequest extends FormRequest
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
            'title'  => 'required',
            'geom_type' => 'sometimes|nullable',
            'fields'  => 'required|json',
            'color' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'metadata' => 'sometimes|nullable',
            'isvisible' => 'sometimes|nullable',
            'download' => 'sometimes|nullable',
            'showTable' => 'sometimes|nullable',
            'showInSearch' => 'sometimes|nullable'
        ];
    }
}
