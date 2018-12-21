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
        if ($this->editablelayerdef) {
            $id = $this->editablelayerdef->id;
        }
        else {
            $id = null;
        }
        return [
            'name'    => 'regex:/^[a-zA-Z_]+$/u|unique:editablelayerdefs,name,'.$id,
            'title'  => 'required',
            'url'  => 'required',
            'fields'  => 'required',
            'geom_type' => 'sometimes|nullable',
            'protocol' => 'required',
            'style' => 'sometimes|nullable',
            /*'style' => ['json', function ($attribute, $value, $fail) {
                $jsonobj = json_decode($value);
                if (!isset($jsonobj->fields)) {
                    $fail('El atributo '.$attribute.' es inválido. La definición de campos es incorrecta');
                }
            }],*/
            'geom_style' => 'sometimes|nullable',
            'metadata' => 'sometimes|nullable',
            'conf'    => 'sometimes|nullable|json'
        ];
    }
}
