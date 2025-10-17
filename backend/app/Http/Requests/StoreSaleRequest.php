<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Como não há autenticação implementada, sempre autoriza.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Regras de validação para cadastro de venda:
     * - seller_id: obrigatório, deve existir na tabela sellers
     * - amount: obrigatório, numérico, entre 0.01 e 99.999.999,99, máximo 2 casas decimais
     * - sale_date: obrigatório, formato de data válido, não pode ser futuro
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seller_id' => ['required', 'integer', 'exists:sellers,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'sale_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'seller_id.required' => 'O vendedor é obrigatório.',
            'seller_id.integer' => 'O vendedor deve ser um número válido.',
            'seller_id.exists' => 'O vendedor selecionado não existe.',
            'amount.required' => 'O valor da venda é obrigatório.',
            'amount.numeric' => 'O valor da venda deve ser um número.',
            'amount.min' => 'O valor da venda deve ser maior que zero.',
            'amount.max' => 'O valor da venda não pode ser maior que R$ 99.999.999,99.',
            'amount.decimal' => 'O valor da venda deve ter no máximo 2 casas decimais.',
            'sale_date.required' => 'A data da venda é obrigatória.',
            'sale_date.date' => 'A data da venda deve ser uma data válida.',
            'sale_date.before_or_equal' => 'A data da venda não pode ser no futuro.',
        ];
    }
}
