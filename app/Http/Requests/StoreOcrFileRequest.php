<?php

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOcrFileRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Selecione um arquivo para processar.',
            'file.file' => 'O envio precisa ser um arquivo válido.',
            'file.mimes' => 'Formatos permitidos: PDF, PNG, JPG, JPEG e WEBP.',
            'file.max' => 'O arquivo deve ter no máximo 10 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'arquivo',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->is('api/*')) {
            throw new HttpResponseException(
                ApiResponse::error(
                    message: 'Dados de validacao invalidos.',
                    statusCode: 422,
                    errors: $validator->errors()->toArray()
                )
            );
        }

        parent::failedValidation($validator);
    }
}
