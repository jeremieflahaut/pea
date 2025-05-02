<?php

namespace App\Http\Requests;

use App\Enums\AllocationTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateAllocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $allocation = $this->route('allocation');

        return $allocation && $allocation->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $allocation = $this->route('allocation');

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('allocations')
                    ->where('user_id', $this->user()->id)
                    ->ignore($allocation?->id),
            ],
            'type' => [
                'required',
                new Enum(AllocationTypeEnum::class),
            ],
            'target_percent' => ['required', 'required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
