<div class="card" id="risk-details">
    <div class="card-block">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="sub-title">CIDD Account Information</h4>
                <!-- Row start -->
            @if(in_array($roleId,[5,6]))
                <div class="risk-details-blck">
                    <input type="hidden" id="formId" value="{{$formId}}">
                        <div class="row">
                            <div class="custom-col-review risk-details col-md-6">
                                <!-- <h4>Verify Identity Details</h4> -->
                                <div class="details-custcol">
                                    @if(($is_review == 1) && (!isset($riskDetails[0]->annual_turnover)))
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
                                                Annual Turnover  (₹) :
                                                <span>
                                                    {{strtoupper(config('constants.ANNUAL_TURNOVER.'.$riskDetails[0]->annual_turnover))}}
                                                    @if(count($qcReviewDetails) > 0)
                                                        @if(isset($qcReviewDetails['annual_turnover']))
                                                            <span class="review-comment">
                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                             {{$qcReviewDetails['annual_turnover']}}
                                                            </span>
                                                        @else
                                                            <i class="fa fa-check"></i>
                                                        @endif
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-col-review risk-details col-md-6">
                                <!-- <h4>Verify Identity Details</h4> -->
                                <div class="details-custcol">
                                    @if(($is_review == 1) && (!isset($riskDetails[0]->source_of_funds)))
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
                                                Source Of Funds :
                                                <span>
                                                    {{strtoupper($riskDetails[0]->source_of_funds)}}
                                                    @if(count($qcReviewDetails) > 0)
                                                        @if(isset($qcReviewDetails['source_of_funds']))
                                                            <span class="review-comment">
                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                             {{$qcReviewDetails['source_of_funds']}}
                                                            </span>
                                                        @else
                                                            <i class="fa fa-check"></i>
                                                        @endif
                                                    @endif
                                               </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-col-review risk-details col-md-6">
                                <!-- <h4>Verify Identity Details</h4> -->
                                <div class="details-custcol">
                                    @if(($is_review == 1) && (!isset($riskDetail[0]['expected_transactions'])))
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
                                                Expected Transactions :
                                                <span>
                                                    {{strtoupper(config('constants.EXPECTED_TRANSACTION.'.$riskDetails[0]->expected_transactions))}}
                                                    @if(count($qcReviewDetails) > 0)
                                                        @if(isset($qcReviewDetails['expected_transactions']))
                                                            <span class="review-comment">
                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                             {{$qcReviewDetails['expected_transactions']}}
                                                            </span>
                                                        @else
                                                            <i class="fa fa-check"></i>
                                                        @endif
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="custom-col-review risk-details col-md-6">
                                <!-- <h4>Verify Identity Details</h4> -->
                                <div class="row">
                                    <div class="col">
                                        <div class="details-custcol">
                                            @if(($is_review == 1) && (!isset($riskDetails[0]->inward_outward)))
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
                                                        Inward Outward :
                                                        <span>
                                                            @if($riskDetails[0]->inward_outward == 0)
                                                               No
                                                            @else
                                                              Yes
                                                            @endif

                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['inward_outward']))
                                                                    <span class="review-comment">
                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['inward_outward']}}
                                                            </span>
                                                                @else
                                                                    <i class="fa fa-check"></i>
                                                                @endif
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($riskDetails[0]->inward_outward == 0)
                                        <div class="col display-none">
                                    @else
                                        <div class="col">
                                    @endif
                                        <div class="details-custcol">
                                            @if(($is_review == 1) && (!isset($riskDetails[0]->approximate_value)))
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
                                                        Approximate Value (₹)
                                                        <span>
                                                            {{strtoupper(config('constants.APROXIMATE_VALUE.'.$riskDetails[0]->approximate_value))}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['approximate_value']))
                                                                    <span class="review-comment">
                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['approximate_value']}}
                                                            </span>
                                                                @else
                                                                    <i class="fa fa-check"></i>
                                                                @endif
                                                            @endif
                                                        </span>
                                                    </div>
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
                            <!-- Row end -->
                        </div>
                        <div class="row risk-details-multi-applican mt-5">
                            <div class="col-lg-12">
                                  <div class="tabs">
                                    <ul id="reviewrisk-tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb">
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
                                            $is_huf = false;
                                            if($accountDetails['constitution'] == 'NON_IND_HUF'){
                                                $is_huf = true;
                                            }
                                            $firstTab = $is_huf ? 'Karta/Manager' : 'Primary Account Holder';
                                            $secondTab = $is_huf ? 'HUF' : 'Applicant'.$i;
                                    @endphp

                                            <li class="nav-item {{$class}}">
                                                @if($i == 1)
                                                    <a href="#reviewrisk-tab{{$i}}" class="nav-link">{{ $firstTab }}</a>
                                                @else
                                                    <a href="#reviewrisk-tab{{$i}}"  class="nav-link">{{ $secondTab }}</a>
                                                @endif
                                            </li>
                                        @endfor
                                    </ul>
                                    <div id="reviewrisk-tabs-content-cust" class="reviewrisk-tabs-content-cust">
                                        @for($i = 1;$i<=$accountHoldersCount;$i++)

                                        @php
                                        if ($accountDetails['constitution'] == 'NON_IND_HUF' && $i == 2) {
                                            $is_huf_display = true;
                                        }else{
                                            $is_huf_display = false;
                                        }
                                        @endphp
                                            <div id="reviewrisk-tab{{$i}}" class="reviewrisk-tab-content-cust">
                                                <div class="card" id="customer_on_boarding">
                                                    <div class="card-block">
                                                        <div class="row">
                                                            <div class="col-lg-12">
                                                                <h4 class="sub-title">CIDD Customer Information</h4>
                                                                <!-- Row start -->
                                                                 <div class="row">
                                                                    @if (!$is_huf_display)
                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['education-'.$i]))) || ($accountDetails['customer_id'] != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                      Education :
                                                                                    <span>
                                                                                        {{strtoupper(config('constants.EDUCATION.'.$riskDetails[$i-1]->education))}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['education-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['education-'.$i]}}
                                                                                                 </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                 </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>
                                                                     @endif
                                                                     
                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['customer_type-'.$i]))) || ($riskDetails[$i-1]->customer_type != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        Customer Type :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->customer_type)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['customer_type-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['customer_type-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                     <div class="col-md-6">
                                                                        @if(($is_review == 1) && (!isset($reviewDetails['gross_income-'.$i])) && (!in_array($roleId,[5,6])) && isset($customerOvdDetails[$i-1]->is_new_customer) && $customerOvdDetails[$i-1]->is_new_customer)
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

                                                                        @php
                                                                        if(isset($checkvisibleciid['data'][$i]['grossincome']) && $checkvisibleciid['data'][$i]['grossincome'] == 'Y'){
                                                                                $checked = "";
                                                                                $display = "";
                                                                                $disabled = '';
                                                                            }else{

                                                                                $checked = "checked";
                                                                                $display = "display-none";
                                                                                $disabled = 'disabled';
                                                                            }
                                                                        @endphp
                                                                        <div class="details-custcol-row">
                                                                            <div class="details-custcol-row-top d-flex">
                                                                                <div class="detaisl-left d-flex align-items-center">
                                                                                        Gross Income :
                                                                                    <span>
                                                                                        <!-- {{strtoupper(config('constants.GROSS_INCOME.'.$riskDetails[$i-1]->gross_income))}} -->
                                                                                        {{strtoupper($riskDetails[$i-1]->gross_income)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['gross_income-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['gross_income-'.$i]}}
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
                                                                                                <input type="checkbox" name="gross_income_toggle-{{$i}}" class="mobileToggle reviewComments" id="gross_income_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                                                <label for="gross_income_toggle-{{$i}}"></label>
                                                                            </div>
                                                                        </div>
                                                                                        <div class="comments-blck {{$display}}">
                                                                                            <input type="text" class="form-control commentsField" id="gross_income-{{$i}}">
                                                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                     </div>
                                                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['networth-'.$i]))) || ($riskDetails[$i-1]->networth != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        Networth :
                                                                                    <span>
                                                                                        {{strtoupper(config('constants.NETWORTH.'.$riskDetails[$i-1]->networth))}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['networth-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                 <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['networth-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                   </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>


                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['occupation-'.$i]))) || ($riskDetails[$i-1]->occupation != '') && (!in_array(Session::get('role'),[5,6])))
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

                                                                        <?php
                                                                            $customer_type = isset($customerOvdDetails[$i-1]->is_new_customer) && $customerOvdDetails[$i-1]->is_new_customer != ''?$customerOvdDetails[$i-1]->is_new_customer:'';
                                                                            //  echo "<pre>";print_r($customer_type);exit; 
                                                                         ?>
                                                                        <div class="details-custcol-row">
                                                                            <div class="details-custcol-row-top d-flex">
                                                                                <div class="detaisl-left d-flex align-items-center">
                                                                                        {{ $is_huf_display ? 'Nature of Business :' : 'Occupation :' }}
                                                                                    <span>
                                                                                        @if($customer_type != 0)
                                                                                        {{strtoupper($riskDetails[$i-1]->occupation)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['occupation-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['occupation-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['other_occupation-'.$i]))) || ($riskDetails[$i-1]->other_occupation != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        {{ $is_huf_display ? ' Other Business :' : ' Other Occupation :' }}
                                                                                    <span>
                                                                                        @if($customer_type != 0)
                                                                                        {{strtoupper($riskDetails[$i-1]->other_occupation)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['other_occupation-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['other_occupation-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>



                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['pep-'.$i]))) || ($riskDetails[$i-1]->pep != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        PEP :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->pep)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['pep-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['pep-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>


                                                                 </div>
                                                            </div>
                                                            <!-- Row end -->
                                                        </div>

                                                        <!-- Information Related to FATCA Compliance Start here -->
                                                        <div class="row mt-5">
                                                            <div class="col-lg-12">
                                                                <h4 class="sub-title">Information Related to FATCA Compliance</h4>
                                                                <!-- Row start -->
                                                                 <div class="row">
                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['country_name-'.$i]))) || ($riskDetails[$i-1]->country_name != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                      Current Residency :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->country_name)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['country_name-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                    <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['country_name-'.$i]}}
                                                                                                 </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>


                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['country_of_birth-'.$i]))) || ($riskDetails[$i-1]->country_of_birth != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                      Country of Birth :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->country_of_birth)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['country_of_birth-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                    <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['country_of_birth-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['citizenship-'.$i]))) || ($riskDetails[$i-1]->citizenship != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        Citizenship :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->citizenship)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['citizenship-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                    <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['citizenship-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['place_of_birth-'.$i]))) || ($riskDetails[$i-1]->place_of_birth != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        Place of Birth :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->place_of_birth)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['place_of_birth-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                 <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['place_of_birth-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['residence-'.$i]))) || ($riskDetails[$i-1]->residence != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        Residence for Tax Purpose :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->residence)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['residence-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                   <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['residence-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                     <div class="col-md-6">
                                                                        <div class="row">
                                                                            <div class="col">
                                                                               @if((($is_review == 1) && (!isset($reviewDetails['us_person-'.$i]))) || ($riskDetails[$i-1]->us_person != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        Us Person :
                                                                                    <span>
                                                                                    @if($riskDetails[$i-1]->us_person == 0)
                                                                                          No
                                                                                    @else
                                                                                        Yes
                                                                                    @endif

                                                                                    @if(count($qcReviewDetails) > 0)
                                                                                        @if(isset($qcReviewDetails['us_person-'.$i]))
                                                                                            <span class="review-comment">
                                                                                                <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                {{$qcReviewDetails['us_person-'.$i]}}
                                                                                             </span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        </div>
                                                                        @if($riskDetails[$i-1]->us_person == 0)
                                                                            <div class="col display-none">
                                                                        @else
                                                                            <div class="col">
                                                                        @endif
                                                                               @if((($is_review == 1) && (!isset($reviewDetails['tin-'.$i]))) || ($riskDetails[$i-1]->tin != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                        Tin Number :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->tin)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['tin-'.$i]))
                                                                                                <span class="review-comment">
                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['tin-'.$i]}}
                                                            </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
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
                                                        <!-- Categorization Start here -->
                                                        <div class="row mt-5">
                                                            <div class="col-lg-12">
                                                                <hr>
                                                                <!-- <h4 class="sub-title">Information Related to FATCA Compliance</h4> -->
                                                                <!-- Row start -->
                                                                 <div class="row">
                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['basis_categorisation-'.$i]))) || ($riskDetails[$i-1]->basis_categorisation != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                      Categorization :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->basis_categorisation)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['basis_categorisation-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['basis_categorisation-'.$i]}}
                                                                                              </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>


                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['lcr_category-'.$i]))) || ($riskDetails[$i-1]->lcr_category != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                    LCR Customer Type :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->lcr_category)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['lcr_category-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                    <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['lcr_category-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                     <div class="col-md-6">
                                                                         @if((($is_review == 1) && (!isset($reviewDetails['risk_classification_rating-'.$i]))) || ($riskDetails[$i-1]->risk_classification_rating != '') && (!in_array(Session::get('role'),[5,6])))
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
                                                                                    Risk Classification :
                                                                                    <span>
                                                                                        {{strtoupper($riskDetails[$i-1]->risk_classification_rating)}}
                                                                                        @if(count($qcReviewDetails) > 0)
                                                                                            @if(isset($qcReviewDetails['risk_classification_rating-'.$i]))
                                                                                                <span class="review-comment">
                                                                                                <i class="fa fa-exclamation review-exclamation"></i>
                                                                                                 {{$qcReviewDetails['risk_classification_rating-'.$i]}}
                                                                                                </span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>



                                                                 </div>
                                                            </div>
                                                            <!-- Row end -->
                                                        </div>
                                                        <!-- Categorization END here -->



                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>



<!-- <div class="tabs">
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

</div> -->
