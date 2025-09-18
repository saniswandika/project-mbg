<?php

namespace App\Http\Requests;

use App\Models\rekomendasi_keringanan_pbb;
use Illuminate\Foundation\Http\FormRequest;

class Updaterekomendasi_keringanan_pbbRequest extends FormRequest
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
        $rules = rekomendasi_keringanan_pbb::$rules;
        
        return $rules;
    }
}
