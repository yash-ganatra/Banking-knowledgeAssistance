<?php

 $getQuestionNo = fmod($formId,94);
 $getQuestionNo = fmod($getQuestionNo,10)+1;

 ?>

@php
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
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
                              @if($hold_reject_comment['status'] == 5)
                                <h4 class="hold_reject_title">Form on hold: <span class="hold_reject">
                                  {{$hold_reject_comment['comments']}}</span></h4>
                              @else
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
                                <div class="col-md-3">
                                    <p class="lable-cus">Account Type</p>
                                    <p class="lable-green">{{$accountDetails['account_type']}}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="lable-cus">No of Account Holders</p>
                                    <p class="lable-green">{{$accountDetails['no_of_account_holders']}}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="lable-cus">Scheme Code</p>
                                    <p class="lable-green">
                                        @if($accountDetails['account_type_id'] == 3)
                                            <span> {{strtoupper($accountDetails['tdscheme_code'])}} </span>
                                        @else
                                            <span> {{strtoupper($accountDetails['scheme_code'])}} </span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p class="lable-cus">Account Level Type</p>
                                    <p class="lable-green">{{$accountDetails['account_level_type']}}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!---Main Section start here  ------>
                        <div class="tabs">
    <ul id="reviewcod-tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb">
        @for($i = 1;$i<=$accountHoldersCount;$i++)
            @if($i == 1)
                @php
                    $class = "active";
                @endphp
            @else
                @php
                    $class = "";
                @endphp
            @endif
            <li class="nav-item {{$class}}">
                @if($i == 1)
                    <a href="#reviewcod-tab{{$i}}" class="nav-link">Primary Account Holder</a>
                @else
                    <a href="#reviewcod-tab{{$i}}"  class="nav-link">Applicant{{$i}}</a>
                @endif
            </li>
        @endfor
    </ul>
    <div id="reviewcod-tabs-content-cust" class="reviewcod-tabs-content-cust">
        @for($i = 1;$i<=$accountHoldersCount;$i++)
            @php
                $customerOvd = (array) $customerOvdDetails[$i-1];
            @endphp
            <div id="reviewcod-tab{{$i}}" class="reviewcod-tab-content-cust">
                <div class="card" id="customer_on_boarding">
                    <div class="card-block">
                        <div class="col-lg-12">
                                <h4 class="sub-title">Account ID choosen for Debit: {{$customerOvd['account_number']}} |  Customer ID: {{$customerOvd['customer_id']}}</h4>
                                
                                <!-- Row start -->
                                <div class="proofs-blck" style="visibility: hidden;">
                                    <input type="hidden" id="formId" value="{{$formId}}">
                                    <div class="row">
                                       <div class="custom-col-review proof-of-identity col-md-8">
                                            <div class="details-custcol">
                                    
                                                    <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                               <h4>1. Is KYC Updated?</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="kyc_updated_toggle-{{$i}}" class="mobileToggle reviewComments" id="kyc_updated_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                            <label for="kyc_updated_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="kyc_updated">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                  <h4>2. Is FATCA updated?</h4>
                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <span style="margin-left: 4%;color:#000">a. Place of Birth</span>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="place_of_birth_toggle-{{$i}}" class="mobileToggle reviewComments" id="place_of_birth_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                            <label for="place_of_birth_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="place_of_birth">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <span style="margin-left: 4%;color:#000">b. Country of Birth</span>
                                                                
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="country_of_birth_toggle-{{$i}}" class="mobileToggle reviewComments" id="country_of_birth_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                            <label for="country_of_birth_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="country_of_birth">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                               <span style="margin-left: 4%;color:#000">c. Nationality</span>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="nationality_toggle-{{$i}}" class="mobileToggle reviewComments" id="nationality_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                            <label for="nationality_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="nationality">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                 <span style="margin-left: 4%;color:#000">d. Residence country</span>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="residence_country_toggle-{{$i}}" class="mobileToggle reviewComments" id="residence_country_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                            <label for="residence_country_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="residence_country">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>3. Constitution (Individual)?</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="constitution_toggle-{{$i}}" class="mobileToggle reviewComments review" id="constitution_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                            <label for="constitution_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="constitution">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>4. Residential Status Indian?</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="residence_status_indian_toggle-{{$i}}" class="mobileToggle reviewComments" id="residence_status_indian_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                            <label for="residence_status_indian_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="residence_status_indian">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>5. Customer Age (Major)?</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="customer_age_toggle-{{$i}}" class="mobileToggle reviewComments review" id="customer_age_toggle-{{$i}}">
                                                                            <label for="customer_age_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="customer_age">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>6. Valid PAN (TD >=50000)?</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="valid_pan_toggle-{{$i}}" class="mobileToggle reviewComments review" id="valid_pan_toggle-{{$i}}">
                                                                            <label for="valid_pan_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="valid_pan">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>7. Account not under freeze?</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="acoount_not_under_freeze_toggle-{{$i}}" class="mobileToggle reviewComments review" id="acoount_not_under_freeze_toggle-{{$i}}">
                                                                            <label for="acoount_not_under_freeze_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="acoount_not_under_freeze">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>8. Account clear of lien?</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="account_clear_of_lien_toggle-{{$i}}" class="mobileToggle reviewComments" id="account_clear_of_lien_toggle-{{$i}}">
                                                                            <label for="account_clear_of_lien_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="account_clear_of_lien">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>9. Active account (Not Dormant)?</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="active_account_toggle-{{$i}}" class="mobileToggle reviewComments" id="active_account_toggle-{{$i}}">
                                                                            <label for="active_account_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="active_account">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <h4>10. Joint holder check</h4>
                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <span style="margin-left: 4%;color:#000">a. Max 1 joint applicant?</span>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="max_one_joint_applicant_toggle-{{$i}}" class="mobileToggle reviewComments review" id="max_one_joint_applicant_toggle-{{$i}}">
                                                                            <label for="max_one_joint_applicant_toggle-1"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="max_one_joint_applicant">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <span style="margin-left: 4%;color:#000">b. MOP (Single/Either or Survivor)</span>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="mop_toggle-{{$i}}" class="mobileToggle reviewComments review" id="mop_toggle-{{$i}}">
                                                                            <label for="mop_toggle-1"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="mop">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!--New Row-->
                                                  <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>11. Adequate Balance (TD Value: {{$customerOvd['td_amount']}})</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="adequate_balance_toggle-{{$i}}" class="mobileToggle reviewComments review" id="adequate_balance_toggle-{{$i}}">
                                                                            <label for="adequate_balance_toggle-1"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="adequate_balance">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

           
            @if($showdoc == 'true' )
            
                                                     <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <h4>12. Customer Reference  Image</h4>
                                                            </div>
                                                            <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="cc_image_toggle-{{$i}}" class="mobileToggle reviewComments review" id="cc_image_toggle-{{$i}}">
                                                                            <label for="cc_image_toggle-1"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck ">
                                                                        <input type="text" class="form-control commentsField" id="cc_image">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    @php
                                                    $getCCDeclarationImages = Session::get('getCCDeclarationImages');
                                                @endphp

                                                @for($seqCcDoc=0;count($getCCDeclarationImages)>$seqCcDoc;$seqCcDoc++)
                                                
                                                    @php

                                                        $ccDeclaration = isset($declarationAll[$getCCDeclarationImages[$seqCcDoc]->declaration_id]) && $declarationAll[$getCCDeclarationImages[$seqCcDoc]->declaration_id] != ''
                                                                        ?$declarationAll[$getCCDeclarationImages[$seqCcDoc]->declaration_id] :'';
                                                    @endphp
                                                    <label>{{$ccDeclaration}}</label>
                                                    <div class="details-custcol-row">
                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                         <img width="400px" src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$getCCDeclarationImages[$seqCcDoc]->attachment) }}"/>
                                                    </div>
                                                    </div>
                                                @endfor
            @endif
            @php
                $getCCDeclarationImages = (array) current($getCCDeclarationImages);
                $nriDate = '';
            @endphp
            @if(isset($getCCDeclarationImages['declaration_id']) && $getCCDeclarationImages['declaration_id'] == 53)
                @php
                    $nriDate = json_decode($getCCDeclarationImages['dyna_text'],true);
                @endphp
                                                    <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                        <div class="details-left-date-details  align-items-center">
                                                            <h4>13. NRI Email Date : 
                                                                <span  style="color:#545353; margin-left: 26px;" id="nri_date">{{isset($nriDate['nri_date']) && $nriDate['nri_date'] !=''?$nriDate['nri_date']:''}}</span>

                                                            </h4>
                                                        </div>
                                                        <div class="detaisl-right" style="margin-left:160px;">
                                                            <div class=" d-flex flex-row">
                                                                <div class="switch-blck">
                                                                    <div class="toggleWrapper">
                                                                        <input type="checkbox" name="nri_email_date-{{$i}}" class="mobileToggle reviewComments review" id="nri_email_date-{{$i}}">
                                                                        <label for="nri_email_date-1"></label>
                                                    </div>
                                                                </div>
                                                                <div class="comments-blck ">
                                                                    <input type="text" class="form-control commentsField" id="nri_email_date">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>   
            @else
                                                <div class="details-custcol-row">
                                                    <div class="details-custcol-row-top d-flex">
                                                            <div class="details-left-date-details d-flex align-items-center">
                                                               <h4>12.Select Value Date</h4>
                                                               <div class="details-custcol-row-bootm">
                                                           <!-- 22May23 - For BS5 - commented below line -->
                                                            <!-- <div class="mt-2 ml-4 value_date_div"> -->
                                                                <div class="ml-4 value_date_div form-check form-check-inline">
                                                                <div class="radio-selection">
                                                                    <label class="radio">
                                                                        <input class="AddOvdDetailsField creation_date" type="radio" name="value_date" id="creation_date" value="01"  created_at="{{\Carbon\Carbon::parse($customerOvd['created_at'])->format('d-m-Y')}}">
                                                                        <span class="lbl padding-8 creation_date_label">Creation Date ({{strtoupper(\Carbon\Carbon::parse($customerOvd['created_at'])->format('d-M-Y'))}})</span>
                                                                    </label>
                                                                    <label class="radio">
                                                                        <input classs="AddOvdDetailsField review_date" type="radio" name="value_date" id="review_date" value="02" checked="checked" review_date="{{\Carbon\Carbon::now()->format('d-m-Y')}}">
                                                                        <span class="lbl padding-8">Review Date ({{strtoupper(\Carbon\Carbon::now()->format('d-M-Y'))}})</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                            </div>
                                                        </div>
                                                    </div>
            @endif
            @if($nriDate == '' && $tdSchemeCode == 1)
                <div class="details-custcol-row">
                    <div class="details-custcol-row-top d-flex">
                        <div class="details-left-date-details  align-items-center">
                            <h4>13.Mode Of Communication: Phone
                            </h4>
                                            </div>
                                </div>
                </div>  
            @endif
                            </div>
                                </div>
                            </div>
                            <!-- Row end -->
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>

                    <!-----Main section end here ----->





<div class="col-md-12 text-center mb-3">
                    @if(!in_array($accountDetails['application_status'],[5,12]))
                        
                            <!-- hide for match and pending -->
                           <!--  @if($deDupeStatusButton) -->
                                <!-- <button type="button" class="btn btn-primary mr-4 submit_to_bank clear display-none" id="approved">Clear</button> -->
                            <!-- @else
                                <button type="button" class="btn btn-outline-danger mr-4 submit_to_bank" disabled id="approved">Dedupe not cleared</button> -->
                            <!-- @endif
                            @if($deDupeStatusButton == false)
                            <button type="button" class="btn btn-outline-danger mr-4 submit_to_bank display-none" disabled>Dedupe not cleared</button>
                            @else
                            @endif -->
                             <button type="button" class="btn btn-primary mr-4 submit_to_bank clear display-none" id="approved">Clear</button>
                            <!-- <button type="button" class="btn btn-info mr-4 submit_to_bank discrepent" id="discrepent">Discrepant</button> -->
                            <button type="button" class="btn btn-warning mr-4" id="hold_modal">Hold</button>
                            <!-- <button type="button" class="btn btn-danger" id="reject_modal">Reject</button> -->

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

           <!-- <div class="row npcl2-continue">
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
@push('scripts')

<script type="text/javascript">

    man_ver_id = JSON.parse('<?php echo json_encode($getQuestionNo); ?>');

    $(document).ready(function(){

       
    setTimeout(function(){
    $(".reviewComments").click();

    },400);


    setTimeout(function(){
        
        $($('.reviewComments')[man_ver_id]).click();

        $('.proofs-blck').css('visibility','');


    },700);
    
 });


</script>

@endpush