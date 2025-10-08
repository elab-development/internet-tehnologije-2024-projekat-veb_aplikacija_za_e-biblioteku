<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'year' => 'required|integer|min:0',
            'genre' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20|unique:books,isbn',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Naslov knjige je obavezan.',
            'author.required' => 'Autor knjige je obavezan.',
            'year.required' => 'Godina izdanja je obavezna.',
            'year.integer' => 'Godina mora biti broj.',
            'year.min' => 'Godina ne može biti negativna.',
            'isbn.unique' => 'ISBN već postoji u bazi.',
        ];
    }
}
