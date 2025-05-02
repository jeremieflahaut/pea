<?php

namespace App\Http\Requests;

use App\Enums\AllocationTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreAllocationRequest extends FormRequest
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
            'isin' => [
                'required',
                'string',
                Rule::unique('allocations', 'isin')->where('user_id', $this->user()->id),
            ],
            'name' => [
                'required',
                'string',
                Rule::unique('allocations', 'name')->where('user_id', $this->user()->id),
            ],
            'ticker' => [
                'required',
                'string',
                Rule::unique('allocations', 'ticker')->where('user_id', $this->user()->id),
            ],
            'type' => [
                'required',
                new Enum(AllocationTypeEnum::class),
            ],
            'target_percent' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
        ];
    }
}
