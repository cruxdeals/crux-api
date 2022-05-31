<?php
namespace App\services;

class Helper
{
    public static function randomString($length = 32, $numeric = false)
    {
        $random_string = "";
        while (strlen($random_string) < $length && $length > 0) {
            if ($numeric === false) {
                $randnum = mt_rand(0, 61);
                $random_string .= ($randnum < 10) ?
                chr($randnum + 48) : ($randnum < 36 ?
                    chr($randnum + 55) : $randnum + 61);
            } else {
                $randnum = mt_rand(0, 9);
                $random_string .= chr($randnum + 48);
            }
        }
        return $random_string;
    }
}
