<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id|unique:clients,user_id,' . $this->client,
            'agency_id' => 'required|exists:agencies,id',
            'company_name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive,Pending',
        ];
    }
} 