<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = (int) config('cdn.max_image_size_kb', 10240);

        return [
            'file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,svg',
                "max:{$maxKb}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'No file was provided.',
            'file.mimes'    => 'Accepted formats: jpg, jpeg, png, gif, webp, svg.',
            'file.max'      => 'The image exceeds the maximum allowed size.',
        ];
    }
}
