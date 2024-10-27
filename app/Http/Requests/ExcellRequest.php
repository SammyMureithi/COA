<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExcellRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'file' => 'required|mimes:xls,xlsx|max:2048',
        ];
    }
    public function messages()
    {
        return [
            'file.required' => 'An Excel file is required.',
            'file.mimes' => 'The file must be an Excel file (.xls or .xlsx).',
            'file.max' => 'The Excel file may not be larger than 2MB.',
        ];
    }
}
