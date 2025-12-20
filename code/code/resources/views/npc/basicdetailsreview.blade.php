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

            @php
            $is_huf_display = false;
            
            if($accountDetails['constitution'] == 'NON_IND_HUF'){
                $is_huf_display = true;
            }
            $firstTab = $is_huf_display ? 'Karta/Manager' : 'Primary Account Holder';
            $secondTab = $is_huf_display ? 'HUF' : 'Applicant'.$i;
            @endphp

            <li class="nav-item {{$class}}">
                @if($i == 1)
                    <a href="#reviewcod-tab{{$i}}" class="nav-link">{{ $firstTab }}</a>
                @else
                    <a href="#reviewcod-tab{{$i}}"  class="nav-link">{{ $secondTab }}</a>
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
                                                        <div class="uploaded-img-ovd" style="filter:blur(30px);">
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
                                                            <!-- <div class="detaisl-right">
                                                                <div class=" d-flex flex-row">
                                                                    <div class="switch-blck">
                                                                        <div class="toggleWrapper">
                                                                            <input type="checkbox" name="scheme_code_toggle-{{$i}}" class="mobileToggle reviewComments" id="scheme_code_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                            <label for="scheme_code_toggle-{{$i}}"></label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="comments-blck {{$display}}">
                                                                        <input type="text" class="form-control commentsField" id="scheme_code">
                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                </div>
                                                            </div> -->
                                                        </div>
                                                    </div>

                                                    @if(isset($accountDetails['td_scheme_code']))
                                                        @if(($is_review == 1) && (!isset($reviewDetails['td_scheme_code'])))
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
                                                                    TD Scheme Code :
                                                                    <span>
                                                                        {{strtoupper($accountDetails['td_scheme_code'])}}
                                                                        @if(count($qcReviewDetails) > 0)
                                                                            @if(isset($qcReviewDetails['td_scheme_code']))
                                                                            <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                                {{$qcReviewDetails['td_scheme_code']}}
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
                                                                                <input type="checkbox" name="td_scheme_code_toggle" class="mobileToggle reviewComments" id="td_scheme_code_toggle" {{$checked}} {{$disabled}}>
                                                                                <label for="td_scheme_code_toggle"></label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="comments-blck {{$display}}">
                                                                            <input type="text" class="form-control commentsField" id="td_scheme_code">
                                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                        </div>
                                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
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
                                                                    <label id="unmaskpanvalue">{{$customerOvd['pancard_no']}}</label>
                                                                </span>
                                                            @else
                                                              FORM60 complete Inorder :
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
                                                            <div class=" d-flex flex-row">
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
                                                            </div>
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
                                                            @if($accountDetails['constitution'] == 'NON_IND_HUF' && $i==2)
                                                            DOF (Date of Formation) :
                                                            @else
                                                            DOB :
                                                            @endif
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
                                                            <div class=" d-flex flex-row">
                                                                <div class="switch-blck">
                                                                    <div class="toggleWrapper">
                                                                        <input type="checkbox" name="dob_toggle-{{$i}}" class="mobileToggle reviewComments" id="dob_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="dob_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}">
                                                                    <input type="text" class="form-control commentsField" id="dob-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($accountDetails['constitution'] == 'NON_IND_HUF' && $i==2)
                                                @if((($is_review == 1) && (!isset($reviewDetails['huf_signatory_relation-'.$i]))) || ($accountDetails['customer_id'] != '') && (!in_array(Session::get('role'),[5,6])) && ($customerOvd['is_new_customer'] == 0))
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
                                                            Relationship Between HUF and Signatory :
                                                            <span>
                                                                {{strtoupper($customerOvd['huf_signatory_relation'])}}
                                                                @if(count($qcReviewDetails) > 0)
                                                               
                                                                    @if(isset($qcReviewDetails['huf_signatory_relation-'.$i]))
                                                                   <span class="review-comment">
                                                                        <i class="fa fa-exclamation review-exclamation"></i>
                                                                         {{$qcReviewDetails['huf_signatory_relation-'.$i]}}
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
                                                                        <input type="checkbox" name="huf_signatory_relation_toggle-{{$i}}" class="mobileToggle reviewComments" id="huf_relation_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="huf_signatory_relation_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}">
                                                                    <input type="text" class="form-control commentsField" id="huf_signatory_relation-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif

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
                                                            <!-- <div class=" d-flex flex-row">
                                                                <div class="switch-blck">
                                                                    <div class="toggleWrapper">
                                                                        <input type="checkbox" name="dedupe_status_toggle-{{$i}}" class="mobileToggle reviewComments" id="dedupe_status_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="dedupe_status_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}">
                                                                    <input type="text" class="form-control commentsField" id="dedupe_status-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div> -->
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
                                                            <!-- <div class=" d-flex flex-row">
                                                                <div class="switch-blck">
                                                                    <div class="toggleWrapper">
                                                                        <input type="checkbox" name="dedupe_status_toggle-{{$i}}" class="mobileToggle reviewComments" id="dedupe_status_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="dedupe_status_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}">
                                                                    <input type="text" class="form-control commentsField" id="dedupe_status-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div> -->
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
                                                            <!-- <div class=" d-flex flex-row">
                                                                <div class="switch-blck">
                                                                    <div class="toggleWrapper">
                                                                        <input type="checkbox" name="dedupe_status_toggle-{{$i}}" class="mobileToggle reviewComments" id="dedupe_status_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="dedupe_status_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}">
                                                                    <input type="text" class="form-control commentsField" id="dedupe_status-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div> -->
                                                        </div>
                                                    </div>
                                                </div>


                                                @if($accountDetails['scheme_code'] == 'SB118' && $i == '1')
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
                                                                Corp. Label Code :
                                                                <!-- <span style="color: green;"> $customerOvd['dob'] </span> -->
                                                                <span>
                                                                    {{$customerOvd['label_code']}}
                                                                    @if(count($qcReviewDetails) > 0)
                                                                        @if(isset($qcReviewDetails['dedupe_status-'.$i]))
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
                                                @endif
                                                
                                                @if($accountDetails['scheme_code'] == 'SB124' || $accountDetails['scheme_code'] == 'CA224')
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
                                                            Elite Account Number :
                                                            <!-- <span style="color: green;"> $customerOvd['dob'] </span> -->
                                                            <span>
                                                                {{$customerOvd['elite_account_number']}}
                                                            </span>
                                                        </div>
                                                        <div class="detaisl-right">
                                                           
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif


                                                @if($customerOvd['customer_account_type'] == 3)
                                                @if(($is_review == 1) && (!isset($reviewDetails['empno-'.$i])))
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
                                                            HRMS Number :
                                                            <!-- <span style="color: green;"> $customerOvd['dob'] </span> -->
                                                            <span>
                                                                {{$customerOvd['empno']}}
                                                                @if(count($qcReviewDetails) > 0)
                                                                    @if(isset($qcReviewDetails['empno-'.$i]))
                                                                        <span class="review-comment">
                                                                            <i class="fa fa-exclamation review-exclamation"></i>
                                                                         {{$qcReviewDetails['empno-'.$i]}}
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
                                                                        <input type="checkbox" name="empno_toggle-{{$i}}" class="mobileToggle reviewComments" id="empno_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                        <label for="empno_toggle-{{$i}}"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="comments-blck {{$display}}">
                                                                    <input type="text" class="form-control commentsField" id="empno-{{$i}}">
                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                </div>
                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif



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
