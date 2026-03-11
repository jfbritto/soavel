<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'vehicle_id'     => 'required|exists:vehicles,id',
            'customer_id'    => 'required|exists:customers,id',
            'preco_venda'    => 'required|numeric|min:0',
            'tipo_pagamento' => 'required|in:a_vista,financiado,consorcio,permuta,misto',
            'financiadora'   => 'nullable|string|max:80',
            'parcelas'       => 'nullable|integer|min:1|max:120',
            'entrada'        => 'nullable|numeric|min:0',
            'data_venda'     => 'required|date',
            'status'         => 'required|in:pendente,concluida,cancelada',
            'observacoes'    => 'nullable|string',
            'valor_troca'    => 'nullable|numeric|min:0',
        ];
    }
}
