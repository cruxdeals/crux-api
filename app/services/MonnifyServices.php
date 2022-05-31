<?php
namespace App\services;

use Illuminate\Support\Facades\Http;

class MonnifyServices
{
    public static function disburse($bankcode, $accountnumber, $amount, $narration, $giroreference)
    {
        $url = "https://api.monnify.com/api/v2/disbursements/single";
        $parameter = array(
            "amount" => (int) $amount,
            "reference" => $giroreference,
            "narration" => $narration,
            "destinationBankCode" => $bankcode,
            "destinationAccountNumber" => $accountnumber,
            "currency" => "NGN",
            "sourceAccountNumber" => "8000005553",
        );
        $data = Http::withHeaders([
            'content-type' => 'application/json',
            'authorization' => 'Basic TUtfUFJPRF9BWThXTFBOVlJQOkZVREc0N1pKR1hURTRQQjIyVEJWRFdSWVM4VjJUUkJT',
        ])->post($url, $parameter);

        if ($data["requestSuccessful"] == 'true') {
            return ['status' => 'success', 'data' => $data, 'message' => 'Loan disbursed automatically with Monnify!'];
        } else {
            return ['status' => 'error', 'data' => $data, 'message' => 'Loan was not disbursed with Monnify, please check payment reference for transaction status!'];
        }
    }

}
