@php
$comment='';
$iconNotDone='&nbsp;&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>';
$iconDone='&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>';

$button='';
$buttonDisable='<button class="btn disabled_button" disabled="">Create</button>';
$buttonDoneTxt='   <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>';
$buttonCheck='<button class="btn check_button" id="check_funding_status" formId="'.$formId.'">Check</button>';

    if($fundingStatus['funding_status'] == 'Y'){
        $comment .='Cleared'.$iconDone;
    }else{
        $comment .='Cleared'.$iconNotDone;
    }

    if($custIdButtonReqd){
        $button=$buttonDisable;
    }elseif($fundingStatus['funding_status'] != 'Y'){
        $button=$buttonCheck;
    }else{
        $button=$buttonDoneTxt;
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
                    Check Funding
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