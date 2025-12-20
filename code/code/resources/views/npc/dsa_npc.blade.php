@php
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
@endphp
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
                
                    if($accountDetails['flow_tag_1'] == 'INDI'){
                        $currentPropInd = 'Individual';
                    }else{
                        $currentPropInd = 'Proprietorship';
                    }
            @endphp
            <div id="reviewcod-tab{{$i}}" class="reviewcod-tab-content-cust">
                <div class="card" id="customer_on_boarding">
                    <div class="card-block">
                        <div class="row">
                            <div class="col-lg-12">
                                @if($accountDetails['account_type'] == 'Current')
                                <h4 class="sub-title">Customer onbording details ({{$currentPropInd}})</h4>
                                @else
                                <h4 class="sub-title">Customer onbording details</h4>
                                @endif
                                <!-- Row start -->
                                <div class="proofs-blck">
                                    <input type="hidden" id="formId" value="{{$formId}}">
                                    <div class="row">
                                        @if($customerOvd['is_new_customer'] == 1)
                                            <div class="custom-col-review col-md-4">
                                                <div class="form-group">
                                                    <div class="proof-of-identity">
                                                        <div class="row" style="margin-bottom: 8px;">
                                                            <h4>{{ucfirst($customerOvd['pf_type'])}}</h4>
                                                            
                                                             <!-- 22May23 - For BS5 - commented below line -->
                                                            <!-- <button id="rotate" class="rotate" style="margin-left: 356px;"><i class="fa fa-rotate-right"></i></button> -->
                                                            <button id="rotate" class="rotate col-sm-1" style="margin-left:356px"><i class="fa fa-rotate-right"></i></button>
                                                        </div>
                                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                            <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/_DONOTSIGN_'.$customerOvd['pf_type_image']) }}" class="img-fluid ovd_image rotate_image">
                                                        </div>
                                                        @if(($is_review == 1) && (!isset($reviewDetails['pf_type_image-'.$i])))
                                                            @php
                                                                $checked = "checked";
                                                                $display = "display-none";
                                                                $disabled = 'disabled';
                                                            @endphp
                                                        @else
                                                            @php
                                                                $checked = "";
                                                                $display = "";
                                                                $disabled = '';
                                                            @endphp
                                                        @endif
                                                        <div class="detaisl-right" style="margin-top:15px; width:100%;">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                <span>
                                                                    @if(isset($qcReviewDetails['pf_type_image-'.$i]))
                                                                         <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                            {{$qcReviewDetails['pf_type_image-'.$i]}}
                                                                        </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="d-flex flex-row">
                                                                <div class="switch-blck" style="margin-right: 20px;">
                                                                    <div class="toggleWrapper">
                                                                        <input type="checkbox" name="pf_type_image_toggle-{{$i}}" class="mobileToggle reviewComments" id="pf_type_image_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="pf_type_image_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}" style="width:100%;">
                                                                    <input type="text" class="form-control commentsField" id="pf_type_image-{{$i}}" name="pf_type_image-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="custom-col-review proof-of-identity col-md-8">
                                            @if($customerOvd['is_new_customer'] != 1)
                                                <div class="details-custcol-row">
                                                    <h4>
                                                        Existing Customer:
                                                        <span style="color: #364fcc;">
                                                            {{strtoupper($customerOvd['customer_full_name'])}} [CustID: {{strtoupper($customerOvd['customer_id'])}}]
                                                        </span>
                                                    </h4>
                                                </div>
                                            @endif
                                            <h4>Verify Customer on boarding Details</h4>
                                            <div class="details-custcol">
                                                @if($i == 1)
                                                    @if(($is_review == 1) && (!isset($reviewDetails['scheme_code'])))
                                                        @php
                                                            $checked = "checked";
                                                            $display = "display-none";
                                                            $disabled = 'disabled';
                                                        @endphp
                                                    @else
                                                        @php
                                                            $checked = "";
                                                            $display = "";
                                                            $disabled = '';
                                                        @endphp
                                                    @endif
                                                    <div class="details-custcol-row">
                                                        <div class="details-custcol-row-top d-flex">
                                                            <div class="detaisl-left d-flex align-items-center">
                                                                Scheme Code :
                                                                @if($accountDetails['account_type_id'] == 3)
                                                                    <span> {{strtoupper($accountDetails['tdscheme_code'])}} </span>
                                                                @else
                                                                @if($accountDetails['account_type_id'] ==2)
                                                                    <span>{{strtoupper($accountDetails['scheme_code'])}}</span>
                                                                @else
                                                                    <span> {{strtoupper($accountDetails['scheme_code'])}} </span>
                                                                @endif
                                                                @endif
                                                                <span>
                                                                    @if(count($qcReviewDetails) > 0)
                                                                        @if(isset($qcReviewDetails['scheme_code']))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                             {{$qcReviewDetails['scheme_code']}}
                                                                        </span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                @if((($is_review == 1) && (!isset($reviewDetails['pancard_no-'.$i]))) || ($accountDetails['customer_id'] != '') && (!in_array(Session::get('role'),[5,6])) && ($customerOvd['is_new_customer'] == 0))
                                                    @php
                                                        $checked = "checked";
                                                        $display = "display-none";
                                                        $disabled = 'disabled';
                                                    @endphp
                                                @else
                                                    @php
                                                        $checked = "";
                                                        $display = "";
                                                        $disabled = '';
                                                    @endphp
                                                @endif
                                                <div class="details-custcol-row">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            @if($customerOvd['pf_type'] == "pancard")
                                                                PAN Number :
                                                                <label class="maskingfield">
                                                                    <label>***********</label>
                                                                </label>
                                                                <span class="unmaskingfield" style="display: none;">
                                                                    <label>{{$customerOvd['pancard_no']}}</label>
                                                                </span>
                                                            @endif
                                                            <span>
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['pancard_no-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                                {{$qcReviewDetails['pancard_no-'.$i]}}
                                                                        </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                            {{-- <div class=" d-flex flex-row">
                                                                <div class="switch-blck">
                                                                    <div class="toggleWrapper">
                                                                        <input type="checkbox" name="pancard_no_toggle-{{$i}}" class="mobileToggle reviewComments" id="pancard_no_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="pancard_no_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}">
                                                                    <input type="text" class="form-control commentsField" id="pancard_no-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div> --}}
                                                        </div>
                                                    </div>
                                                </div>

                                                @if((($is_review == 1) && (!isset($reviewDetails['dob-'.$i]))) || ($accountDetails['customer_id'] != '') && (!in_array(Session::get('role'),[5,6])) && ($customerOvd['is_new_customer'] == 0))
                                                    @php
                                                        $checked = "checked";
                                                        $display = "display-none";
                                                        $disabled = 'disabled';
                                                    @endphp
                                                @else
                                                    @php
                                                        $checked = "";
                                                        $display = "";
                                                        $disabled = '';
                                                    @endphp
                                                @endif
                                                <div class="details-custcol-row">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            DOB :
                                                            <span>
                                                                {{strtoupper(Carbon\Carbon::parse($customerOvd['dob'])->format('d-M-Y'))}}
                                                                @if(count($qcReviewDetails) > 0)
                                                               
                                                                    @if(isset($qcReviewDetails['dob-'.$i]))
                                                                   <span class="review-comment">
                                                                        <i class="fa fa-exclamation review-exclamation"></i>
                                                                         {{$qcReviewDetails['dob-'.$i]}}
                                                                    </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                            
                                                        </div>
                                                    </div>
                                                </div>

                                                @if(($is_review == 1) && (!isset($reviewDetails['dedupe_refrence-'.$i])))
                                                    @php
                                                        $checked = "checked";
                                                        $display = "display-none";
                                                        $disabled = 'disabled';
                                                    @endphp
                                                @else
                                                    @php
                                                        $checked = "";
                                                        $display = "";
                                                        $disabled = '';
                                                    @endphp
                                                @endif
                                                <div class="details-custcol-row" id="dedupe_refrence_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            Dedupe Refrence :
                                                            <span>
                                                                {{$customerOvd['query_id']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['dedupe_refrence-'.$i]))
                                                                    <span class="review-comment">
                                                                        <i class="fa fa-exclamation review-exclamation"></i>
                                                                         {{$qcReviewDetails['dedupe_refrence-'.$i]}}
                                                                    </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                           
                                                        </div>
                                                    </div>
                                                </div>

                                                @if(($is_review == 1) && (!isset($reviewDetails['dedupe_reference_comment-'.$i])))
                                                    @php
                                                        $checked = "checked";
                                                        $display = "display-none";
                                                        $disabled = 'disabled';
                                                    @endphp
                                                @else
                                                    @php
                                                        $checked = "";
                                                        $display = "";
                                                        $disabled = '';
                                                    @endphp
                                                @endif
                                                <div class="details-custcol-row" id="dedupe_reference_comment_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            Dedupe Reference Comment :
                                                            <span>
                                                                {{$customerOvd['dedupe_reference']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['dedupe_reference_comment-'.$i]))
                                                                    <span class="review-comment">
                                                                        <i class="fa fa-exclamation review-exclamation"></i>
                                                                         {{$qcReviewDetails['dedupe_reference_comment-'.$i]}}
                                                                    </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                           
                                                        </div>
                                                    </div>
                                                </div>


                                                @if(($is_review == 1) && (!isset($reviewDetails['dedupe_status-'.$i])))
                                                    @php
                                                        $checked = "checked";
                                                        $display = "display-none";
                                                        $disabled = 'disabled';
                                                    @endphp
                                                @else
                                                    @php
                                                        $checked = "";
                                                        $display = "";
                                                        $disabled = '';
                                                    @endphp
                                                @endif
                                                <div class="details-custcol-row" id="dedupe__status_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            Dedupe Status :
                                                            <!-- <span style="color: green;"> $customerOvd['dob'] </span> -->
                                                            <span>
                                                                {{$customerOvd['dedupe_status']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['dedupe_refrence-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                            {{$qcReviewDetails['dedupe_status-'.$i]}}
                                                                         </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="details-custcol-row" id="vkyc__status_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            Vkyc Status:
                                                          
                                                            <span>
                                                                {{$customerOvd['dsa_vkyc']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['dsa_vkyc-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                            {{$qcReviewDetails['dsa_vkyc-'.$i]}}
                                                                         </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="details-custcol-row" id="dsa_name_mismatch_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            Name Mismatch Status:                                                          
                                                            <span>
                                                                {{$customerOvd['dsa_name_mismatch']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['dsa_name_mismatch-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                            {{$qcReviewDetails['dsa_name_mismatch-'.$i]}}
                                                                         </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">

                                                            <div class=" d-flex flex-row">
                                                                <div class="switch-blck">
                                                                    <div class="toggleWrapper">
                                                                        <input type="checkbox" name="dsa_name_toggle-{{$i}}" class="mobileToggle reviewComments" id="dsa_name_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="dsa_name_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}">
                                                                    <input type="text" class="form-control commentsField" id="dsa_name_mismatch-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                @if (in_array($customerOvd['proof_of_identity'], [1, 'Aadhaar Photocopy']) || 
                                                    in_array($customerOvd['proof_of_address'], [1, 'Aadhaar Photocopy']))
                                                <div class="details-custcol-row" id="dsa_aadhar__status_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            Name as per Aadhaar:                                                          
                                                            <span>
                                                                {{$customerOvd['first_name'].' '.$customerOvd['middle_name'].' '.$customerOvd['last_name']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if($qcReviewDetails['first_name-'.$i].' '.$qcReviewDetails['middle_name-'.$i].' '.$qcReviewDetails['last_name-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                            {{$qcReviewDetails['first_name-'.$i].' '.$qcReviewDetails['middle_name-'.$i].' '.$qcReviewDetails['last_name-'.$i]}}
                                                                         </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

                                                <div class="details-custcol-row" id="dsa_pan__status_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            Name as per Pan:                                                          
                                                            <span>
                                                                {{$customerOvd['dsa_panname_user']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['dsa_panname_user-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                            {{$qcReviewDetails['dsa_panname_user-'.$i]}}
                                                                         </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                        </div>
                                                    </div>
                                                </div>
                                                

                                                <div class="details-custcol-row" id="dsa_funding__status_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                            Name as per Funding: 
                                                            <span>
                                                                {{$customerOvd['account_name']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['account_name-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                            {{$qcReviewDetails['account_name-'.$i]}}
                                                                         </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="details-custcol-row" id="dsa_seeding__status_div">
                                                    <div class="details-custcol-row-top d-flex">
                                                        <div class="detaisl-left d-flex align-items-center">
                                                           Aadhaar Seeding Status:
                                                            <span>
                                                                {{$customerOvd['dsa_aadhaarseedingstatus']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['dsa_aadhaarseedingstatus-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                            {{$qcReviewDetails['dsa_aadhaarseedingstatus-'.$i]}}
                                                                         </span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Row end -->
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>