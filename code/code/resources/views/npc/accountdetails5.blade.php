
@php
$comment = ''; 
$SArequired = false; $CArequired = false; $TDrequired = false;
$label = '';
    switch($accountDetails['account_type']){
        case "4": // Combo
            $label = 'Savings & TD Entry';
            $SArequired = true; $TDrequired = true; break;
        case "2":  // Current
            if($accountDetails['account_type'] == "2" && $accountDetails['scheme_code'] == 1){
            $label = 'Savings & Current';
                $SArequired = true; $CArequired = true; 
            }else{
                $label = 'Current';
                $CArequired = true; 
            }
            break;
        case "3":    // TD
            $label = 'TD Entry';
            $TDrequired = true; break;
        case "1":    // SA
            $label = 'Savings Account';
            $SArequired = true; break;         
    }

    $clearText = '&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i><br>';
    $notDone = '&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i><br>';
    
    $buttonDoneTxt = '<button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>';
    $buttonCreateTxt = '<button class="btn create_button" id="create_account_no" formId="'.$formId.'">Create</button>';
    $buttonDisable = '<button class="btn disabled_button" disabled="">Check</button>';
    $button = '';
    
    $SAbutton = 'Done'; $TDbutton = 'Done'; $CAbutton = 'Done';

    if($CArequired){
        if(isset($currentdetails['entity_account_no']) && ($currentdetails['entity_account_no'] != '')){
            $comment .= 'CA: '.$currentdetails['entity_account_no'].$clearText;
        }else{                    
            $comment .='CA: '.$notDone;   
            $CAbutton = 'Create';     
        }
    }  

    if($TDrequired){
        if($tdApiShow == 'TABLE'){
            if(isset($checktdexist['td_entry_done']) && $checktdexist['td_entry_done'] == 'Y'){
                $comment .= 'TD Entry'.$clearText;
            }else{                    
                $comment .='TD Entry: '.$notDone;  
                $TDbutton = 'Create';                        
            }
        }

        if($tdApiShow == 'API'){
            if(isset($accountDetails['td_account_no']) && $accountDetails['td_account_no'] != ''){
                $comment .= 'TD: '.$accountDetails['td_account_no'].$clearText;
                $label  = 'TD ACCOUNT';
            }else{
                $comment .= 'TD: '.$notDone;
                $label  = 'TD ACCOUNT';
                $TDbutton = 'Create';                        
            }
        }
        
    }
    
    if($SArequired){
        if(isset($accountDetails['account_no']) && ($accountDetails['account_no'] != '')){
            $comment .= 'SA: '.$accountDetails["account_no"].$clearText;
        }else{                    
            $comment .= 'SA: '.$notDone;
            $SAbutton = 'Create';                              
        }
    }

    if($fundingStatus['funding_status'] != 'Y'){
        $button = $buttonDisable;
    }elseif($SAbutton == 'Create' || $TDbutton == 'Create' || $CAbutton == 'Create'){
        $button = $buttonCreateTxt;
    }else{
        $button = $buttonDoneTxt;
    }

@endphp


<div class="timeline timeline-5 accountdetails">
    <!--begin::Item-->
    <div class="timeline-item align-items-start text-muted">
        <!--begin::Badge-->
        <div class="timeline-badge">
            <i class="fa fa-genderless text-grey icon-xl"></i>
        </div>
        <!--end::Badge-->
        <!--begin::Content-->
        <div class="timeline-content d-flex">
            <!--begin::Text-->
            <div class="font-weight-mormal font-size-lg timeline-content pl-3">
                <div class="content-blck-1 content-blck-tl px-3">
                     {{$label}}            
                </div>                
                <div class="content-blck-2 content-blck-tl px-2">
                     {!! $comment !!}
                </div>
                <div class="content-blck-3 content-blck-tl px-2">
                    -
                </div>
                <div class="content-blck-4 content-blck-tl px-2">
                    -
                </div>
                <div class="content-blck-6 content-blck-tl px-2">
                    -
                </div>
                <div class="content-blck-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    
                  {!! $button !!}
                       
                </div>
            </div>
            <!--end::Text-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Item-->
</div>