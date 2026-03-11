<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
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
            'interesse'   => 'nullable|string|max:200',
            'origem'      => 'required|in:site,whatsapp,presencial,indicacao,instagram,outro',
            'status'      => 'required|in:novo,em_contato,convertido,perdido',
            'observacoes' => 'nullable|string',
            'user_id'     => 'nullable|exists:users,id',
        ];
    }
}
