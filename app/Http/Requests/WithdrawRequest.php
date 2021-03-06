<?php

namespace App\Http\Requests;

use App\Models\UsersAccount;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
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
            'apply_total' => 'required|numeric|between:50.00,50000.00'
        ];
    }
}
