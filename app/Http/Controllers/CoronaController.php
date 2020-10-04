<?php

namespace App\Http\Controllers;

use App\Corona;
use App\Daily;
use Illuminate\Http\Request;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;

class CoronaController extends Controller
{
    public function daily(){
        $data = $this->req('https://data.covid19.go.id/public/api/update.json');
        if($data){
            $json = json_encode($data);
            $store = Corona::updateOrCreate([
                'type'=>'daily',
            ],[
                'json'=>$json
            ]);
        } else {
            $query = Corona::where('type','daily')->first();
            $data=json_decode($query['json'],true);

        }
        return response()->json([
            'response_code'=>'200',
            'response_status'=>'success',
            'data'=>$data
            ],200);
    }
    public function prov(){

        $data = $this->getData();
        // dd($data);
        return response()->json([
                'response_code'=>'200',
                'response_status'=>'success',
                'data'=>[
                    'last_update'=>$data['last_date'],
                    'provinsi'=>$data['list_data']
                ]
                ],200);

    }
    public function show($id){
        $data = $this->getData();
        // dd($data);
        return response()->json([
                'response_code'=>'200',
                'response_status'=>'success',
                'data'=>[
                    'last_update'=>$data['last_date'],
                    'provinsi'=>$data['list_data'][$id-1]
                ]
                ],200);
    }
    
    public function getData(){
        $data = $this->req('https://data.covid19.go.id/public/api/prov.json');
        if ($data) {
            //encode ke dalam json array list_data
            $json = json_encode($data);
            //simpan ke dalam database
            $store = Corona::updateOrCreate([
                'type'=>'prov',
            ],[
                'json'=>$json
            ]);
        } else {
            //jika ambil data json dari server pemerintah gagal maka ambil data dari database
            $query = Corona::where('type','prov')->first();
            $data=json_decode($query['json'],true);
            
        }

        foreach ($data['list_data'] as $key => $prov) {
            $data['list_data'][$key]=['id'=>$key+1]+$data['list_data'][$key];
        }
        // dd($data['list_data']);
        return $data;
    }
    public function req($url){
        try {
            $client = new GuzzleHttp\Client([
                'timeout'  => 5.0,
            ]);
            //ambil data json
            $response = $client->request('GET', $url);
            //convert ke dalam array
            $data = json_decode($response->getBody()->getContents(),true);
            return $data;
            
        } catch (\Throwable $th) {
            return false;
        }
    }
}
