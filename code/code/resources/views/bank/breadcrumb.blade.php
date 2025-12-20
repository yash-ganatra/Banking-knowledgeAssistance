 @php
if(!function_exists('generateBreadCrumb')){
function generateBreadCrumb($page,$step,$is_review,$formId,$accountType,$reviewSectionDetails)
    {   
      try { 
     

    $stepclass = 'step-'.$step;
    $label = $stepclass;
    $colorclass = 'blu-clr'; 
    
    $urlfunction = "redirectUrl('123','/bank/addovddocuments')";

    
    
    switch ($step) {
     case '1':
             $label = 'Basic Details';
             $checkfield =  'customer_on_boarding';    
             $urlfunction = ($page == $step)? '' : "redirectUrl('".$is_review."_".$formId."','/bank/addaccount')";
         break;

     case '2':
             $label = 'OVD Documents';
             $checkfield =  'ovd_proofs'; 
             $urlfunction = ($page == $step)? '' : "redirectUrl('$formId','/bank/addovddocuments')";
         break;

     case '3':
             $label = 'CIDD';
             $checkfield =  'risk_classfication';    
             $urlfunction = ($page == $step)? '' : "redirectUrl('$formId','/bank/addriskclassification')";
         break;

     case '4':
             $label = ($accountType == 3) ?'TD Initial Funding': 'Initial Funding';
             $checkfield =  'initial_funding';    
             $urlfunction = ($page == $step)? '' : "redirectUrl('$formId','/bank/addfinancialinfo')";
         break;

     case '5':
             $label = 'Nomination';
             $checkfield =  'nominee_details';    
             $urlfunction = ($page == $step)? '' : "redirectUrl('$formId','/bank/addnomineedetails')";
         break;

     case '6':
             $label = 'Customer Declarations';
             $checkfield =  'declarations';    
             $urlfunction = ($page == $step)? '' : "redirectUrl('$formId','/bank/declaration')";
         break;

    case '7':
             $label = 'Review & Submit';
             $checkfield =  'reviewsubmitpage';    
             $urlfunction = ($page == $step)? '' : "redirectUrl('$formId','/bank/submission')";
         break;
     
     default:
         # code...
         break;
    }
    
    $colorclass = '';
    if(($is_review == 1) && (isset($reviewSectionDetails[$checkfield]))){
        
       $colorclass =  'red-clr';    
    }else{

        if($step < $page){
           
           $colorclass = 'step-'.$step;

        }if($step == $page){
           
           $colorclass = 'blu-clr';
        }
        if($step > $page){
           
           $colorclass = 'gry-clr';
		   $urlfunction = '';
        }
        
    } 

    if((Session::get('role') == "11")){

       $urlfunction = '';
        $getccaccountType = DB::table('ACCOUNT_DETAILS')->select('SCHEME_CODE','SOURCE')->whereId($formId)->get()->toArray();
        $getccaccountType = (array) current($getccaccountType);
        $steps_to_disable = array(2,3,6);


       if(isset($getccaccountType['scheme_code']) && $getccaccountType['scheme_code'] != '' && isset($getccaccountType['source']) && $getccaccountType['source'] == 'CC'){    
        $is_nri_cc = DB::table('TD_SCHEME_CODES')->whereId($getccaccountType['scheme_code'])->where('RI_NRI','NRI')->count();
            if($is_nri_cc == 1){
                $steps_to_disable = array(2,3);
            }
        }

      if(in_array($step,$steps_to_disable)){

        $colorclass = 'gry-clr'; $stepclass = 'force_gray';
      }

    }
    
    $html =  '<div class="col-3">';
    $html .=    '<div class="process-step-cont">';
    $html .=       '<div class="process-step '.$stepclass.' '.$colorclass.'" onclick="'.$urlfunction.'"></div>';
    $html .=       '<span class="process-label">'.$label.'</span>';
    $html .=     '</div>';
    $html .=  '</div>';



    return $html;

            


       }
        catch(\Illuminate\Database\QueryException $e) {
            if (env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            return '';
        }
    }
}

 @endphp
 <style type="text/css">
   .force_gray{
    background-color: #b9b9b9 !important;
   }
 </style>


                       
                            <div class="process-main">


                                @for($i = 1; $i <= 7; $i++)
                                  @php
                                      $currentbreadcrumb = generateBreadCrumb($page,$i,$is_review,$formId,$accountType,$reviewSectionDetails);
                                      echo $currentbreadcrumb; 
                                  @endphp

                                @endfor
                                



                                



                            </div> 
                       