<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user,
            'role' => 'required|in:admin,agency,client',
            'password' => $this->isMethod('post') ? 'required|string|min:8' : 'sometimes|string|min:8',
        ];
    }
} 