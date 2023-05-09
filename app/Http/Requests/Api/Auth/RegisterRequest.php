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
            // 'username' => ['required', 'min:5', 'max:25', 'regex:/^[A-Za-z][A-Za-z0-9_.]{5,25}$/', 'unique:users'],
            'username' => ['required', 'min:5', 'max:25'],
            'email' => ['required', 'email', 'max:50', 'unique:users'],
            'phone' => ['sometimes', 'min:11', 'max:15', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(response()->json([
    //         'errors' => $validator->errors()->all(),
    //     ], 422));
    // }
}
