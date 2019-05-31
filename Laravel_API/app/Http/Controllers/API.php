<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Locations;
use App\Http\Controllers\Tools;
use DB;
use App\Http\Resources\closeLocation as NearByMe;
use App\Http\Requests\newLocation as NewLoc;
use App\OldCashBack;
class API extends Controller
{
    
    protected $PATH = "\location_data.csv";
    public function closeLocation(string $code)
    {
        //In the try the code will check if the postcode is right.
        try {
            $infoPostCode = Tools::getLatLng(str_replace(' ','',$code));
        } catch (\Exception $ex) {
            //If the postcode is incorect the API will return the next respond.
            return response()->json([
                'status' => false,
                'error' => 'The Postcode is invalid!'
            ],200);
        }
        //In the next line I parsed the respond from the above function.
        [ "lat" => $latitude, "lng" => $longitude ] = $infoPostCode;
        //The following formula is to calculate the distance using the lat and lng.
        $dist = "( 3959 * acos( cos( radians($latitude) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians($longitude) ) + sin( radians($latitude) ) * sin(radians(lat)) ) )";
        //The next query will select from the Locations table the nearest postcode.
        $closeLocation = Locations::select('*',DB::raw("$dist AS dist" ))->orderBy('dist', 'ASC')->take(1)->get();
        //The next line will decode the timetable to be easy to use in another apps.
        $closeLocation[0]->timeTable = json_decode($closeLocation[0]->timeTable);
        //Using the respond from $infoPostCode I created an address based on City,Region and the Postcode.
        $closeLocation[0]->address = $infoPostCode['region'].', '.$infoPostCode['city'].', '.$closeLocation[0]->postCode;
        //Using the next collection resource I will return just what I need to return in this case the address and timetable.
        return NearByMe::collection($closeLocation);
    }
    public function newLocation(Request $r)
    {
        if($r->isJson())
        {
            //The postcode was filtered by any empty space to prevent any problems base on this aspect.
            $postcode = str_replace(' ','',$r->input('postcode'));
            //The next line will check if the above postcode is already in our DB.
            if(Locations::where('postCode',$postcode)->exists())
            {
                return response()->json([
                    'status' => false,
                    'error' => 'The Postcode already exist!'
                    ],200);
            }
            //I used try, because the API to inspect the postcode can return an error.
            try {
                //I will save all details about the current postcode in this variable.
                $infoPostCode = Tools::getLatLng($postcode);
                //I get the request parameters and saved in few variables.
                $open = $r->input('opening_times');
                $close = $r->input('closing_times');
                //The next variable will create the location's timetable. (null=close in that day)
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
                //Return the error when the Postcode is invalid.
                return response()->json([
                    'status' => false,
                    'error' => 'The Postcode is invalid!'
                ],200);
            }
        }
        else
        {
            //Return this error when the post body is not a JSON.
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
            //Get all parameterss if the parameters are empty the variable will be used as 0.
            $ristretto = $r->input('Ristretto') or 0;
            $espresso  = $r->input('Espresso') or 0;
            $lungo = $r->input('Lungo') or 0;
            $capsules = 0;
            //The total number of capsules.
            $capsules += ($ristretto + $espresso + $lungo);
            if($capsules === 0){
                //Return the next message when the quantity is 0.
                return response()->json(['message' => 'Invalid quantity'], 200);
            }
            $moneyBack = 0;
            //Depend of the total capsules the next switch will apply a formula to get the total cashback.
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
            //The total money will be converted to pounds.
            $cash = $moneyBack/100.0;
            //A new Object to add a new row in OldCashBack table.
            $oldCashBack = new OldCashBack();
            $oldCashBack->ip = \Request::ip(); //The real IP
            $oldCashBack->agent = $_SERVER['HTTP_USER_AGENT']; //The browser Agent
            $oldCashBack->postData = json_encode($r->all()); //All paramaters sent above.
            $oldCashBack->cashBack = $cash; //The total cashback
            $oldCashBack->save(); //The object will be saved in the DB.
            //The next message will be returned to the user with the total cashback.
            return response()->json([
                'status' => true,
                'message' => "You will receive £". $cash.'!'
                ],200);
        }
        else
        {
            //If the body is not a valid JSON the next response will be returned.
            return response()->json([
                'status' => false,
                'error' => "Your body post is not a JSON."
                ],200);
        }
    }
}
