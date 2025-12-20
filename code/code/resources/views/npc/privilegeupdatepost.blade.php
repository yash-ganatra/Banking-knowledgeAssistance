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
               
                      SIGNATURE UPDATE
                
                </div>
                <div class="content-blck-2 content-blck-tl px-2">
                   
                        @if($accountDetails['signature_flag'] == 'Y')
                            Done&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-times not_done" aria-hidden="true"></i>
                        @endif
               
                </div>
                <!-- <div class="content-blck-3 content-blck-tl">
                    -
                </div> -->
                <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                     @if($accountDetails['signature_flag'] != 'Y')
                       <input type="text" class="form-control input-capitalize"  id="signature_flag" name="signature_flag" value="Y" onkeyup="this.value = this.value.toUpperCase();" placeholder="Signature Update" readonly="readonly">
                    @endif
                </div>
                <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                     @if($accountDetails['signature_flag'] != 'Y')
                     <textarea class="form-control rounded-0" id="signature_flag_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Signature Update Comments"></textarea>
                    @endif
                </div>
                <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    @if((isset($accountDetails['signature_flag'])) && ($accountDetails['signature_flag'] == 'Y'))
                       <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                    @else
                        <button class="btn trasnfer_button" id="update_signature_flag" data-value="api" formId="{{$formId}}">Update</button>
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
               
                      SIGNATURE FLAG UPDATE
                
                </div>
                <div class="content-blck-2 content-blck-tl px-2">
                   
                        @if($accountDetails['signature_flag'] == 'Y')
                            Done&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-times not_done" aria-hidden="true"></i>
                        @endif
               
                </div>
                <!-- <div class="content-blck-3 content-blck-tl">
                    -
                </div> -->
                <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                     @if($accountDetails['signature_flag'] != 'Y')
                       <input type="text" class="form-control input-capitalize"  id="signature_flag_manual" name="signature_flag" value="Y" onkeyup="this.value = this.value.toUpperCase();" placeholder="Signature Flag" readonly="readonly">
                    @endif
                </div>
                <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                     @if($accountDetails['signature_flag'] != 'Y')
                     <textarea class="form-control rounded-0" id="signature_flag_comment_manual" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Signature Flag Comments"></textarea>
                    @endif
                </div>
                <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    @if((isset($accountDetails['signature_flag'])) && ($accountDetails['signature_flag'] == 'Y'))
                       <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                    @else
                        <button class="btn trasnfer_button" id="update_signature_flag" data-value="manual" formId="{{$formId}}">Update</button>
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
               
                    SUBMIT CARD CHECK
                
                </div>
                <div class="content-blck-2 content-blck-tl px-2">
                        @if($accountDetails['card_flag'] == 'Y')
                            Done&nbsp;&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
                        @else
                            <i class="fa fa-times not_done" aria-hidden="true"></i>
                        @endif
               
                </div>
                <!-- <div class="content-blck-3 content-blck-tl">
                    -
                </div> -->
                <div class="content-blck-4 content-blck-update-privileges-4 content-blck-tl">
                     @if($accountDetails['card_flag'] != 'Y')
                       <input type="text" class="form-control input-capitalize"  id="card_flag" name="card_flag" value="Y" onkeyup="this.value = this.value.toUpperCase();" placeholder="Signature Update" readonly="readonly">
                    @endif
                </div>
                <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                     @if($accountDetails['card_flag'] != 'Y')
                     <textarea class="form-control rounded-0" id="card_flag_comment" rows="2" oninput="this.value = this.value.replace(/[^a-z0-9\-. ]/gi, '').replace(/(\..*)\./g, '$1');" placeholder="Card Flag Comments"></textarea>
                    @endif
                </div>
                <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                    @if((isset($accountDetails['card_flag'])) && ($accountDetails['card_flag'] == 'Y'))
                       <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                    @else
                        <button class="btn trasnfer_button" id="update_card_flag" formId="{{$formId}}">Update</button>
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
                    KYC FLAG UPDATE
                </div>
                    <div class="content-blck-2 content-blck-tl px-2">
                   @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence
                            @endphp
                            @if($AccountHolder->kyc_update != '')
                                Applicant{{$applicantSeq}} ({{$kycUpdates[$applicantSeq-1]}})&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
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
                               $applicantSeq = $AccountHolder->applicant_sequence;
                               $kycflagshoworhide = 'hide';
                            @endphp
                                @if($AccountHolder->kyc_update == null)
                                @php
                                    $kycflagshoworhide = 'show';
                                    break;
                                @endphp
                                @endif
                        @endforeach
                        @if($kycflagshoworhide == 'show')
                                <input type="text" class="form-control input-capitalize kyc_update_manual mt-1"  id="kyc_update-{{$applicantSeq}}" name="{{$applicantSeq}}" value="Y" readonly>
                       @endif
                    @else
                    @php
                        $applicantSeq = 1;
                    @endphp
                                <input type="text" class="form-control input-capitalize kyc_update_manual mt-1"  id="kyc_update-{{$applicantSeq}}" name="{{$applicantSeq}}" value="Y" readonly>
                    @endif
                </div>

                 <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">

                   @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence;
                               $kycflagshoworhide = 'hide';
                               $class = '';
                                
                            @endphp
                            
                                @if($AccountHolder->kyc_update != 'Y')
                                @php
                                    $kycflagshoworhide = 'show';
                                @endphp
                                @endif
                        @if($kycflagshoworhide == 'show')
                               <textarea class="form-control rounded-0 kyc_update_comment_manual mt-1 {{$class}}" id="kyc_update_comment-{{$applicantSeq}}" name={{$applicantSeq}} rows="2"  placeholder="Kyc Update Comments"></textarea>
                       @endif
                        @endforeach
                   
                    @endif
                </div>
              <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                        @if($customerDetails['kyc_update'] != '' && $kycUpdateButtonReqd == false)
                            <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                        @else                           
                            <button class="btn create_button" id="kyc_update" data-value="manual" formId="{{$formId}}">Update</button>                          
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
                    KYC UPDATE
                </div>
                <div class="content-blck-2 content-blck-tl px-2">
                   @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence
                            @endphp
                            @if($AccountHolder->kyc_update != '')
                                Applicant{{$applicantSeq}} ({{$kycUpdates[$applicantSeq-1]}})&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
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
                               $applicantSeq = $AccountHolder->applicant_sequence;
                               $kycflagshoworhide = 'hide';
                            @endphp
                            
                                @if($AccountHolder->kyc_update == null)
                                @php
                                    $kycflagshoworhide = 'show';
                                    break;
                                @endphp
                                @endif
                        @endforeach
                        @if($kycflagshoworhide == 'show')
                                <input type="text" class="form-control input-capitalize kyc_update_api mt-1"  id="kyc_update-{{$applicantSeq}}" name="{{$applicantSeq}}" value="Y" readonly>
                        @endif
                    @else
                        @php
                            $applicantSeq = 1;
                        @endphp
                                <input type="text" class="form-control input-capitalize kyc_update_api mt-1"  id="kyc_update-{{$applicantSeq}}" name="{{$applicantSeq}}" value="Y" readonly>
                    @endif
                </div>


                 <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                    @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence;
                               $kycflagshoworhide = 'hide';
                               $class = '';
                                
                            @endphp
                            
                                @if($AccountHolder->kyc_update != 'Y')
                                @php
                                    $kycflagshoworhide = 'show';
                                @endphp
                                @endif
                        @if($kycflagshoworhide == 'show')
                               <textarea class="form-control rounded-0 kyc_update_comment_api mt-1 {{$class}}" id="kyc_update_comment-{{$applicantSeq}}" name={{$applicantSeq}} rows="2"  placeholder="Kyc Update Comments"></textarea>
                       @endif
                        @endforeach
                   
                    @endif        
                 </div>
              <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                        @if($customerDetails['kyc_update'] != '' && $kycUpdateButtonReqd == false)
                            <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                        @else                           
                            <button class="btn create_button" id="kyc_update" data-value="api" formId="{{$formId}}">Update</button>                          
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
                    INTERNET BANK
                </div>
                <div class="content-blck-2 content-blck-tl px-3">
                   @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence
                            @endphp
                            @if($AccountHolder->internet_banking != '')
                                Applicant{{$applicantSeq}} ({{'Y'}})&nbsp;&nbsp;<i class="fa fa-check icon_clear" aria-hidden="true"></i>
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
                               $applicantSeq = $AccountHolder->applicant_sequence;
                               $ibshoworhide = 'hide';
                            @endphp
                            
                                @if($AccountHolder->internet_banking == null)
                                @php
                                    $ibshoworhide = 'show';
                                    break;
                                @endphp
                                @endif
                        @endforeach
                        @if($ibshoworhide == 'show')
                            <input type="text" class="form-control input-capitalize internet_bank_api mt-1"  id="internet_update-{{$applicantSeq}}" name="{{$applicantSeq}}" value="Y" readonly>
                       @endif
                    @else
                    @php
                        $applicantSeq = 1;
                    @endphp
                     <input type="text" class="form-control input-capitalize internet_bank_api mt-1"  id="internet_update-{{$applicantSeq}}" name="{{$applicantSeq}}" value="Y" readonly>
                @endif
                </div>
                                
                 <div class="content-blck-5 content-blck-update-privileges-5 content-blck-tl">
                     @if(count($checkAccountHolder)>0)
                        @foreach($checkAccountHolder as $AccountHolder)
                            @php
                               $applicantSeq = $AccountHolder->applicant_sequence;
                               $ibshoworhide = 'hide';
                            @endphp
                            
                                @if($AccountHolder->internet_banking == null)
                                @php
                                    $ibshoworhide = 'show';
                                    break;
                                @endphp
                                @endif
                        @endforeach
                            @if($ibshoworhide == 'show')
                               <textarea class="form-control rounded-0 internet_bank_comment_api mt-1" id="internet_bank_api_comment-{{$applicantSeq}}" name={{$applicantSeq}} rows="2"  placeholder="Internet Bank Comments"></textarea>

                            @endif
                    @else
                    @php
                        $applicantSeq = 1;
                    @endphp
                        <textarea class="form-control rounded-0 internet_bank_comment_api mt-1" id="internet_bank_api_comment-{{$applicantSeq}}" name={{$applicantSeq}} rows="2"  placeholder="Internet Bank Comments"></textarea>
                    @endif
                    </div>
              <div class="content-blck-6 content-blck-update-privileges-6 comments_blck_width content-blck-tl text-center font-weight-bold">
                        @if($customerDetails['internet_banking'] != '' && $internetBankUpdatesBtn == false)
                            <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button>
                        @else                           
                            <button class="btn create_button" id="internet_bank" data-value="api" formId="{{$formId}}">Update</button>                          
                        @endif
                    </div>
            </div>
            <!--end::Text-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Item-->
</div>