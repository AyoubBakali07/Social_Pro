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
            'agency_id' => 'required|exists:agencies,id',
            'client_id' => 'required|exists:clients,id',
            'content' => 'required|string',
            'media' => 'nullable|string',
            'scheduleDate' => 'required|date',
            'platform' => 'required|string',
            'postType' => 'required|string',
            'status' => 'required|in:draft,scheduled,published,approved,rejected',
            'feedback' => 'nullable|string',
        ];
    }
} 