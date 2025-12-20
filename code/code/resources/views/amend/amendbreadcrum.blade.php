@php
if(isset($breadcrumshow) && $breadcrumshow == 'N'){
    $stepdisabled = 'N';
}else{
    $stepdisabled = 'Y';
}

if(!function_exists('generateBreadCrumb')){
function generateBreadCrumb($page,$step,$stepdisabled)
    {   
      try { 

        $stepclass = 'step-'.$step;
        $label = $stepclass;
        $colorclass = 'blu-clr'; 
        
        $urlfunction = "";

    
    
    switch ($step) {
     case '1':
             $label = 'Customer Selection';
             $checkfield =  '';    
             $urlfunction = ($page == $step)? '' : "redirectUrl('','/bank/checkamendcustomer')";
        break;
     case '2':
             $label = 'Amendment';
             $checkfield =  '';    
             // $urlfunction = ($page == $step)? '' : "redirectUrl('','/bank/fetchdataperselectedid')"; 
        break;
     case '3':
            $label = 'Documents Upload';
            $checkfield = '';
            $urlfunction = ($page == $step)? '' : "redirectUrl('','/bank/amendinput')";
        break;
     case '4':
            $label = 'Review and Submit';
            $checkfield = '';
            $urlfunction = ($page == $step)? '' : "redirectUrl('','')";
     default:
        break;
    }
    
    $colorclass = '';
    // if(($is_review == 1) && (isset($reviewSectionDetails[$checkfield]))){
        
    //    $colorclass =  'red-clr';    
    // }else{

        if($step < $page){
           
           $colorclass = 'step-'.$step;

        }if($step == $page){
           
           $colorclass = 'blu-clr';
        }
        if($step > $page){
           
           $colorclass = 'gry-clr';
		   $urlfunction = '';
        }
        
    // } 
    
    $html =  '<div class="col-3">';
    $html .=    '<div class="process-step-cont">';
    if($stepdisabled == 'N'){

        $html .=      '<div class="process-step '.$stepclass.' '.$colorclass.'"></div>'; 
    }else{
        $html .=      '<div class="process-step '.$stepclass.' '.$colorclass.'" onclick="'.$urlfunction.'"></div>'; 

    }
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
    @for($i = 1; $i <= 4;$i++)
      @php
          $currentbreadcrumb = generateBreadCrumb($page,$i,$stepdisabled);
          echo $currentbreadcrumb; 
      @endphp

    @endfor
</div> 


@push('scripts')
<script src="{{ asset('custom/js/amend.js') }}"></script>
@endpush