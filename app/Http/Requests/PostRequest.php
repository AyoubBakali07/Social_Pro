<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'media' => 'nullable|array',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov|max:20480', // 20MB
            'scheduleDate' => 'required|date|after:now',
            'platform' => 'required|string',
            'postType' => 'required|string',
            'status' => 'required|in:draft,scheduled,published,approved,rejected',
            'feedback' => 'nullable|string',
        ];
    }
} 