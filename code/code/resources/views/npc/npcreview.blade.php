@php
$is_huf = false;
if($accountDetails['constitution'] == 'NON_IND_HUF'){
    $is_huf = true;
}
@endphp

@if(isset($hold_reject_comment['comments']))
<div class="pcoded-content1 branch-review-new" id="hold_reject_div">
  <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper" style="padding-bottom:0px;">
                <!-- Page-body start -->
                <div class="page-body">
                    <div class="card">
                       <div id="casatd-key-block" class="card-block pb-0">
                           <div class="row">
                             <div class="col-md-12">
                              
                              @if($aofStatus == 'HOLD')
                                <h4 class="hold_reject_title">Form on hold: <span class="hold_reject">
                                  {{$hold_reject_comment['comments']}}</span></h4>
                              @endif
                              @if($aofStatus == 'REJECTED')
                                 <h4 class="hold_reject_title">Form rejected: <span class="hold_reject">
                                  {{$hold_reject_comment['comments']}}</span></h4>
                              @endif
                             </div>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
<div class="pcoded-content1 branch-review-new" id="verify-checkbox">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper" style="padding-bottom:0px;">
                <!-- Page-body start -->
                <div class="page-body">
                    <div class="card">
                        <div id="casatd-key-block" class="card-block pb-0">
                            <div class="row">
                                <div class="col-md-2">
                                    <p class="lable-cus">Account Type</p>
                                    <p class="lable-green">{{$accountDetails['account_type']}}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="lable-cus">
                                      {{ $is_huf ? 'No of Customer Id' : 'No of Account Holders' }}
                                    </p>
                                    <p class="lable-green">{{$accountDetails['no_of_account_holders']}}</p>
                                </div>
                                <div class="col-md-2">
                                    <p class="lable-cus">Scheme Code</p>
                                    <p class="lable-green">
                                        @if($accountDetails['account_type_id'] == 3)
                                            <span> {{strtoupper($accountDetails['tdscheme_code'])}} </span>
                                        @else
                                        @if($accountDetails['account_type_id'] ==2)
                                            @if($accountDetails['scheme_code'] != '')
                                            <span> {{strtoupper($accountDetails['scheme_code'])}}</span>
                                        @else
                                                <span> {{strtoupper($entityDetails['entity_scheme_code'])}}</span>
                                            @endif
                                        @else
                                            <span> {{strtoupper($accountDetails['scheme_code'])}} </span>
                                        @endif
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-2">
                                    <p class="lable-cus">Account Level Type</p>
                                    <p class="lable-green">{{$accountDetails['account_level_type']}}</p>
                                </div>
                                <div class="col-md-2">
                                    <p class="lable-cus">Review Form Iteration</p>
                                    <p class="lable-green">{{$reviewIteration}}</p>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary tauggle_blur" id="unmaskfields">UnMask</button>
                                    <button type="submit" class="btn btn-primary tauggle_blur" id="maskfields" style="display: none;background-color:#DE3163;">Mask</button>
                            </div>
                        </div>
                    </div>
                    </div>
                    <!----Normal Review Flow------->
                    @include('npc.basicdetailsreview')
                    @include('npc.ovddetailsreview')
                    @if($accountDetails['account_type'] == 'Current' && $accountDetails['flow_tag_1'] != 'INDI')
                    @include('npc.entitydetailsreview') 
                    @endif
                    @if($delightSavings)
                       @include('npc.delightdetailsreview')
                    @endif
                  <!-- old logic -->
                    <!-- @php 
                        $counter = 0;
                        for($i = 1;$i<=$accountHoldersCount;$i++){
                            if(isset($riskDetails[$i-1]->occupation) && ($riskDetails[$i-1]->occupation == 'OTHER - SELF EMPLOYED PROFESSIONAL' || $riskDetails[$i-1]->occupation == 'OTHER - SELF EMPLOYED - NON-PROFESSIONAL'  || $riskDetails[$i-1]->occupation == 'OTHER - TRADING' || $riskDetails[$i-1]->occupation == 'OTHERS') || isset($reviewDetails['occupation-'.$i]) && $reviewDetails['occupation-'.$i] != '' || isset($reviewDetails['other_occupation-'.$i]) && $reviewDetails['other_occupation-'.$i] != ''){
                                $counter++;
                            }
                        }
                    @endphp -->
                @if($accountDetails['source'] !='DSA')
                        <!-- old logic -->
                    <!-- @if(($roleId == 3 || $roleId == 4) && ($counter > 0)&& (isset($accountDetails['flow_type']) &&  $accountDetails['flow_type'] != 'ETB'))
                      @include('npc.riskdetailsreview')
                    @endif -->

                    @if(($roleId == 3 || $roleId == 4) && (isset($checkvisibleciid['status']) && $checkvisibleciid['status'] == 'Y'))
                      @include('npc.riskdetailsreview')
                    @endif
                    @include('npc.financialdetailsreview')
                
                    @include('npc.nomineedetailsreview')
                    @include('npc.documentsdetailsreview')
                    @if($accountDetails['account_type'] == 'Current' && (in_array($roleId,[3,4,5,8])))
                      @include('npc.entitydetailsquestionreview')
                    @endif
                    @if(in_array($roleId,[5,6,8]))
                      @include('npc.riskdetailsdisplay')
                    @endif
                    @if(in_array($roleId, [5,6,8]))
                      @include('npc.addnotes')
                    @endif

                @endif 

                    <div class="col-md-12 text-center mb-3">
               
                    @if(!in_array($accountDetails['application_status'],[5,12]))
                        @if(!in_array($roleId, [5,6,8]))
                            <!-- hide for match and pending -->
                            @if($deDupeStatusButton)
                                <!-- <button type="button" class="btn btn-primary mr-4 submit_to_bank clear display-none" id="approved">Clear</button> -->
                            @else
                                <button type="button" class="btn btn-outline-danger mr-4 submit_to_bank" disabled id="approved">Dedupe not cleared</button>
                            @endif
                            @if($deDupeStatusButton == false)
                                <button type="button" class="btn btn-outline-danger mr-4 submit_to_bank display-none" disabled>Dedupe not cleared</button>
                            @elseif(!$neftStatusButton)
                                <button type="button" class="btn btn-outline-danger mr-4 submit_to_bank" disabled id="approved">Neft canceled</button>
                            @else
                                <button type="button" class="btn btn-primary mr-4 submit_to_bank clear display-none" id="approved">Clear</button>
                            @endif
                            <button type="button" class="btn btn-info mr-4 submit_to_bank discrepent" id="discrepent">Discrepant</button>
                            <button type="button" class="btn btn-warning mr-4" id="hold_modal">Hold</button>
                            <button type="button" class="btn btn-danger" id="reject_modal">Reject</button>
                        @elseif($roleId == 5)
                            <button type="button" class="btn btn-primary mr-4 submit_to_bank noValidation clear display-none" id="approved">QC Cleared</button>
                            <button type="button" class="btn btn-info mr-4 submit_to_bank discrepent" id="discrepent">QC Discrepant</button>
                            <button type="button" class="btn btn-warning mr-4" id="hold_modal">QC Hold</button>
                        @elseif($roleId == 8)
                            <button type="button" class="btn btn-primary mr-4 submit_to_bank noValidation clear" id="approved">L3 Cleared</button>
                            <button type="button" class="btn btn-warning mr-4" id="hold_modal">L3 Hold</button>
                        @else
                            <button type="button" class="btn btn-primary mr-4 submit_to_bank noValidation clear display-none" id="approved">Audit Cleared</button>
                            <button type="button" class="btn btn-info mr-4 submit_to_bank discrepent" id="discrepent">Audit Discrepant</button>
                            <button type="button" class="btn btn-warning mr-4" id="hold_modal">Hold</button>
                        @endif
                      @endif
                    </div>

                       <!-- Modal content for Image Preview -->
                    <div id="imagePreviewModal" class="modal-review" style="padding-top:20px; z-index:10000;">

                       <div class="modal-content-review text-center hold_reject_model" style="width:85%;">
                          <p>Image Preview &nbsp;&nbsp;<button class="btn btn btn-primary cancel-preview">Cancel</button> </p>
                          <img id='imagePreviewSrc' src="/CUBE/public/assets/images/dcb-logo.svg" />
                          <br>

                       </div>
                    </div>

                    <!-- The Modal -->
                    <div id="Hold" class="modal-review">
                       <!-- Modal content -->
                       <div class="modal-content-review text-center hold_reject_model">
                          <p>Are you sure to put the form on <span class="hold_reject_title">Hold</span>?<br>If Yes, please comment.</p>
                          <!-- <input type="text" class="form-control commentsField mb-3" id="hold_comment"> -->
                          <textarea type="text" class="form-control commentsField mb-3" id="hold_comment" rows="4"></textarea>
                          <button class="btn btn-danger submit_to_bank mr-4" id="hold" >Confirm</button>
                          <button class="btn btn btn-primary hold-no">Cancel</button>
                       </div>
                    </div>
                    <!-- The Modal -->
                    <div id="Reject" class="modal-review">
                       <!-- Modal content -->
                       <div class="modal-content-review text-center hold_reject_model">
                          <p>Are you sure to <span class="hold_reject_title">Reject</span> the form?<br>If Yes, please comment.</p>
                          <!-- <input type="text" class="form-control commentsField mb-3" id="reject_comment"> -->
                          <textarea type="text" class="form-control commentsField mb-3" id="reject_comment" rows="4"></textarea>
                          <button class="btn btn2 btn-danger submit_to_bank mr-4" id="reject" >Confirm</button>
                          <button class="btn btn btn-primary reject-no">Cancel</button>
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Input field for fromRole -->
<input type='hidden' id='fromRole' value='{{$fromRole}}' />

<!-- Modal NPC side -->
@if($roleId == 3)
<div class="modal fade" id="NpcModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">NPC L1</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <!-- <span aria-hidden="true">&times;</span> -->
        </button>
      </div>
       <div class="br_submit_loader display-none-npc-loader">
                  <div class="br_submit_loader__element"></div>
            </div>
      <div class="modal-body">
        <p id="processing_message"></p>
      </div>
    </div>
  </div>
</div>
@endif


@if($roleId == 4)
<!-- Modal -->
<div class="modal fade" id="NpcModal2" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document" style="width: 50% !important; max-width:100%;">
    <div class="modal-content">
      <div class="modal-header modal-header-body">
        <h5 class="modal-title" id="exampleModalLabel">NPC L2 Review:</h5>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <!-- <span aria-hidden="true">&times;</span> -->
        </button>
      </div>
      <div class="modal-body ">
        <div class="">
           <div class="row">
             {{--  <div class="l2_loader display-none-npc2-loader" id="npc_loader">
                  <div class="l2_progress_loader__element" id="npc_stop_loader"></div>
        </div> --}}
             <div class="col">
                <div class="l2_loader_1 display-none-npc2-loader" id="npc_loader1">
                  <div class="l2_progress_loader__element1" id="npc_stop_loader1"></div>

                </div>

                 <div class="svg-container" >
                    <svg class="ft-green-tick" xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 48 48" aria-hidden="true">
                        <circle id="circle_progress1" class="circle_inprogress" fill="#c7d0c5" cx="24" cy="24" r="22"/>
                        <circle class="circle display-none" id="circle_success1" fill="#5bb543" cx="24" cy="24" r="22"/>
                        <path id="check1" class="tick display-none" fill="none" stroke="#FFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M14 27l5.917 4.917L34 17"/>
                    </svg>

                </div>
             </div>

             <div class="col">
               <div class="l2_loader_2 display-none-npc2-loader" id="npc_loader2">
                  <div class="l2_stop_loader__element1" id="npc_stop_loader2"></div>

                </div>

                <div class="svg-container">
                    <svg class="ft-green-tick" xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 48 48" aria-hidden="true">
                        <circle  id="circle_progress2" class="circle_inprogress" fill="#c7d0c5" cx="24" cy="24" r="22"/>
                        <circle class="circle display-none" id="circle_success2" fill="#5bb543" cx="24" cy="24" r="22"/>
                        <path id="check2" class="tick display-none" fill="none" stroke="#FFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M14 27l5.917 4.917L34 17"/>
                    </svg>

                </div>
             </div>

             <div class="col">
                   <div class="l2_loader_3 display-none-npc2-loader" id="npc_loader3">
                    <div class="l2_stop_loader__element1" id="npc_stop_loader3"></div>
                  </div>

                   <div class="svg-container">
                    <svg class="ft-green-tick" xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 48 48" aria-hidden="true">
                        <circle id="circle_progress3" class="circle_inprogress" fill="#c7d0c5" cx="24" cy="24" r="22"/>
                        <circle class="circle display-none" id="circle_success3" fill="#5bb543" cx="24" cy="24" r="22"/>
                        <path id="check3" class="tick display-none" fill="none" stroke="#FFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M14 27l5.917 4.917L34 17"/>
                    </svg>

                </div>
             </div>

             <div class="col">
                  <div class="l2_loader_4 display-none-npc2-loader" id="npc_loader4">
                     <div class="l2_stop_loader__element1" id="npc_stop_loader4"></div>
                 </div>
                 @if($accountDetails['account_type'] == "Term Deposit")
                 <div class="svg-container display-none">
                    <svg class="ft-green-tick" xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 48 48" aria-hidden="true">
                        <circle id="circle_progress4" class="circle_inprogress" fill="#c7d0c5" cx="24" cy="24" r="22"/>
                        <circle class="circle display-none" id="circle_success4" fill="#c7d0c5" cx="24" cy="24" r="22"/>
                        <path id="check4" class="tick display-none" fill="none" stroke="#FFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M14 27l5.917 4.917L34 17"/>
                    </svg>
                 </div>
                 @else
                  <div class="svg-container">
                    <svg class="ft-green-tick" xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 48 48" aria-hidden="true">
                        <circle id="circle_progress4" class="circle_inprogress" fill="#c7d0c5" cx="24" cy="24" r="22"/>
                        <circle class="circle display-none" id="circle_success4" fill="#c7d0c5" cx="24" cy="24" r="22"/>
                        <path id="check4" class="tick display-none" fill="none" stroke="#FFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" d="M14 27l5.917 4.917L34 17"/>
                    </svg>
                 </div>
                 @endif
             </div>
             @if($accountDetails['account_type'] == "Term Deposit")
               <div class="col display-none">
                    <div class="l2_loader_5 display-none-npc2-loader" id="npc_loader5">
                      <div class="l2_stop_loader__element1" id="npc_stop_loader5"></div>
                    </div>
               </div>
             @else
               <div class="col">
                      <div class="l2_loader_5 display-none-npc2-loader" id="npc_loader5">
                        <div class="l2_stop_loader__element1" id="npc_stop_loader5"></div>
                      </div>
                 </div>
             @endif

           </div>


           <div class="row" style="margin-top: -2%;text-align: center;">
             <div class="col">
                 <h3 class="check-text progress_text1"></h3>
             </div>
             <div class="col">
                    <h3 class="check-text progress_text2"></h3>
             </div>
             <div class="col">
               <h3 class="check-text progress_text3"></h3>
             </div>
             <div class="col">
              @if($accountDetails['account_type'] == "Term Deposit")
                 <h3 class="check-text progress_text4 display-none"></h3>
              @else
                 <h3 class="check-text progress_text4"></h3>
              @endif
             </div>
            @if($accountDetails['account_type'] == "Term Deposit")
                    <div class="col display-none"></div>
            @else
              <div class="col"></div>
            @endif
           </div>

          <!--  <div class="row npcl2-continue">
             <div class="col">
                      <h3 class="check-text progress_text5 contine-button-l2"></h3>
             </div>
           </div> -->



       </div>
      </div>
      <div class="modal-footer modal-footer-body">
       
        <div class="row npcl2-continue mx-auto">
          <div class="col">
              <h3 class="check-text progress_text5 contine-button-l2"></h3>
    </div>
  </div>

     </div>
    </div>
  </div>
</div>

@endif