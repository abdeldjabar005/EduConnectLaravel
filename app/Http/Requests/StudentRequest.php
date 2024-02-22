<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        switch ($this->route()->getName()) {
            case 'student.store':
                return [
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                    'grade_level' => 'required|integer',
                ];
            case 'student.update':
                return [
                    'first_name' => 'sometimes|required|string',
                    'last_name' => 'sometimes|required|string',
                    'grade_level' => 'sometimes|required|integer',
                ];
            case 'student.addStudentToClass':
                return [
                    'class_id' => 'required|exists:classes,id',
                ];
            default:
                return [];
        }
    }

    public function messages()
    {
        return [
            'first_name.required' => 'A first name is required.',
            'last_name.required' => 'A last name is required.',
            'grade_level.required' => 'A grade level is required.',
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
