<?php

namespace App\Http\Requests;

use App\Models\rekomendasi_bantuan_pendidikan;
use Illuminate\Foundation\Http\FormRequest;

class Updaterekomendasi_bantuan_pendidikanRequest extends FormRequest
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
        $rules = rekomendasi_bantuan_pendidikan::$rules;
        
        return $rules;
    }
}
