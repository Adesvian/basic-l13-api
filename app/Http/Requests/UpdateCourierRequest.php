<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courier = $this->route('courier');

        return [
            'first_name' => ['required', 'string', 'max:150'],
            'last_name'  => ['required', 'string', 'max:150'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'email'      => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('couriers', 'email')->ignore($courier),
            ],
            'address'    => ['nullable', 'string'],
            'level_id'   => ['required', 'integer', 'exists:levels,id'],
            'is_active'  => ['required', 'boolean'],
        ];
    }
}
