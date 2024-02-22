<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SchoolClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->role === 'teacher';

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        switch ($this->route()->getName()) {
            case 'schoolclass.store':
                return [
                    'name' => 'required|string',
                    'grade_level' => 'required|integer',
                    'subject' => 'required|string',
                ];
            case 'schoolclass.update':
                return [
                    'name' => 'sometimes|required|string',
                    'grade_level' => 'sometimes|required|integer',
                    'subject' => 'sometimes|required|string',
                ];
            default:
                return [];
        }

    }

    public function messages(): array
    {
        return [
            'name.required' => 'A name is required.',
            'grade_level.required' => 'A grade level is required.',
            'subject.required' => 'A subject is required.',
        ];
    }

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
