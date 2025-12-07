<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:customers,email,".$this->customer->id,
            'phone' => 'nullable|string|max:30',
        ];
    }
}
