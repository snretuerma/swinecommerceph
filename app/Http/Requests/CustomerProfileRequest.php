<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CustomerProfileRequest extends Request
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
            'address_addressLine1' => 'required',
            'address_addressLine2' => 'required',
            'address_province' => 'required',
            'address_zipCode' => 'required|digits:4',
            'mobile' => 'required|digits:11|regex:/^09/',
            'farmAddress.*.name' => 'required',
            'farmAddress.*.addressLine1' => 'required',
            'farmAddress.*.addressLine2' => 'required',
            'farmAddress.*.province' => 'required',
            'farmAddress.*.zipCode' => 'required|digits:4',
            'farmAddress.*.farmType' => 'required',
            'farmAddress.*.mobile' => 'required|digits:11|regex:/^09/',
        ];
    }
}
