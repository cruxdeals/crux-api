<?php
namespace App\services;

use Illuminate\Support\Facades\Http;


class UtilitiesServices
{
    public static function verifyBVN($number)
    {
        $url = 'https://api.myidentitypay.com/api/v1/biometrics/merchant/data/verification/bvn';
        $post_array = ['number' => $number];
        return Http::withHeaders(['x-api-key' => 'ZklUpIYL.drCcZvID3sDWLw5pYCLBVf6e86rYyMYB'])->
            post($url, $post_array);
    }
}
