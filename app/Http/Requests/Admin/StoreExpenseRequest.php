<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'descricao'  => 'required|string|max:200',
            'valor'      => 'required|numeric|min:0',
            'categoria'  => 'required|in:manutencao,documentacao,limpeza,combustivel,comissao,outros',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'data'       => 'required|date',
        ];
    }
}
