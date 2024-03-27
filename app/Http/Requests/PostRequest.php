<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'class_id' => 'required_without:school_id|integer',
            'school_id' => 'required_without:class_id|integer',
            'text' => 'required|string',
            'type' => 'required|string|in:text,video,picture,poll,attachment',
            'video' => 'file|mimes:mp4|max:10240', // 10 MB
            'picture.*' => 'file|mimes:jpeg,jpg,png|max:10240', // 10 MB
            'attachment' => 'file|mimes:pdf,txt|max:10240', // 10 MB
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'A class id is required.',
            'text.required' => 'Text is required.',
            'type.required' => 'A type is required.',
            'type.in' => 'The type must be one of the following: text, video, picture, poll, attachment.',
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
