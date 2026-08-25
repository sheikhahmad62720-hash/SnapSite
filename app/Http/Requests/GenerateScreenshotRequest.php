<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateScreenshotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'max:2048'],
            'width' => ['required', 'integer', 'min:320', 'max:3840'],
            'height' => ['required', 'integer', 'min:320', 'max:21600'],
            'screenshot_type' => ['required', 'string', 'in:viewport,full'],
            'format' => ['required', 'string', 'in:png,jpeg,webp'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'Please enter a website URL.',
            'url.max' => 'The URL is too long.',
            'width.min' => 'Width must be at least 320 pixels.',
            'width.max' => 'Width cannot exceed 3840 pixels.',
            'height.min' => 'Height must be at least 320 pixels.',
            'height.max' => 'Height cannot exceed 21600 pixels.',
            'screenshot_type.in' => 'Screenshot type must be viewport or full page.',
            'format.in' => 'Format must be PNG, JPEG, or WEBP.',
        ];
    }
}
