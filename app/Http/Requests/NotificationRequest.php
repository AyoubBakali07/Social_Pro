<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,error',
            'is_read' => 'boolean',
        ];
    }
} 