@php
$comment='';
$clearText = '&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i><br>';
$notDone = '&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i><br>';
$button='';
$disbalebuttonGenerateTxt='<button type="button" class="btn disabled_button" disabled="">Generate</button>';
$buttonGenerateTxt= '<button class="btn generate_button" id="query_generate_id" formId="'.$formId.'" style="height:2.8rem; width:5.5rem">Generate</button>';
$buttonDoneTxt='<button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>';

    if(count($checkAccountHolder) > 0){
        $applicantSeq = 1;
        foreach($checkAccountHolder as $AccountHolder){
            if($AccountHolder->query_id == ''){
                $comment .= 'Applicant'.$applicantSeq.$notDone;
            }else{
                $comment .='Applicant'.$applicantSeq.$clearText;
            }
            $applicantSeq++;
        }
    }
    
    if(!$ddQIDButtonReqd){
        $button=$buttonDoneTxt;
    }else{
        $button=$buttonGenerateTxt;
    }
                        

@endphp

<script type="text/javascript">
        for(i=0; i<$('.timeline-badge').length; i++){
            $($('.timeline-badge')[i]).addClass('step-'+(i+1))
        }
</script>
<style>.content-blck-tl { vertical-align: top; margin-top: 5px; color: #737272;}</style>

<div class="timeline-item-heading">
    <div class="lable-heading content-blck-1">ACTIVITY</div>
    <div class="lable-heading content-blck-2">COMMENTS</div>
    <div class="lable-heading content-blck-3">DATE</div>
    <div class="lable-heading content-blck-4">TIME</div>
    <div class="lable-heading content-blck-5">CREATED BY</div>
    <div class="lable-heading content-blck-6"  style="text-align: center;">ACTION</div>
</div>
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
                        Generate Query Id
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
@include('npc.accountdetails2')
@include('npc.accountdetails3')
@include('npc.accountdetails4')
@include('npc.accountdetails5')
@include('npc.accountdetails6')
@include('npc.accountdetails7')

