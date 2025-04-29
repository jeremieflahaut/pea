<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'isin' => ['required', 'string', 'size:12'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'price' => ['required', 'numeric', 'min:0'],
            'type' => ['required','in:buy,sell'],
            'date' => ['required','date'],
        ];
    }
}
