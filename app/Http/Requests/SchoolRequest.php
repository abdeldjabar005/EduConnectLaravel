<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SchoolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
//        $user = $this->user();
//
//        return $user && $user->role === 'admin';
        return true;
    }
    public function rules()
    {
        switch ($this->route()->getName()) {
            case 'school.store':
                return [
                    'name' => 'required|string',
                    'address' => 'required|string',
                    'image' => 'nullable|file|image',
                ];
            case 'school.update':
                return [
                    'name' => 'sometimes|required|string',
                    'address' => 'sometimes|required|string',
                    'image' => 'sometimes|nullable|file|image',
                ];
            case 'school.join':
                return [
                    'school_id' => 'required|exists:schools,id',
                ];
            default:
                return [];
        }
    }
    public function messages()
    {
        return [
            'name.required' => 'A name is required.',
            'address.required' => 'An address is required.',
            'image.file' => 'The image must be a file.',
            'image.image' => 'The image must be an image.',];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
