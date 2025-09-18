<?php

namespace App\Http\Requests;

use App\Models\rekomendasi_pengumpulan_undian_berhadiah;
use Illuminate\Foundation\Http\FormRequest;

class Createrekomendasi_pengumpulan_undian_berhadiahRequest extends FormRequest
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
        return rekomendasi_pengumpulan_undian_berhadiah::$rules;
    }
}
