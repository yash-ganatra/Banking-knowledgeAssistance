@php
$comment='';
$clearText = '&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i><br>';
$notDone = '&nbsp;&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i><br>';
$button='';
$buttonDisable='<button type="button" class="btn disabled_button">Create</button>';
$buttonDoneTxt='<button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>';
$buttonCreateTXT='<button class="btn create_button" id="create_customer_id" formId="'.$formId.'">Create</button>';

$customerId = $customerDetails['customer_id'];
    if(count($checkAccountHolder)>0){
        $applicantSeq = 1;
        foreach($checkAccountHolder as $AccountHolder){
            if($AccountHolder->customer_id != ''){
               $comment .= 'Applicant'.$applicantSeq.' '.$custIds[$applicantSeq-1].$clearText;
            }else{
               $comment .= 'Applicant'.$applicantSeq.' '.$notDone;
            }
            $applicantSeq++;
            }
        }   

    if($ddStatusButtonReqd){
       $button = $buttonDisable;
    }elseif($custIdButtonReqd) {
        $button = $buttonCreateTXT;
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
                        Create Customer Id
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

 