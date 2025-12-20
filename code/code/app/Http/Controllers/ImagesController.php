<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImagesController extends Controller
{
     public function showimagestemp($filename)
    {   
        $storagePath = storage_path('uploads/temp/'.$filename);
        return response()->file($storagePath, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    public function showimagesattach($foldername, $filename)
    {   
        $storagePath = storage_path('uploads/attachments/'.$foldername.'/'.$filename);
        return response()->file($storagePath, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }

    public function showimagesmarkattach($foldername, $filename)
    {   
        $storagePath = storage_path('uploads/markedattachments/'.$foldername.'/'.$filename);
        return response()->file($storagePath, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
	
	public function imageslevelthree($foldername, $filename) 
    {   
        $storagePath = storage_path('uploads/markedattachments/'.$foldername.'/L3/'.$filename);
        return response()->file($storagePath, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
        //return Image::make($storagePath)->response();
    }

    public function showamendImage($foldername){
        $getfolder =  base64_decode($foldername); 
        $storagePath = storage_path('uploads/amend/'.$getfolder);
        return response()->file($storagePath, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
    }
	
}
