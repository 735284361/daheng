<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
        return [
            //
            'goodsJsonStr' => 'required|json',
            'name' => 'required',
            'phone' => 'required|regex:/^1[345789][0-9]{9}$/',
            'province' => 'required',
            'city' => 'required',
            'county' => 'required',
            'detail_info' => 'required',
            'postal_code' => 'nullable',
            'remark' => 'nullable'
        ];
    }
}
