<?php

namespace App\Http\Requests;

use App\Helpers\AuthHelper;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {                
        return [
            'name' => 'string|max:50',
            'phone_number' => 'string|digits_between:8,15',
            'address' => 'string|max:255',
            'file_id' => 'required'
        ];
    }
}
