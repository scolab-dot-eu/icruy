<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateFormRequest extends FormRequest
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
            'name'    => 'required|unique:departments',
            'email'    => 'required|email',
            'password'    => ['nullable', 'min:8', function ($attribute, $value, $fail)
            {
                if (!User::checkPasswordClasses($value)) {
                    $fail('La contraseña debe contener al menos 3 características: minúsculas, mayúsculas, números, caracteres especiales o extendidos');
                }
            }
            ],
            'password_confirm'    => 'nullable|same:password',
            'phone'    => 'nullable',
            
        ];
    }
}
