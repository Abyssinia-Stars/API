<?php

use Ichtrojan\Otp\Otp;

function generateOtp($identifier, $type='numeric', $length = 4, $validity=15){
    return (new Otp)->generate($identifier, $type, $length, $validity);;
   
}