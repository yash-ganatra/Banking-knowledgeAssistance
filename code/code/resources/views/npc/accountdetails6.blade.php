@php 
$label = '';
$clearText = '&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i><br>';
$notDone = '&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i><br>';

$buttonDoneTxt = '<button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>';
                      
$buttonCreateTxt = '<button class="btn check_button" id="check_tdaccount_created" formId="'.$formId.'">Create</button>';
$buttondisabled =  '<button class="btn disabled_button" disabled="">Create</button>';
$buttontransfer = '<button class="btn trasnfer_button" id="fund_transfer" formId="'.$formId.'">Transfer</button>';
$button = '';
$comment = '';

    if((isset($accountDetails['account_type'])) && ($accountDetails['account_type'] == 4) || ($accountDetails['account_type'] == 3)){
        $label = 'Check TD acccount Created';
    }else{
        $label = 'Savings FTR';
    }
 

    if((isset($accountDetails['account_type'])) && ($accountDetails['account_type'] == 4) || ($accountDetails['account_type'] == 3) ){
        if($finacleHist == ''){
            $comment .= 'TD Not Processed'.$notDone;
        }elseif($accountDetails['td_account_no'] != ''){
            $comment .= 'Account No: '.$accountDetails['td_account_no'].$clearText;
        }elseif($checktdexist['td_response'] != ''){
           $comment .= 'Error:'. $checktdexist['td_response'].$notdone;
        }else{
            $notDone;
        }
    }else{
        if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] == 'Y') || ($accountDetails['fund_transfer_status'] == 1)){
                $comment = $fundingStatus['ftr_reference_no'].$clearText;
        }else{
            $notDone;
        }
    }
    if($tdApiShow == 'API'){
        $finacleHist = 'TD API';
    }

    if(isset($accountDetails['account_type']) && ($accountDetails['account_type'] == 3) || ($accountDetails['account_type'] == 4)){
        if($finacleHist == ''){
            $button = $buttondisabled;
        }elseif($accountDetails['td_account_no'] == ''){
            $button = $buttonCreateTxt;
        }else{
            $button = $buttonDoneTxt;
        }
    }else{
        if($acctIdButtonReqd){
            $button = $buttondisabled;
        }elseif((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] == 'Y') || ($accountDetails['fund_transfer_status'] == 1)){
            $button = $buttonDoneTxt;
        }else{
            if($accountDetails['account_no'] == '' || $accountDetails['account_type'] == 2 && $currentdetails['entity_account_no'] == ''){
      			$button = $buttondisabled; 
            }else{
        		$button = $buttontransfer;
    		}                  
    	}
    }	


    $hideorshow = 'show';
    if($tdApiShow == 'API' && $accountDetails['account_type'] == 4){
        $label = 'Savings FTR';
        $button = $buttontransfer;
        $comment = 'FTR'.$notDone;
        if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] == 'Y') || ($accountDetails['fund_transfer_status'] == 1)){
            $button = $buttonDoneTxt;
            $comment = $fundingStatus['ftr_reference_no'].$clearText;
        }
    }
    if($tdApiShow == 'API' && $accountDetails['account_type'] == 3){
        $hideorshow = 'hide';
    }



@endphp
@if($hideorshow == 'show')
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
                     {!! $label !!}
               
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
@endif
