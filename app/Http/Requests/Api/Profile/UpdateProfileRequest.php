<?php

namespace App\Http\Requests\Api\Profile;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
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
            'username' => ['required', 'min:5', 'max:25', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['min:11', 'max:15', Rule::unique(User::class)->ignore($this->user()->id)],
            'gender' => ['in:1,2,3'],
            'dob' => ['nullable', 'date', 'before:today'],
            'private' => ['required'],
            'name' => ['nullable', 'string'],
            'cover_photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:4096'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:4096'],
            'job_title' => ['nullable', 'string'],
            'company' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'gender.in' => 'Please enter 1 for male, 2 for female, 3 for not-share'
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(response()->json([
    //         'errors' => $validator->errors()->all(),
    //     ], 422));
    // }
}
