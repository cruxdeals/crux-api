<?php
namespace App\services;

use Illuminate\Support\Facades\Http;

class VerificationServices
{
    public static function resolveBVN($bvn)
    {
        $response = UtilitiesServices::verifyBVN($bvn);
        if ($response->successful()) {
            $data = $response->json();
            if ($data["status"] == true) {
                return response(['status' => 'success', 'message' => 'successful', 'first_name' => $data['bvn_data']['firstName'], 'middle_name' => $data['bvn_data']['middleName'], 'last_name' => $data['bvn_data']['lastName'], 'formatted_dob' => $data['bvn_data']['dateOfBirth'], 'mobile' => $data['bvn_data']['phoneNumber1'], 'image' => $data['bvn_data']['base64Image']], 200);
            } else {
                return response(['status' => 'error', 'message' => 'BVN DOES NOT EXIST ON VALIDATION PORTAL'], 404);
            }
        } else {
            return response(['status' => 'error', 'message' => $response->json()['message'] ?? "Error Verifying BVN "], $response->status());
        }
    }

    public static function getBVNFromBankDetails($accountnumber, $bankcode)
    {
        $url = 'https://api.myidentitypay.com/api/v1/biometrics/merchant/data/verification/bank_account/advance';
        $post_array = ['number' => $accountnumber,'bank_code'=>$bankcode];
        $result =  Http::withHeaders(['x-api-key' => 'ZklUpIYL.drCcZvID3sDWLw5pYCLBVf6e86rYyMYB'])->
            post($url, $post_array)->json();

        if ($result['status'] == false) {
            return false;
        } elseif ($result['status'] == true) {
            if ($result['response_code'] == '00') {
                $data= $result['account_data'];
                $data['accountnumber'] = $accountnumber;
                $data['status'] = "success";
                $data['data'] = $data;
                return $data;
            }
        }
        return false;
    }
}
