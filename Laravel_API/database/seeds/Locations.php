<?php

use Illuminate\Database\Seeder;
use App\Locations;
use App\Http\Controllers\Tools;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class LocationSeeder extends Seeder
{
        /**
     * Path to the CSV file with all Coffee Drop informations.
     *
     * @var string
     */
    protected $PATH = __DIR__."/location_data.csv";
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Check the path integrity.
         if ( ( $h = fopen($this->PATH, "r" ) ) !== FALSE ) {
            // Actual line
            $line = 1;
            while ( ( $data = fgetcsv( $h, 1000, "," ) ) !== FALSE ) {
                // The header from the CSV will be skipped.
                if ( $line === 1 ) {
                    $line++;
                    continue;
                }
                //Check Postcode and get the Lat/Lng if the Postcode is validated.
                $latLngResult = Tools::getLatLng(str_replace(' ','',$data[0]));
                if($latLngResult['status'])
                {
                [ "lat" => $lat, "lng" => $lng ] = $latLngResult;
               
                     //The next variable will create the location's timetable. (null=close in that day)
                    $timetable = [
                        'Mon' => (($data[1]!=null and $data[8]!=null) ? ['open'=> $data[1],'close'=>$data[8]] : null),
                        'Tue' => (($data[2]!=null and $data[9]!=null) ? ['open'=> $data[2],'close'=>$data[9]] : null),
                        'Wed' => (($data[3]!=null and $data[10]!=null) ? ['open'=> $data[3],'close'=>$data[10]] : null),
                        'Thu' => (($data[4]!=null and $data[11]!=null) ? ['open'=> $data[4],'close'=>$data[11]] : null),
                        'Fri' => (($data[5]!=null and $data[12]!=null) ? ['open'=> $data[5],'close'=>$data[12]] : null),
                        'Sat' => (($data[6]!=null and $data[13]!=null) ? ['open'=> $data[6],'close'=>$data[13]] : null),
                        'Sun' => (($data[7]!=null and $data[14]!=null) ? ['open'=> $data[7],'close'=>$data[14]] : null),
                    ];
                    //New Object to add a new Location
                    try {
                        $location = new App\Locations();
                    $location->postCode = str_replace(' ','',$data[0]);
                    $location->lat = $lat;
                    $location->lng = $lng;
                    $location->timeTable = json_encode($timetable);
                    $location->save();
                    } catch (\Exception $th) {
                        echo "The Postcode(".$data[0].") already exist.\n";
                    }
                }
                else{
                    echo "Error";
                }
                // Increase line number.
                $line++;
            }
        }
    }
}
