<?php

namespace App\Http\Requests\Api\Profile;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ViewProfileRequest extends FormRequest
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
            'username' => 'required_without_all:card_uuid,connect_id',
            'card_uuid' => 'required_without_all:username,connect_id',
            'connect_id' => 'required_without_all:username,card_uuid'
        ];
    }

    public function messages()
    {
        return [
            'username.required_without_all' => 'Please enter username, card uuid or connect_id',
            'card_uuid.required_without_all' => 'Please enter username, card uuid or connect_id',
            'connect_id.required_without_all' => 'Please enter username, card uuid or connect_id',
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(response()->json([
    //         'errors' => $validator->errors()->all(),
    //     ], 422));
    // }
}
