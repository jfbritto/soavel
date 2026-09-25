<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Veículos de troca só fazem sentido em permuta/misto. Blocos do formulário
     * que vieram totalmente em branco (ex.: o bloco padrão não preenchido) são
     * descartados antes da validação.
     */
    protected function prepareForValidation()
    {
        $trocas = $this->input('trocas');

        if (!in_array($this->input('tipo_pagamento'), ['permuta', 'misto']) || !is_array($trocas)) {
            $this->request->remove('trocas');
            return;
        }

        $trocas = array_values(array_filter($trocas, function ($troca) {
            if (!is_array($troca)) {
                return false;
            }

            foreach ($troca as $valor) {
                if ($valor !== null && $valor !== '') {
                    return true;
                }
            }

            return false;
        }));

        $this->merge(['trocas' => $trocas]);
    }

    public function rules()
    {
        $anoMax = date('Y') + 1;

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

            // Veículos de troca (0..N), cada um com seu valor avaliado
            'trocas'                    => 'nullable|array',
            'trocas.*.valor_troca'      => 'nullable|numeric|min:0',
            'trocas.*.marca'            => 'required|string|max:50',
            'trocas.*.modelo'           => 'required|string|max:60',
            'trocas.*.versao'           => 'nullable|string|max:80',
            'trocas.*.ano_fabricacao'   => "required|integer|min:1950|max:{$anoMax}",
            'trocas.*.ano_modelo'       => "required|integer|min:1950|max:{$anoMax}",
            'trocas.*.km'               => 'required|integer|min:0',
            'trocas.*.cor'              => 'required|string|max:30',
            'trocas.*.categoria'        => 'required|in:hatch,sedan,suv,pickup,van,esportivo,outro',
            'trocas.*.combustivel'      => 'required|in:gasolina,etanol,flex,diesel,gnv,hibrido,eletrico',
            'trocas.*.transmissao'      => 'required|in:manual,automatico,cvt,semi_automatico',
        ];
    }
}
