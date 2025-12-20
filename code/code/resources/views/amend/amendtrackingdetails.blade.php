@php
    $created_at = Carbon\Carbon::now();
    $duration = '';
    $tat = '';
	$creationStarted = FALSE;
	$currStatus = '';
@endphp
<div class="timeline-item-heading">
    <div class="lable-heading content-blck-1">ACTIVITY</div>
    <div class="lable-heading content-blck-2">DATE</div>
    <div class="lable-heading content-blck-3">TIME</div>
    <div class="lable-heading content-blck-4">ACTIVITY TIME</div>
    <div class="lable-heading content-blck-6">CREATED BY</div>
</div>

<div class="timeline timeline-5 mt-3">
    <!--begin::Item-->
	
    @if(count($getAmendTrackingDetails) > 0)

        @for($i=0;$i<=count($getAmendTrackingDetails)-1;$i++)
            @php				
                $trackingData = (array) $getAmendTrackingDetails[$i];
                $currStatus = $trackingData['status'];

				if($currStatus == "CRF Approved"){
					if(!$creationStarted){
						$creationStarted = TRUE;
					}else{
						$currStatus = "CRF Modified";	
					}
				}	
            @endphp
            <div class="timeline-item align-items-start">
                @if($i != 0)
                    @php
                        $startTime = Carbon\Carbon::parse($getAmendTrackingDetails[$i]->created_at);
                        $finishTime = Carbon\Carbon::parse($getAmendTrackingDetails[$i-1]->created_at);
                        $totalDuration = $finishTime->diff($startTime)->format('%H:%I:%S');
                        $created_at = $getAmendTrackingDetails[$i]->created_at;
                        $duration = $finishTime->diffInMinutes($startTime);
                        $tat = $getAmendTrackingDetails[$i-1]->tat;
                    @endphp
                @endif                                        
                <!--begin::Badge-->
                <div class="timeline-badge">
                    @if($duration > $tat)
                        <i class="fa fa-genderless text-warning icon-xl"></i>
                    @else
                        <i class="fa fa-genderless text-success icon-xl"></i>
                    @endif
                </div>
                <!--end::Badge-->                
                <!--begin::Text-->
                <div class="font-weight-mormal font-size-lg timeline-content pl-3">
                    <div class="content-blck-1 content-blck-tl">
                        {{$currStatus}}
                        <br>
                        @if($trackingData['comments'] != '')
                            (Comments: {{$trackingData['comments']}})
                        @endif
                    </div>
                    <div class="content-blck-2 content-blck-tl vertical_top">
                        {{Carbon\Carbon::parse($trackingData['created_at'])->format('d-M')}}
                    </div>
                    <div class="content-blck-3 content-blck-tl vertical_top">
                        {{Carbon\Carbon::parse($trackingData['created_at'])->format('h:i A')}}
                    </div>
                    <div class="content-blck-4 content-blck-tl vertical_top">
                        @if($i != 0)
                            {{$totalDuration}}
                        @else
                            -
                        @endif
                    </div>
                    <div class="content-blck-6 content-blck-tl vertical_top">
                        
                    {{isset($trackingData['created_by']->emp_first_name) && $trackingData['created_by']->emp_first_name != ''?$trackingData['created_by']->emp_first_name:''}}

                    </div>
                </div>                    
                <!--end::Text-->
            </div>
        @endfor
    @else
        <center><label>No Data Found</label></center>
    @endif
    <!--end::Item-->

</div>
