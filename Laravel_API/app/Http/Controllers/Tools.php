<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Tools extends Controller
{

    //The function will get the Lat/Lng for the specific Postcode and base on the result will return true or false.
    public static function getLatLng(string $code)
    {
       try {
        $req = new \GuzzleHttp\Client();//I called the object in my function.
        //I used this library to make a request to the ENDPOINT API to lookup the postcode.
        $get = $req->request('GET',env('Postcode_Lookup_API').$code);
        if ($get->getStatusCode() == 200 ) {
            $r = json_decode($get->getBody())->result;
            //When the status is true the code will return both coords.
            return [
                'status' => true,
                'lat' => $r->latitude,
                'lng' => $r->longitude,
                'region' => $r->region,
                'city' => $r->nuts
            ];
        } else {
            return [
                'status' => "aici"
            ];
        }
       } catch (\Exception $ex) {
            return [
                'status' => $ex
            ];
       }
    }
}
