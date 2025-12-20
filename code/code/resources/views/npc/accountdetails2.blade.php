@php
$dedupe_status = $customerDetails['dedupe_status'];
$scheme_code = $accountDetails['scheme_code'];

$is_delight = $accountDetails['delight_scheme'] == 5 ? true : false;

$comment='';
$clearText = '&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i><br>';
$notDone = '&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i><br>';

$button='';
$buttonDoneTxt=' <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>';
$buttonDisable='<button class="btn disabled_button" disabled="">Create</button>';
$buttonUpdateTxt=' <button class="btn update_button"  id="check_dedupe_status" formId="'.$formId.'">Update</button>';


if(count($checkAccountHolder)>0){
    $applicantSeq=1;
    foreach($checkAccountHolder as $AccountHolder){
        if(!($is_delight))
        {
            if($AccountHolder->dedupe_status == 'Dedupe Api has Failed'){
                $comment.='Applicant'.$applicantSeq.$notDone;

            }elseif($AccountHolder->dedupe_status == 'No Match'){
                $comment.='Applicant'.$applicantSeq.$clearText;

            }elseif($AccountHolder->dedupe_status == 'Pending'){
                $comment.='Applicant'.$applicantSeq.$notDone;

            }else{
                $comment.='Applicant'.$applicantSeq.$notDone;
            }
        }else{
            $comment='Delight: Not Applicable';
        }
        $applicantSeq++; 
    }
}


if ($ddQIDButtonReqd) {
    $button = $buttonDisable;
}elseif($ddStatusButtonReqd){
    $button=$buttonUpdateTxt;
}else{
    $button=$buttonDoneTxt;
}
@endphp

<div class="timeline timeline-5 accountdetails">
    @if(!$callcenter)
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
                        Check Dedupe Status
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
     @endif
    </div>

