<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\CommonFunctions;
use Carbon\Carbon;
use DB;
use Session;


class VersionController extends Controller
{


	public function versions(){
        $versions = config('version.VERSIONS');
        $version = json_encode($versions);

        return view('admin.version')->with('versions',json_decode($version));
    }

    public function trespassed(Request $request){

        $url = request()->headers->get('referer');        
        $uid = Session::get('userId');
        $user = Session::get('username');
        $ip = $request->ip();
        
        $userLog['user_id'] = $uid;
        $userLog['url'] = $url;
        $userLog['module'] = 'User';
        $userLog['controller'] = '';        
        $userLog['ip_address'] = $ip;
        $userLog['created_by'] = $uid;
        $userLog['integrity_time'] = Carbon::now()->format('d-m-Y H:i:s');
        $string = $userLog['module'].$userLog['controller'].$request->ip().$userLog['created_by'].$userLog['integrity_time'];
        $hash = hash('sha256', $string);
        $userLog['integrity_check'] = substr($hash, 48,16).substr($hash, 0,16);
        $userLog['comments'] = 'Client tampering detected';
        $createUserLog = DB::table('USER_ACTIVITY_LOG')->insert($userLog);

        Session::flush();

        return view('bank.trespassed')
            ->with('user',$user)
            ->with('ip',$ip);

    }    
    

}


?>