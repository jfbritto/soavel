<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Sem tipo informado o cliente é pessoa física (compatível com o formulário
     * antigo). O documento que não pertence ao tipo é descartado: PJ guarda só
     * CNPJ, PF guarda só CPF, inclusive ao trocar o tipo na edição.
     */
    protected function prepareForValidation()
    {
        $tipo = $this->input('tipo_pessoa') ?: 'pf';

        $this->merge([
            'tipo_pessoa' => $tipo,
            'cpf'         => $tipo === 'pj' ? null : $this->input('cpf'),
            'cnpj'        => $tipo === 'pj' ? $this->input('cnpj') : null,
        ]);
    }

    public function rules()
    {
        $customerId = $this->route('customer')?->id;

        return [
            'nome'        => 'required|string|max:100',
            'tipo_pessoa' => 'required|in:pf,pj',
            'cpf'         => "nullable|string|max:14|unique:customers,cpf,{$customerId}",
            'cnpj'        => "nullable|string|max:18|unique:customers,cnpj,{$customerId}",
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
