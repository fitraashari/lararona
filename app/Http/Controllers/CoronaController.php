<?php

namespace App\Http\Controllers;

use App\Corona;
use Illuminate\Http\Request;
use GuzzleHttp;

class CoronaController extends Controller
{
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
        try {
            $client = new GuzzleHttp\Client([
                'timeout'  => 30.0,
            ]);
            //ambil data json
            $response = $client->request('GET', 'https://data.covid19.go.id/public/api/prov.json');
            //conver ke dalam array
            $data = json_decode($response->getBody()->getContents(),true);
            //encode ke dalam json array list_data
            $list_data = json_encode($data['list_data']);
            //simpan ke dalam database
            $store = Corona::updateOrCreate([
                'last_date'=>$data['last_date'],
            ],[
                'list_data'=>$list_data
            ]);
        } catch (\Throwable $th) {
            //jika ambil data json dari server pemerintah gagal maka ambil data dari database
            $query = Corona::orderBy('updated_at','desc')->first();
            $data['last_date']=$query->last_date;
            $data['list_data']=json_decode($query->list_data,true);
        }
        foreach ($data['list_data'] as $key => $prov) {
            $data['list_data'][$key]=['id'=>$key+1]+$data['list_data'][$key];
        }
        // dd($data['list_data']);
        return $data;
    }
}
