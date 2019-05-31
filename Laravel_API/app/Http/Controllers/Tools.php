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
        $get = $req->request('GET',"http://api.postcodes.io/postcodes/".$code);
      
            $r= $get->getBody()->getContents();
            $data=json_decode($r,true);    
            if($data['status']!="200")
            {
                return [
                    'status' => false
                ];
            }   
            //When the status is true the code will return both coords.
            return [
                'status' => true,
                'lat' => $data['result']['latitude'],
                'lng' => $data['result']['longitude'],
                'region' => $data['result']['region'],
                'city' => $data['result']['nuts']
            ];

       } catch (\Exception $ex) {
            return [
                'status' => false
            ];
       }
    }
}
