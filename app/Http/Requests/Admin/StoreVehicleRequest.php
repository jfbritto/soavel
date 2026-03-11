<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'marca'          => 'required|string|max:60',
            'modelo'         => 'required|string|max:80',
            'versao'         => 'nullable|string|max:100',
            'ano_fabricacao' => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'ano_modelo'     => 'required|integer|min:1990|max:' . (date('Y') + 1),
            'km'             => 'required|integer|min:0',
            'preco'          => 'required|numeric|min:0',
            'preco_compra'   => 'nullable|numeric|min:0',
            'cor'            => 'required|string|max:40',
            'combustivel'    => 'required|in:gasolina,etanol,flex,diesel,gnv,hibrido,eletrico',
            'transmissao'    => 'required|in:manual,automatico,automatizado,cvt',
            'portas'         => 'required|integer|in:0,2,4',
            'motorizacao'    => 'nullable|string|max:20',
            'categoria'      => 'required|in:hatch,sedan,suv,pickup,van,esportivo,outro',
            'status'         => 'required|in:disponivel,reservado,vendido',
            'descricao'      => 'nullable|string',
            'destaque'       => 'boolean',
            'placa'          => 'nullable|string|max:10',
            'renavam'        => 'nullable|string|max:20',
            'chassi'         => 'nullable|string|max:25',
            'features'       => 'nullable|array',
            'features.*'     => 'string|max:100',
        ];
    }

    public function attributes()
    {
        return [
            'marca'          => 'Marca',
            'modelo'         => 'Modelo',
            'ano_fabricacao' => 'Ano de Fabricação',
            'ano_modelo'     => 'Ano do Modelo',
            'km'             => 'Quilometragem',
            'preco'          => 'Preço',
            'cor'            => 'Cor',
            'combustivel'    => 'Combustível',
            'transmissao'    => 'Transmissão',
            'portas'         => 'Portas',
            'categoria'      => 'Categoria',
            'status'         => 'Status',
        ];
    }
}
