<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Locations;
use App\Http\Controllers\Tools;
use DB;
use App\Http\Resources\closeLocation as NearByMe;
use App\Http\Requests\newLocation as NewLoc;
class API extends Controller
{
    public function closeLocation(string $code)
    {
        try {
            $infoPostCode = Tools::getLatLng(str_replace(' ','',$code));
        } catch (\Exception $ex) {
            return response()->json([
                'status' => false,
                'error' => 'The Postcode is invalid!'
            ],200);
        }
        [ "lat" => $latitude, "lng" => $longitude ] = $infoPostCode;
        $dist = "( 3959 * acos( cos( radians($latitude) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians($longitude) ) + sin( radians($latitude) ) * sin(radians(lat)) ) )";
        $closeLocation = Locations::select('*',DB::raw("$dist AS dist" ))->orderBy('dist', 'ASC')->take(1)->get();
        $closeLocation[0]->timeTable = json_decode($closeLocation[0]->timeTable);
        $closeLocation[0]->address = $infoPostCode['region'].', '.$infoPostCode['city'].', '.$closeLocation[0]->postCode;
        return NearByMe::collection($closeLocation);
    }
    public function newLocation(Request $r)
    {
        if($r->isJson())
        {
            $postcode = str_replace(' ','',$r->input('postcode'));
            if(Locations::where('postCode',$postcode)->exists())
            {
                return response()->json([
                    'status' => false,
                    'error' => 'The Postcode already exist!'
                    ],200);
            }
            try {
                $infoPostCode = Tools::getLatLng($postcode);
                $open = $r->input('opening_times');
                $close = $r->input('closing_times');
                $timetable = [
                    'Mon' => ((array_key_exists("monday",$open) and array_key_exists("monday",$close)) ? ['open'=> $open['monday'],'close'=>$close['monday']] : null),
                    'Tue' => ((array_key_exists("tuesday",$open) and array_key_exists("tuesday",$close)) ? ['open'=> $open['tuesday'],'close'=>$close['tuesday']] : null),
                    'Wed' => ((array_key_exists("wednesday",$open) and array_key_exists("wednesday",$close)) ? ['open'=> $open['wednesday'],'close'=>$close['wednesday']] : null),
                    'Thu' => ((array_key_exists("thursday",$open) and array_key_exists("thursday",$close)) ? ['open'=> $open['thursday'],'close'=>$close['thursday']] : null),
                    'Fri' => ((array_key_exists("friday",$open) and array_key_exists("friday",$close)) ? ['open'=> $open['friday'],'close'=>$close['friday']] : null),
                    'Sat' => ((array_key_exists("saturday",$open) and array_key_exists("saturday",$close)) ? ['open'=> $open['saturday'],'close'=>$close['saturday']] : null),
                    'Sun' => ((array_key_exists("sunday",$open) and array_key_exists("sunday",$close)) ? ['open'=> $open['monday'],'close'=>$close['sunday']] : null),
                ];
                //New Object to add a new Location
                $location = new Locations();
                $location->postCode = $postcode;
                $location->lat = $infoPostCode['lat'];
                $location->lng = $infoPostCode['lng'];
                $location->timeTable = json_encode($timetable);
                $location->save();
                return response()->json([
                    'status' => true,
                    'message' => 'The new location was added!'
                    ],200);
            } catch (\Exception $ex) {
                return response()->json([
                    'status' => false,
                    'error' => 'The Postcode is invalid!'
                ],200);
            }
        }
        else
        {
            return response()->json([
            'status' => false,
            'error' => "Your body post is not a JSON."
            ],200);
        }    
    }
    public function cashBack(Request $r)
    {
        if($r->isJson())
        {
            $ristretto = $r->input('Ristretto') or 0;
            $espresso  = $r->input('Espresso') or 0;
            $lungo = $r->input('Lungo') or 0;
            $capsules = 0;
            $capsules += ($ristretto + $espresso + $lungo);
            if($capsules === 0){
                return response()->json(['message' => 'Invalid quantity'], 200);
            }
            $moneyBack = 0;
            switch ($capsules) {
                case 0 < $capsules && $capsules <= 50:
                    $moneyBack = $ristretto * 2 + $espresso * 4 + $lungo * 6;
                    break;
                case 50 < $capsules && $capsules <= 500:
                    $moneyBack = $ristretto * 3 + $espresso * 6 + $lungo * 9;
                    break;
                case $capsules > 500:
                    $moneyBack = $ristretto * 5 + $espresso * 10 + $lungo * 15;
                    break;
            }
            $cash = $moneyBack/100.0;
            return response()->json([
                'status' => true,
                'message' => "You will receive £". $cash.'!'
                ],200);
        }
        else
        {
            return response()->json([
                'status' => false,
                'error' => "Your body post is not a JSON."
                ],200);
        }
    }
}
