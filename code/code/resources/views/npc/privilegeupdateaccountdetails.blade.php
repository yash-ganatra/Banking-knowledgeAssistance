@php
    $created_at = Carbon\Carbon::now();
    $duration = '';
    $tat = '';
    $creationStarted = FALSE;
    $submitted = FALSE;
    $currStatus = '';
@endphp

<style>
    .content-blck-tl { vertical-align: top; margin-top: 5px; color: #737272;}
    .step-9 .fa-genderless:before{content:'9'}
    .step-10 .fa-genderless:before{content:'10'}
    .step-11 .fa-genderless:before{content:'11'}
    .step-12 .fa-genderless:before{content:'12'}
    .step-13 .fa-genderless:before{content:'13'}
    .step-14 .fa-genderless:before{content:'14'}
    .step-15 .fa-genderless:before{content:'15'}
</style>

<div class="timeline-item-heading">
    <div class="lable-heading content-blck-1 content-blck-update-privileges-1">ACTIVITY</div>
    <div class="lable-heading content-blck-2">STATUS</div>
    <!-- <div class="lable-heading content-blck-3">DATE</div> -->
    <div class="lable-heading content-blck-4 content-blck-update-privileges-4">UPDATE</div>
    <div class="lable-heading content-blck-5 content-blck-update-privileges-5">COMMENTS</div>
    <div class="lable-heading content-blck-6 content-blck-update-privileges-6 "  style="text-align: center;">ACTION</div>
</div>
    @php
        $customerDetails = (array) $customerDetails;
        $accountId = '';
        $customerId = '';
        $query_id = '';
        $dedupe_status = '';	
        $form_update_status = ''
        @endphp
    @if($customerDetails['id'] != '')
        @php
            $accountId = $customerDetails['id'];
            $query_id = $customerDetails['query_id'];
            $dedupe_status = $customerDetails['dedupe_status'];
        @endphp
    @endif
    @if($customerDetails['customer_id'] != '')
        @php
            $customerId = $customerDetails['customer_id'];
        @endphp
    @endif
    @if($application_status != '')
        @php
            $form_update_status = $application_status;
        @endphp
    @endif

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
                    <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl px-3">
                        Query Id
                    </div>
                    <div class="content-blck-2 content-blck-tl px-2">
                    @if(count($checkAccountHolder)>0)
                       @php
                        $applicantSeq = 1;
                      @endphp
                        @foreach($checkAccountHolder as $AccountHolder)
                            @if($AccountHolder->query_id == '')
                               <!--  <span>&#10003;</span>{{$query_id}} -->
                                Applicant{{$applicantSeq}}&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>
                            @else
                                Applicant{{$applicantSeq}}&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                            @endif
                            @php
                        $applicantSeq++;
                        @endphp
                        @endforeach
                    @endif
                    </div>
                   <!--  <div class="content-blck-3 content-blck-tl">
                        -
                    </div> -->
                    <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                        <!--Query ID -->
                    </div>
                    <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                        -
                    </div>
                    <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $query_id = $AccountHolder->query_id;
                            @endphp
                    @endforeach
                        @if($query_id != '' || !$ddQIDButtonReqd)
                            <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                        @else
                            <button class="btn generate_button" id="query_generate_id" formId="{{$formId}}" style="height:2.8rem; width:5.5rem">Generate</button>
                        @endif
                    </div>
                </div>
                <!--end::Text-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Item-->
    </div>
    <div class="timeline timeline-5 accountdetails">
        <!--begin::Item-->
        <div class="timeline-item align-items-start text-muted">
            <!--begin::Badge-->
            <div class="timeline-badge ">
                <i class="fa fa-genderless text-grey icon-xl"></i>
            </div>
            <!--end::Badge-->
            <!--begin::Content-->
            <div class="timeline-content d-flex">
                <!--begin::Text-->
                <div class="font-weight-mormal font-size-lg timeline-content pl-3">
                    <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl px-3">
                        Dedupe Status
                    </div>
                    <div class="content-blck-2 content-blck-tl px-2">
                    @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence
                            @endphp

                            @if($AccountHolder->dedupe_status == 'Dedupe Api has Failed')
                                Applicant{{$applicantSeq}}&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>
                            @elseif($AccountHolder->dedupe_status == 'No Match')
                                Applicant{{$applicantSeq}}&nbsp;<span>(No Match)</span>&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                            @elseif($AccountHolder->dedupe_status == 'Pending')
                                 Applicant{{$applicantSeq}}&nbsp;<span>(Pending)</span>&nbsp;&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>
                            @else
                               Applicant{{$applicantSeq}}&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>
                            @endif
                        @endforeach
                    @endif
                    </div>
                    <!-- <div class="content-blck-3 content-blck-tl">
                        -
                    </div> -->
                    <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                        @if(count($checkAccountHolder)>0)
                            @foreach($checkAccountHolder as $AccountHolder)
                                @php
                                   $applicantSeq = $AccountHolder->applicant_sequence
                                @endphp

                                @if($AccountHolder->dedupe_status != 'No Match')
                                  <input type="text" class="form-control input-capitalize update_dedupestatus mt-1"  id="update_dedupestatus-{{$applicantSeq}}" name="{{$applicantSeq}}" value="" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Dedupe Status" maxlength="8">
                                @endif
                            @endforeach
                        @endif
                    </div>
                    <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence
                            @endphp
                             @if($AccountHolder->dedupe_status != 'No Match')
                               <textarea class="form-control rounded-0 dedupe_comment mt-1" id="dedupe_comment-{{$applicantSeq}}" name="{{$applicantSeq}}" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Dedupe Comments"></textarea>
                            @endif
                        @endforeach
                    </div>
                    <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $query_id = $AccountHolder->query_id;
                            @endphp
                        @endforeach
        
					@if($query_id == '' || 1 == 2)  
                            <button class="btn disabled_button" disabled="">Update</button>
                        @elseif($dedupe_status == '')
                            <button class="btn update_button"  id="check_dedupe_status" formId="{{$formId}}">Update</button>
                        @elseif($dedupe_status == 'Pending')
                            <button class="btn update_button"  id="check_dedupe_status" formId="{{$formId}}">Update</button>
                        @elseif($dedupe_status == 'Dedupe Api has Failed')
                            <button class="btn update_button"  id="check_dedupe_status" formId="{{$formId}}">Update</button>
					@else 
							@if($ddStatusButtonReqd)
								<button class="btn update_button"  id="check_dedupe_status" formId="{{$formId}}">Update</button>
							@else
								<button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
							@endif
					@endif 
                    </div>
                </div>
                <!--end::Text-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Item-->
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
                    <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl px-3">
                        Customer Id
                    </div>
                    <div class="content-blck-2 content-blck-tl px-2">
                    @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence
                            @endphp
                            @if($AccountHolder->customer_id != '')
                                Applicant{{$applicantSeq}} ({{$custIds[$applicantSeq-1]}})&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                            @else
                                Applicant{{$applicantSeq}}&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>
                            @endif
                        @endforeach
                    @endif
                    </div>
                    <!-- <div class="content-blck-3 content-blck-tl">
                        -
                    </div> -->
                    <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">

                    @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence
                            @endphp
                            @if($AccountHolder->customer_id != '')
                                
                            @else
                               @if($AccountHolder->customer_id == '')
                                <input type="text" class="form-control input-capitalize customer_id mt-1"  id="customer_id-{{$applicantSeq}}" name="{{$applicantSeq}}" value="" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^0-9 ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Customer ID" maxlength="9">
                               @endif
                            @endif
                        @endforeach
                    @endif

                        
                    </div>
                    <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence
                            @endphp
                            @if($AccountHolder->customer_id == '')
                               <textarea class="form-control rounded-0 customer_id_comment mt-1" id="customer_id_comment-{{$applicantSeq}}" name={{$applicantSeq}} rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Customer ID Comments"></textarea>
                            @endif
                        @endforeach

                    </div>

                    <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $dedupe_status = $AccountHolder->dedupe_status;
                            @endphp
                    @endforeach						
                       @if($dedupe_status == '' || $dedupe_status == 'Dedupe Api has Failed' || $dedupe_status == 'Pending' || $ddStatusButtonReqd) 
                            <button class="btn disabled_button" disabled="">Update</button>
                        @elseif($customerId != '' && $custIdButtonReqd == false )
                            <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                        @else							
							<button class="btn create_button" id="update_customer_id" formId="{{$formId}}">Update</button>							
                        @endif
                    </div>
                </div>
                <!--end::Text-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Item-->
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
                <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl px-3">
                    Update Funding
                </div>
                <div class="content-blck-2 content-blck-tl px-2">
                    @if($fundingStatus['funding_status'] == 'Y')
                        Cleared&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                    @else
                        <i class="fa fa-times not_done" aria-hidden="true"></i>
                    @endif
                </div>
                <!-- <div class="content-blck-3 content-blck-tl">
                    -
                </div> -->
                <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                    @if($fundingStatus['funding_status'] != 'Y')
                    <input type="text" class="form-control input-capitalize"  id="update_funding" name="update_funding" value="Y" onkeyup="this.value = this.value.toUpperCase();" placeholder="Funding" readonly="readonly">
                    @endif
                </div>
                <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                    @if($fundingStatus['funding_status'] != 'Y')
                      <textarea class="form-control rounded-0" id="funding_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Funding Comments"></textarea>
                    @endif
                </div>
                <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $customerId = $AccountHolder->customer_id;
                            @endphp
                    @endforeach
                    @if($customerId == '')
                        <button class="btn disabled_button" disabled="">Update</button>
                    @elseif($fundingStatus['funding_status'] == 'Y')
                        <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                    @else
                        <button class="btn check_button" id="update_funding_status" formId="{{$formId}}">Update</button>
                    @endif
                </div>
            </div>
            <!--end::Text-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Item-->
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
                <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl px-3">
                @if((isset($accountDetails['account_type'])) && ($accountDetails['account_type'] == 3))
                    TD Account 
                @else
                    Account Id&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp;     
                @endif
                </div>
                <div class="content-blck-2 content-blck-tl px-2">
                    @if($accountDetails['account_type'] == 3)
                        @if($accountDetails['td_account_no'] != '' )
                            {{$accountDetails['td_account_no']}}&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-times not_done" aria-hidden="true"></i>
                        @endif
                    @elseif($accountDetails['account_type'] == 4)
                        @if((isset($accountDetails['account_no'])) && ($accountDetails['account_no'] != ''))
                            SA :{{$accountDetails['account_no']}}&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i><br>
                            @if((isset($accountDetails['td_account_no'])) && ($accountDetails['td_account_no'] != ''))
                            TD :{{$accountDetails['td_account_no']}}&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                            @else
                             TD :&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>
                            @endif
                        @else
                           
                        @endif
                    @elseif($accountDetails['account_type'] == 2)
                            @if($entityAccountNumber['entity_account_no'] != '')
                                {{$entityAccountNumber['entity_account_no']}}&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                            @else
                            <i class="fa fa-times not_done" aria-hidden="true"></i>
                        @endif
                    @else
                        @if((isset($accountDetails['account_no'])) && ($accountDetails['account_no'] != ''))
                            {{$accountDetails['account_no']}}&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-times not_done" aria-hidden="true"></i>
                        @endif
                    @endif
                </div>
               <!--  <div class="content-blck-3 content-blck-tl">
                    -
                </div> -->
                <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                @if($accountDetails['account_type'] == 3)
                    @if($accountDetails['td_account_no'] == "")
                     <input type="text" class="form-control input-capitalize"  id="td_update_accountno" name="update_accountno" value="" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Account No" maxlength="14">
                    @endif
                @elseif($accountDetails['account_type'] == 4)
                    @if($accountDetails['account_no'] == "")
                     <input type="text" class="form-control input-capitalize"  id="sa_update_accountno" name="update_accountno" value="" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="SA Account No" maxlength="14">
                    @endif
                    @if($accountDetails['td_account_no'] == "")
                     <input type="text" class="form-control input-capitalize"  id="td_update_accountno" name="update_accountno" value="" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="TD Account No" maxlength="14">
                    @endif
                @elseif($accountDetails['account_type'] == 2)
                    @if($entityAccountNumber['entity_account_no'] == '')
                        <input type="text" class="form-control input-capitalize"  id="sa_update_accountno" name="update_accountno" value="" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="CA Account No" maxlength="14">
                    @endif
                @else
                    @if($accountDetails['account_no'] == "")
                     <input type="text" class="form-control input-capitalize"  id="sa_update_accountno" name="update_accountno" value="" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Account No" maxlength="14">
                    @endif
                @endif

                </div>
                <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                   @if($accountDetails['account_type'] == 3)
                       @if($accountDetails['td_account_no'] == "")
                         <textarea class="form-control rounded-0" id="account_id_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Account ID Comments"></textarea>
                       @endif
                   @elseif($accountDetails['account_type'] == 2)
                        @if($entityAccountNumber['entity_account_no'] == '')
                        <textarea class="form-control rounded-0" id="account_id_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Account ID Comments"></textarea>
                        @endif
                   @else
                       @if($accountDetails['account_no'] == "")
                         <textarea class="form-control rounded-0" id="account_id_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Account ID Comments"></textarea>
                       @endif
                   @endif
                </div>
                <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    @if($accountDetails['account_type'] == 3)
                        <!-- @if($accountDetails['td_account_no'] != '')
                            <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                        @else
                            <button class="btn create_button" id="update_account_no" formId="{{$formId}}" accounttype={{$accountDetails['account_type']}}>Update</button>
                        @endif -->
                         @if($fundingStatus['funding_status'] != 'Y')
                                  <button class="btn disabled_button" disabled="">Check</button>
                             @else
                                @if($accountDetails['td_account_no'] != '')
                                   <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                                @else
                                <button class="btn create_button" id="update_account_no" formId="{{$formId}}" accounttype={{$accountDetails['account_type']}}>Update</button>
                                @endif
                             @endif
                    @elseif($accountDetails['account_type'] == 4)

                        
                             @if($fundingStatus['funding_status'] != 'Y')
                                  <button class="btn disabled_button" disabled="">Check</button>
                             @else
                                @if(($accountDetails['account_no'] != '') && ($accountDetails['td_account_no'] != ''))
                                   <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                                @else
                                <button class="btn create_button" id="update_account_no" formId="{{$formId}}" accounttype={{$accountDetails['account_type']}}>Update</button>
                                @endif
                             @endif
                    @elseif($accountDetails['account_type'] == 2)
                            @if($fundingStatus['funding_status'] != 'Y')
                                  <button class="btn disabled_button" disabled="">Check</button>
                    @else
                                @if($entityAccountNumber['entity_account_no'] != '')
                                   <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                                @else
                                <button class="btn create_button" id="update_account_no" formId="{{$formId}}" accounttype={{$accountDetails['account_type']}}>Update</button>
                                @endif
                             @endif
                    @else
                        @if($fundingStatus['funding_status'] != 'Y')
                            <button class="btn disabled_button" disabled="">Check</button>
                        @elseif((isset($accountDetails['account_no'])) && ($accountDetails['account_no'] != ''))
                            <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                        @else
                            <button class="btn create_button" id="update_account_no" formId="{{$formId}}" accounttype={{$accountDetails['account_type']}}>Update</button>
                        @endif
                    @endif
                </div>
            </div>
            <!--end::Text-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Item-->
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
                <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl px-3">
               
                      FTR
                
                </div>
                <div class="content-blck-2 content-blck-tl px-2">
                   
                        @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] == 'Y') && ($accountDetails['fund_transfer_status'] == 1))
                            {{$fundingStatus['ftr_reference_no']}}&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-times not_done" aria-hidden="true"></i>
                        @endif
               
                </div>
                <!-- <div class="content-blck-3 content-blck-tl">
                    -
                </div> -->
                <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                    @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] == 'N') && ($accountDetails['fund_transfer_status'] != 1))
                       <input type="text" class="form-control input-capitalize"  id="ftr_fundtransfer" name="ftr_fundtransfer" value="Y" onkeyup="this.value = this.value.toUpperCase();" placeholder="Fund transfer" readonly="readonly">
                    @endif
                </div>
                <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                    @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] == 'N') && ($accountDetails['fund_transfer_status'] != 1))
                     <textarea class="form-control rounded-0" id="ftr_status_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="FTR Comments"></textarea>
                    @endif
                </div>
                <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                
            
                    @if($accountDetails['account_type'] == 3)
                        @if($accountDetails['td_account_no'] == '')
                             <button class="btn disabled_button" disabled="">Update</button>
                        @else
                            @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] != 'N') && ($accountDetails['fund_transfer_status'] == 1))
                               <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                            @else
                            <button class="btn trasnfer_button" id="update_ftr_fund_transfer" formId="{{$formId}}">Update</button>
                            @endif
                       @endif
                    @elseif($accountDetails['account_type'] == 4)
                       @if(($accountDetails['td_account_no'] == '') || ($accountDetails['account_no'] == ''))
                             <button class="btn disabled_button" disabled="">Update</button>
                        @else
                            @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] != 'N') && ($accountDetails['fund_transfer_status'] == 1))
                               <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                            @else
                            <button class="btn trasnfer_button" id="update_ftr_fund_transfer" formId="{{$formId}}">Update</button>
                            @endif
                       @endif

                    @elseif($accountDetails['account_type'] == 2)
                        @if($entityAccountNumber['entity_account_no'] == '')
                            <button class="btn disabled_button" disabled="">Update</button>
                    @else
                            @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] != 'N') && ($accountDetails['fund_transfer_status'] == 1))
                               <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                            @else
                                <button class="btn trasnfer_button" id="update_ftr_fund_transfer" formId="{{$formId}}">Update</button>
                            @endif
                        @endif
                    @else
                        @if($accountDetails['account_no'] == '')
                           <button class="btn disabled_button" disabled="">Update</button>
                        @else
                           @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] != 'N') && ($accountDetails['fund_transfer_status'] == 1))
                               <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                            @else
                         <button class="btn trasnfer_button" id="update_ftr_fund_transfer" formId="{{$formId}}">Update</button>
                            @endif

                        @endif
                    @endif
              
                     
                    
                </div>
            </div>
            <!--end::Text-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Item-->
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
                    <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl px-3">
                        Mark Form For next role
                    </div>
                    <div class="content-blck-2 content-blck-tl px-2">
                    @if((isset($accountDetails['account_type'])) && ($accountDetails['account_type'] == 3))
                        @if($accountDetails['td_account_no'] != '' && ($accountDetails['next_role'] != 1) && 
                        ($accountDetails['next_role'] > 5))
                           Form Marked&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @elseif($checktdexist['td_response'] != '')
                              Error: {{$checktdexist['td_response']}}<i class="fa fa-times not_done" aria-hidden="true"></i></i>
                        @else
                            Role: {{$accountDetails['next_role']}}<i class="fa fa-times not_done" aria-hidden="true"></i>
                        @endif
                    @else
                        @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] == 'Y') && ($accountDetails['fund_transfer_status'] == 1) && ($accountDetails['next_role'] != 1) && ($accountDetails['next_role'] != '') && ($accountDetails['next_role'] > 5) )
                            Form Marked &nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @else
                            Role: {{$accountDetails['next_role']}}&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @endif
                    @endif                        
                    </div>
                    <!-- <div class="content-blck-3 content-blck-tl">
                        -
                    </div> -->
                    <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                        @if(($accountDetails['next_role'] == '1') || ($accountDetails['next_role'] == '') || ($accountDetails['next_role'] < 5))
                           {!! Form::select('updatenextrole',$updatenextrole,null,array('class'=>'form-control updatenextrole','id'=>'updatenextrole','name'=>'updatenextrole','placeholder'=>'Select Next Role')) !!}
                        @endif
                    </div>
                    <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                        @if(($accountDetails['next_role'] == '1') || ($accountDetails['next_role'] == '') || ($accountDetails['next_role'] < 5))
                          <textarea class="form-control rounded-0" id="next_role_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Next Role Comments"></textarea>
                        @endif
                    </div>
                    <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    

              <!--       @if($accountDetails['account_type'] == 3)
                      @if(($fundingStatus['ftr_status'] != 'Y') && ($accountDetails['fund_transfer_status'] != 1))
                               <button class="btn disabled_button" disabled="">Mark</button>
                        @else
                            @if(($fundingStatus['ftr_status'] == 'Y') && ($accountDetails['fund_transfer_status'] == 1))
                                <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                            @else
                             <button class="btn btn-primary form_mark_button" id="update_next_role" formId="{{$formId}}">Update</button>
                            @endif
                        @endif
                    @else

                        @if((isset($fundingStatus['ftr_status'])) && ($fundingStatus['ftr_status'] == 'Y') && ($accountDetails['fund_transfer_status'] == 1) && ($accountDetails['next_role'] == '5' || $accountDetails['next_role'] == '4'))
                            <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                        @else
                            @if(($fundingStatus['ftr_status'] != 'Y') && ($accountDetails['fund_transfer_status'] != 1))
                               <button class="btn disabled_button" disabled="">Mark</button>
                            @else
                             <button class="btn btn-primary form_mark_button" id="update_next_role" formId="{{$formId}}">Update</button>
                            @endif
                        @endif
                    @endif -->

                      <button class="btn btn-primary form_mark_button" id="update_next_role" formId="{{$formId}}">Update</button>
                     
                    </div>
                </div>
                <!--end::Text-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Item-->
</div>

@foreach($checkAccountHolder as $AccountHolder)
        @php
           $customerId = $AccountHolder->customer_id;
        @endphp
@endforeach
@php
    
    if($customerDetails['is_new_customer'] == 0 && $accountDetails['l2_cleared_status'] == 0 && $fundingStatus['funding_type'] == 2){
        $allowETBreuseNEFT = true;
    }else{
        $allowETBreuseNEFT = false;
    }

    if(strtoupper($accountDetails['flow_type']) == 'HYBRID' && $accountDetails['l2_cleared_status'] == 0 && $fundingStatus['funding_type'] == 2){
        $allowETBreuseNEFT = true;
    }
    
@endphp

@if (($fundingStatus['funding_type'] == 2 && $customerId == '') || $allowETBreuseNEFT == true)
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
                        <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl">
                            Cancel NEFT Record
                        </div>
                        <div class="content-blck-2 content-blck-tl">
                            @if($fundingStatus['abort'] == '')
                                Canceled &nbsp;&nbsp;&nbsp;<i class="fa fa-times not_done" aria-hidden="true"></i>
                            @else
                                Canceled &nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                            @endif                        
                        </div>
                        <!-- <div class="content-blck-3 content-blck-tl">
                            -
                        </div> -->
                         <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                            @if($fundingStatus['abort'] == '')
                            <input type="text" class="form-control input-capitalize"  id="update_abort_form" name="update_abort_form" value="Y" onkeyup="this.value = this.value.toUpperCase();" placeholder="Abort" readonly="readonly">
                            @endif
                        </div>
                        <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                            @if($fundingStatus['abort'] == '')
                              <textarea class="form-control rounded-0" id="form_abort_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="NEFT cancel Comments"></textarea>
                            @endif
                        </div>
                        <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                            @if($fundingStatus['abort'] == '')
                                <button class="btn check_button" id="abort_form" formId="{{$formId}}">Update</button>
                            @else
                                <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                            @endif
                        </div>
                    </div>
                    <!--end::Text-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Item-->
    </div>
@endif
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
                    <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl px-3">
                        Mark Form Reject
                    </div>
                                    <div class="content-blck-2 content-blck-tl px-2">
                    @if($form_update_status == '45')
                      Form  Rejected&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                    @else
                        <i class="fa fa-times not_done" aria-hidden="true"></i>
                    @endif
                </div>
                    <!-- <div class="content-blck-3 content-blck-tl">
                        -
                    </div> -->
                    
                        <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                    @if($form_update_status != '45')
                    <input type="text" class="form-control input-capitalize"  id="mark_reject" name="mark_reject" value="Reject" readonly="readonly">
                    @endif
                                 </div>
                <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                @if($form_update_status != '45')               
                  <textarea class="form-control rounded-0" id="reject_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Reject Comments"></textarea>
                   @endif 
                </div>

                <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                @if($form_update_status == '45')
                <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                @else
                    <button class="btn update_button"  id="reject_button" formId="{{$formId}}">Update</button>
                @endif
                </div>
            </div>
                
                <!--end::Text--> 
            </div>
            <!--end::Content-->
        </div>
        <!--end::Item-->
        @include('npc.privilegeupdatepost');
</div>
@push('scripts')

<script type="text/javascript">

    $(document).ready(function(){
       addSelect2('updatenextrole','Customer Name'); 
    });
</script>
@endpush
