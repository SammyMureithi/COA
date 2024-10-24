<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PdfQuestioneer extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }

  
    public function rules(): array
    {
        return [
            'file' => 'required|mimes:pdf|max:2048', 
        ];
    }

  
    public function messages()
    {
        return [
            'file.required' => 'A PDF file is required.',
            'file.mimes' => 'The file must be a PDF.',
            'file.max' => 'The PDF file may not be larger than 2MB.',
        ];
    }
}
