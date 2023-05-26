<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'username' => ['required', 'min:5', 'max:25', 'regex:/^[A-Za-z][A-Za-z0-9_.]{5,25}$/', 'unique:users'],
            'email' => ['required', 'email', 'max:50', 'unique:users'],
            'phone' => ['sometimes', 'min:11', 'max:15', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
        ];
    }

    public function messages()
    {
        return [
            'username.regex' => 'The username must start with a letter and can only contain letters (uppercase or lowercase), numbers, underscores, or periods. It should be between 5 and 25 characters long.'
        ];
    }
    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(response()->json([
    //         'errors' => $validator->errors()->all(),
    //     ], 422));
    // }
}
