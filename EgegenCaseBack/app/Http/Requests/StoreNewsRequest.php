<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:230', 'min:3'],
            'content' => ['required', 'string', 'min:10'],
            'image' => ['nullable', 'mimes:webp', 'max:2048'], // Maksimum 2MB
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Başlık alanı zorunludur.',
            'title.string' => 'Başlık metin olmalıdır.',
            'title.max' => 'Başlık en fazla 230 karakter olmalıdır.',
            'title.min' => 'Başlık en az 3 karakter olmalıdır.',
            'content.required' => 'İçerik alanı zorunludur.',
            'content.string' => 'İçerik metin olmalıdır.',
            'content.min' => 'İçerik en az 10 karakter olmalıdır.',
            'image.mimes' => 'Resim formatı sadece webp olmalıdır.',
            'image.max' => 'Resim boyutu en fazla 2MB olmalıdır.',
        ];
    }
}
