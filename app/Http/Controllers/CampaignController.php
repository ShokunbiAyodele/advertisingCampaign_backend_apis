<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\support\facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Storage;


class CampaignController extends Controller
{
    function StoreCampaign(Request $request){

        $request->validate([
            'name' => 'required',
            'to' => 'required',
            'from' => 'required',
            'dailyBudget' => 'required|numeric',
            'totalBudget' => 'required|numeric',
        ]);

        $dailyBudget = '';
        $totalBudget = '';


        if(strpos($request->input('dailyBudget'), '.') !== false){
            $dailyBudget = number_format(preg_replace('/[^\\d.]+/', '',$request->input('dailyBudget')),2,'.',',');
        }else{
            $dailyBudget = preg_replace('/[^\\d.]+/', '',$request->input('dailyBudget')) . '.'.'00';
        }

        if(strpos($request->input('totalBudget'), '.') !== false){
            $totalBudget = number_format(preg_replace('/[^\\d.]+/', '',$request->input('totalBudget')),2,'.',',');
        }else{
            $totalBudget = preg_replace('/[^\\d.]+/', '',$request->input('totalBudget')) . '.'.'00';
        }

        $campaignPost =  DB::table('campaign_posts')->insert([
            'name'  =>  $request->input('name'),
            'to'  => Carbon::parse($request->input('to')),
            'from'  =>   Carbon::parse($request->input('from')),
             'dailyBudget'  =>  '$'.$dailyBudget,
            'totalBudget'  =>  '$'.$totalBudget,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
      ]);

      $insertedId = DB::getPdo()->lastInsertId();

      $files = array();
      if($request->hasFile('files')){
        foreach ($request->file('files') as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
          $path =  $file->store('uploads','public');
          $url = asset('storage/' . $path);

        $filesPayload = array(
            'path' => $url,
            'fileName' => $filename,
            'campaign_posts_id' => $insertedId,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        );
        DB::table('campaign_files')->insert($filesPayload);
        }
    }
        return response()->json([
            'message' => 'successfull',
            'status' => 'ok',
            'id' =>$insertedId
        ]);
    }
    function ReadCampaign(){
      $campaignPosts =  DB::table('campaign_posts')->get();

      return response()->json([
        'data' => $campaignPosts,
        'status' => 'ok',
    ]);

    }

    function ReadCampaigns(){
        $campaignPosts =  DB::table('campaign_posts')->get();
        $campaignFiles =  DB::table('campaign_files')->get();


        $parentArray = array();
        $map = array();
        foreach($campaignPosts as $campaignPost){
          $campaignMap = array();
          array_push($campaignMap,$campaignPost);
        //   $campaignMap[] = $campaignPost;
          $fileMap = array();
          foreach($campaignFiles as $campaignFile){
              if($campaignPost->id == $campaignFile->campaign_posts_id){
                  $fileMap[] =$campaignFile;
              }
          }
          array_push($campaignMap,$fileMap);
          $parentArray[] =$campaignMap;
        }
        return response()->json([
            'data' => $parentArray,
            'status' => 'ok',
        ]);

      }

    function Previewimage($id){
       $images  = DB::table('campaign_files')->where('campaign_posts_id', $id)->get();
       if(count($images) == 0){
        return response()->json([
            'message' => 'no records fould for this id',
            'status' => 'ok',
        ]);
        return;
       }else{
        return response()->json([
            'message' => 'image gallery',
            'status' => 'ok',
            'data'   => $images
        ]);
       }
    }

    function EditCampaign($id){
        $EditCampaign =  DB::table('campaign_posts')->find($id);
        if($EditCampaign == null){
            return response()->json([
                'message' => 'record with id no '. $id .' is not availbale',
                'status' => 'ok',
            ]);
        }
        return $EditCampaign;
    }

    function updateCampaign(Request $request, $id){
        $check= DB::table('campaign_posts')->find($id);
        if($check == null){
            return response()->json([
                'message' => 'no record found',
                'status' => 'ok',
            ]);
            return;
        }

        $dailyBudget = '';
        $totalBudget = '';


        if(strpos($request->input('dailyBudget'), '.') !== false){
            $dailyBudget = number_format(preg_replace('/[^\\d.]+/', '',$request->input('dailyBudget')),2,'.',',');
        }else{
            $dailyBudget = preg_replace('/[^\\d.]+/', '',$request->input('dailyBudget')) . '.'.'00';
        }

        if(strpos($request->input('totalBudget'), '.') !== false){
            $totalBudget = number_format(preg_replace('/[^\\d.]+/', '',$request->input('totalBudget')),2,'.',',');
        }else{
            $totalBudget = preg_replace('/[^\\d.]+/', '',$request->input('totalBudget')) . '.'.'00';
        }


        $updatecampaign=DB::table('campaign_posts')->where('id', $id)->update([
            'name'  =>  $request->input('name'),
            'from'  => Carbon::parse($request->input('from')),
            'to'  =>   Carbon::parse($request->input('to')),
            'dailyBudget'  => '$' .$dailyBudget,
            'totalBudget'  =>  '$' .$totalBudget
        ]);
      $files = array();
      if($request->hasFile('files')){
        $getpreviousPath = DB::table('campaign_files')->where('campaign_posts_id',$id)->get();
        foreach($getpreviousPath as $previousPath){
            Storage::delete('upload/'. $previousPath->fileName);
        }
        foreach ($request->file('files') as $file) {
            $filename = time() . '_' . $file->getClientOriginalName();
            $path =  $file->store('uploads','public');
            $url = asset('storage/' . $path);
            $filesPayload = array(
            'path' => $url,
            'fileName' => $filename,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        );
        DB::table('campaign_files')->where('campaign_posts_id',$id)->update($filesPayload);
        }
    }
        return response()->json([
            'message' => 'update successful',
            'status' => 'ok',
        ]);

    }
}
