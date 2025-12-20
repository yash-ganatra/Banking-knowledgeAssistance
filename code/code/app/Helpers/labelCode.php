<?php
namespace App\Helpers;

class labelCode{
	
	public static function getLabel($scheme, $input){ 
		$input = substr($input,6);
		$response = config('label.'.$scheme.'.'.$input);
		if($response == ''){
			$response = str_replace('_', ' ', $input);
			return ucwords($response);
		}else{
			return $response;
		}
			
	}

	public static function getKYClist($scheme=''){ 
		
		if($scheme==''){
			return array();
		}else{
			return config('entity.'.$scheme);
		}				
	}

	public static function getKYClistItem($scheme,$item){ 
		
		if($scheme == '' || $item == ''){
			return '';
		}else{
			$response = '';
			$response = config('entity.'.$scheme.'.'.$item);
			return $response;			
		}				
	}



}

?>