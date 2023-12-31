<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
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
            'email'       => 'required|email',
            'password'    => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required'       => 'O campo email é obrigátorio',
            'email.email'          => 'O campo deve ter um formato de email válido',
            'password.required'    => 'O campo password é obrigátorio',
        ];
    }
}
