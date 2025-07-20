<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgencyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id|unique:agencies,user_id,' . $this->agency,
            'company_name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,pending',
        ];
    }
} 