<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupportLayerDefFormRequest extends FormRequest
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
        if ($this->supportlayerdef) {
            $id = $this->supportlayerdef->id;
        }
        else {
            $id = null;
        }
        return [
            'name'    => 'required|unique:supportlayerdefs,name,'.$id,
            'title'    => 'required|unique:supportlayerdefs,title,'.$id,
            'protocol' => 'required',
            'url' => 'required_if:protocol,wms,wfs,tilelayer',
            'api_key' => 'nullable',
            'isbaselayer'    => 'sometimes|accepted',
            'isvisible'    => 'sometimes|accepted',
            'layergroup'    => 'nullable', # fixme
            'conf'    => ['nullable', 'json', function ($attribute, $value, $fail) {
            }]
        ];
    }
}
