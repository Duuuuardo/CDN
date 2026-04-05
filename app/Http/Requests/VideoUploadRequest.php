<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKb = (int) config('cdn.max_video_size_kb', 512000);

        return [
            'file' => [
                'required',
                'file',
                'mimes:mp4,webm,ogg,mov,avi,mkv',
                "max:{$maxKb}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'No file was provided.',
            'file.mimes'    => 'Accepted formats: mp4, webm, ogg, mov, avi, mkv.',
            'file.max'      => 'The video exceeds the maximum allowed size.',
        ];
    }
}
