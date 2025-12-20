@php
    $created_at = Carbon\Carbon::now();
    $duration = '';
    $tat = '';
	$creationStarted = FALSE;
	$submitted = FALSE;
	$currStatus = '';
@endphp
<div class="timeline-item-heading">
    <div class="lable-heading content-blck-1">ACTIVITY</div>
    <div class="lable-heading content-blck-2">DATE</div>
    <div class="lable-heading content-blck-3">TIME</div>
    <div class="lable-heading content-blck-4">ACTIVITY TIME</div>
   {{--  <div class="lable-heading content-blck-5">PROCESS TAT</div> --}}
    <div class="lable-heading content-blck-6">CREATED BY</div>
</div>

<div class="timeline timeline-5 mt-3">
    <!--begin::Item-->
	
    @if(count($trackingDetails) > 0)
		
        @for($i=0;$i<=count($trackingDetails)-1;$i++)
            @php				
                $trackingData = (array) $trackingDetails[$i];
				$currStatus = $trackingData['status'];
				if($currStatus == "AOF Creation Process Started"){
					if(!$creationStarted){
						$creationStarted = TRUE;
					}else{
						$currStatus = "AOF Modified";	
					}
				}	
            @endphp
            <div class="timeline-item align-items-start">
                @if($i != 0)
                    @php
                        $startTime = Carbon\Carbon::parse($trackingDetails[$i]->created_at);
                        $finishTime = Carbon\Carbon::parse($trackingDetails[$i-1]->created_at);
                        $totalDuration = $finishTime->diff($startTime)->format('%H:%I:%S');
                        $created_at = $trackingDetails[$i]->created_at;
                        $duration = $finishTime->diffInMinutes($startTime);
                        $tat = $trackingDetails[$i-1]->process_tat;
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
                    @if($customerType == 'ETB')
                        @if(($trackingDetails[$i]->comments == "auto_approval") && (($currStatus == "L1 Cleared") || (($currStatus == "L2 Cleared"))))
                            {{$currStatus.' (Auto Approved)'}}
                        @elseif($currStatus == "Customer ID created")
                             @if(count($ovd)>0)
                                @php
                                  $applicantSeq = 1;
                                @endphp
                               @foreach($ovd as $ovddetails)
                                    <!-- {{$currStatus.' (Customer ID Created: '.$ovddetails->customer_id.')'}} -->
                                 @if($applicantSeq > 1 )
                                    <p></p>   
                                 @endif
                                    Applicant{{$applicantSeq}} Customer ID : {{$ovddetails->customer_id}}
                                    @php
                                       $applicantSeq++;
                                    @endphp
                                @endforeach
                             @endif
                        @else
                            @if($currStatus == "Account created")
                                
                                @if($accountdetails['account_type'] == 2)
                                    {{$currStatus.' (CA Account No: '.$trackingStatus['entity_account_number'].')'}}
                                    @if($trackingStatus['account_no'] != '')
                                        <br>
                                        {{'(SA Account No:'.$trackingStatus['account_no'].')'}}
                                    @endif
                                @else
                                    @if($trackingStatus['account_no'] != '')
                                {{$currStatus.' (Account No: '.$trackingStatus['account_no'].')'}}
                            @else
                                        {{$currStatus.' (Account No: '.$trackingStatus['td_account_no'].')'}}
                                    @endif
                                @endif
                            @else
                                {{$currStatus}}
                            @endif
                        @endif
                    @else
                         
                        @if($currStatus == "Customer ID created")
                          @if(count($ovd)>0)
                                @php
                                  $applicantSeq = 1;
                                @endphp
                               @foreach($ovd as $ovddetails)
                                    <!-- {{$currStatus.' (Customer ID Created: '.$ovddetails->customer_id.')'}} -->
                                 @if($applicantSeq > 1 )
                                    <p></p>   
                                 @endif
                                    {{$currStatus}} <br>(Applicant{{$applicantSeq}} : {{$ovddetails->customer_id}})
                                    @php
                                       $applicantSeq++;
                                    @endphp
                                @endforeach
                             @endif
                        @else
                          {{$currStatus}}
                        @endif
                        
                        @if($currStatus == "Account created")

                               @if($accountdetails['account_type'] == 1)
                               <br>
                                (Account No: {{$trackingStatus['account_no']}})
                                @elseif($accountdetails['account_type'] == 2)
                               <br>
                                    @if($trackingStatus['account_no'] !='')
                                  SA :(Account No: {{$trackingStatus['account_no']}})
                                    @endif
                               <br>
                                  
                                  CA :(Account No: {{$trackingStatus['entity_account_number']}})
                               @elseif($accountdetails['account_type'] == 3)
                               <br>
                                  (Account No: {{$trackingStatus['td_account_no']}})
                               @elseif($accountdetails['account_type'] == 4)
                               <br>
                                  SA :(Account No: {{$trackingStatus['account_no']}})
                                  TD :(Account No: {{$trackingStatus['td_account_no']}})
                               @else
                               <br>
                                  (Account No: {{$trackingStatus['account_no']}})
                            @endif
                        @endif
                    @endif
                    @if(substr($currStatus,-7)== 'ejected')
                    <br>
                        (Comments: {{$trackingData['comments']}})
                    @endif
                    @if(substr($currStatus,-3)== 'old')
                    <br>
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
                   {{--  <div class="content-blck-5 content-blck-tl ">
                        {{$trackingData['process_tat']}} minutes
                    </div> --}}
                    <div class="content-blck-6 content-blck-tl vertical_top">
                        {{$trackingData['created_by']}}
                    </div>
                </div>                    
                <!--end::Text-->
            </div>
        @endfor
    @endif
    <!--end::Item-->
    @if(count($flowStatusDetails) > 0)
        @php
            $notice = 15;
        @endphp
        @foreach($flowStatusDetails as $id=>$status)
            <div class="timeline-item align-items-start text-muted">
                <!--begin::Badge-->
                <div class="timeline-badge">
                    <i class="fa fa-genderless  text-grey icon-xl"></i>
                </div>
                <!--end::Badge-->
                <!--begin::Content-->
                <div class="timeline-content d-flex">
                    <!--begin::Text-->
                    <div class="font-weight-mormal font-size-lg timeline-content pl-3">
                        <div class="content-blck-1 content-blck-tl">
                            {{$status}} Pending
                        </div>
                        <div class="content-blck-2 content-blck-tl">
                            {{Carbon\Carbon::parse($trackingData['created_at'])->addMinutes($notice)->format('d-M')}}
                        </div>
                        <div class="content-blck-3 content-blck-tl">
                            {{Carbon\Carbon::parse($trackingData['created_at'])->addMinutes($notice)->format('h:i A')}}
                        </div>
                        <div class="content-blck-4 content-blck-tl">
                            -
                        </div>
                       {{--  <div class="content-blck-5 content-blck-tl">
                            {{$trackingData['process_tat']}} minutes
                        </div> --}}
                        <div class="content-blck-6 content-blck-tl">
                            -
                        </div>
                    </div>
                    <!--end::Text-->
                </div>
                <!--end::Content-->
            </div>
            @php
                $notice = $notice + 15;
            @endphp
        @endforeach
    @endif
</div>

<input type='hidden' id='formId_forCopy' name="formId_forCopy" value="{{$accountdetails['id']}}">