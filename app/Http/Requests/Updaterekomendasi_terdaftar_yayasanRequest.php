<?php

namespace App\Http\Requests;

use App\Models\rekomendasi_terdaftar_yayasan;
use Illuminate\Foundation\Http\FormRequest;

class Updaterekomendasi_terdaftar_yayasanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = rekomendasi_terdaftar_yayasan::$rules;
        
        return $rules;
    }
}
