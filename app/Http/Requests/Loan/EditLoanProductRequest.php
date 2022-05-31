<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class EditLoanProductRequest extends FormRequest
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
            'id' => 'required',
            'product_name' => 'required',
            'interest_rate' => 'required',
            'maximum_amount' => 'required',
            'minimum_amount' => 'required',
            'product_id' => 'required',
            'balance' => 'required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {

        $response = response()->json([
            'status' => 'error',
            'message' => $validator->errors()
        ], 400);

        throw (new ValidationException($validator, $response))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }

    public function failedAuthorization()
    {
        throw new AuthorizationException("You don't have the authority to perform this resource");
    }
}
