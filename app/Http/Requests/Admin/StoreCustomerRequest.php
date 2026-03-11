<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $customerId = $this->route('customer')?->id;

        return [
            'nome'        => 'required|string|max:100',
            'cpf'         => "nullable|string|max:14|unique:customers,cpf,{$customerId}",
            'telefone'    => 'required|string|max:20',
            'email'       => 'nullable|email|max:150',
            'cep'         => 'nullable|string|max:9',
            'endereco'    => 'nullable|string|max:150',
            'numero'      => 'nullable|string|max:10',
            'bairro'      => 'nullable|string|max:80',
            'cidade'      => 'nullable|string|max:80',
            'estado'      => 'nullable|string|size:2',
            'observacoes' => 'nullable|string',
        ];
    }
}
