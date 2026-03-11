<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nome'        => 'required|string|max:100',
            'telefone'    => 'required|string|max:20',
            'email'       => 'nullable|email|max:150',
            'vehicle_id'  => 'nullable|exists:vehicles,id',
            'mensagem'    => 'nullable|string|max:500',
        ];
    }
}
