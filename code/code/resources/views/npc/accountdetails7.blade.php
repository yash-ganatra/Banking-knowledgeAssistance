@php
$comment='';
$iconClearTxt='&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>';
$iconCancleTxt='&nbsp;&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>';
$button='';
$buttonDisabled=' <button class="btn disabled_button" disabled="">Mark</button>';
$buttonDone=' <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>';
$buttonMarkForm = ' <button class="btn btn-primary form_mark_button" id="mark_form_for_qc" formId="'.$formId.'">Mark</button>';

    if((isset($accountDetails['account_type'])) && ($accountDetails['account_type'] == 3)){
        if($accountDetails['td_account_no'] != '' && $accountDetails['next_role'] == 5){
           $comment .= 'Form Marked'.$iconClearTxt;
        }elseif($checktdexist['td_response'] != ''){
            $comment .= 'Error'.$checktdexist['td_response'].$iconCancleTxt;
        }else{
            $comment .= 'Role:'.$accountDetails['next_role'].$iconCancleTxt;
        }
    }else{
        if(((isset($fundingStatus['ftr_status']) && $fundingStatus['ftr_status'] == 'Y') || $accountDetails['fund_transfer_status'] == 1) && $accountDetails['next_role'] == 5){
               $comment .=  'Form Marked'.$iconClearTxt;
        }else{
               $comment .=  'Role:'.$accountDetails['next_role'].$iconCancleTxt;
        }
    }

    if($tdApiShow == 'API'){
        $finacleHist = 'TD API';
    }


    if($accountDetails['account_type'] == 3){
        if($finacleHist == ''){
            $button = $buttonDisabled;
        }elseif($finacleHist != '' && $accountDetails['td_account_no'] == '' && $accountDetails['next_role'] < 5){
            $button = $buttonDisabled;
        }elseif($accountDetails['td_account_no'] != '' && $accountDetails['next_role'] < 5){
            $button = $buttonMarkForm;
        }else{
            $button = $buttonDone;
        }
    }else{
        if($acctIdButtonReqd){
            $button = $buttonDisabled;
        }elseif(((isset($fundingStatus['ftr_status']) && $fundingStatus['ftr_status'] == 'Y') || $accountDetails['fund_transfer_status'] == 1) && $accountDetails['next_role'] == 5){
            $button = $buttonDone;
        }else{
            if(isset($fundingStatus['ftr_status']) && $fundingStatus['ftr_status'] != 'Y'){
                $button = $buttonDisabled;
            }elseif($accountDetails['next_role'] < 5 && $fundingStatus['ftr_status'] == 'Y'){
                $button = $buttonMarkForm;
            }else{
                $button = $buttonDone;
            }
        }
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
                        Mark Form For QC
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