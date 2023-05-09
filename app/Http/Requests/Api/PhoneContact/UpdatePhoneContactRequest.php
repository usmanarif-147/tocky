<?php

namespace App\Http\Requests\Api\PhoneContact;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePhoneContactRequest extends FormRequest
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
            'contact_id' => ['required'],
            'first_name' => ['required', 'min:2', 'max:30'],
            'last_name'  => ['min:2', 'max:30'],
            'email'      => ['email', 'max:50'],
            'work_email' => ['email', 'max:50'],
            'company_name' => ['min:3', 'max:20'],
            'job_title'  => ['max:500'],
            'address'       => ['min:3', 'max:110'],
            'phone'      => ['required', 'min:9', 'max:20'],
            'work_phone' => ['min:11', 'max:15'],
            'photo' => ['nullable', 'mimes:jpeg,jpg,png', 'max:2000'],
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(response()->json([
    //         'errors' => $validator->errors()->all(),
    //     ], 422));
    // }
}
