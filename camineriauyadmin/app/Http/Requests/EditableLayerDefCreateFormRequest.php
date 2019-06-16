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

class EditableLayerDefCreateFormRequest extends FormRequest
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
        $validated = $this->all();
        $validated['name'] = 'cr_'.$validated['name'];
        return $validated;
    }
    
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->editablelayerdef) {
            $id = $this->editablelayerdef->id;
        }
        else {
            $id = null;
        }
        return [
            'name'    => 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/u|unique:editablelayerdefs,name,'.$id,
            'title'  => 'required',
            'geom_type' => 'required',
            'color' => 'required|regex:/^#[a-fA-F0-9]{6}$/',
            'fields'  => 'required|json',
            'metadata' => 'sometimes|nullable',
            'isvisible' => 'sometimes|nullable',
            'download' => 'sometimes|nullable',
            'showTable' => 'sometimes|nullable',
            'showInSearch' => 'sometimes|nullable'
        ];
    }
}
