<div class="card" id="risk-details">
    <div class="card-block">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="sub-title">CIDD Account Information</h4>
            </div>
        </div>
        <div class="row risk-details-multi-applican mt-2">
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
                                        @if((isset($checkvisibleciid['data'][$i]['otheroccupation']) && $checkvisibleciid['data'][$i]['otheroccupation'] == 'Y') || (isset($checkvisibleciid['data'][$i]['sourceoffund']) && $checkvisibleciid['data'][$i]['sourceoffund'] == 'Y') || (isset($checkvisibleciid['data'][$i]['grossincome']) && $checkvisibleciid['data'][$i]['grossincome'] == 'Y'))
                                        @if($i == 1)
                                            <a href="#reviewrisk-tab{{$i}}" class="nav-link">{{ $firstTab }}</a>
                                        @else
                                            <a href="#reviewrisk-tab{{$i}}"  class="nav-link">{{ $secondTab }}</a>
                                        @endif
                                    @endif
                                </li>
                        @endfor
                    </ul>
                    <div id="reviewrisk-tabs-content-cust" class="reviewrisk-tabs-content-cust">
                            <div class="card" id="risk_classfication">
                                <div class="card-block">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <h4 class="sub-title">CIDD Customer Information</h4>
                                        @for($i=1;$i<=$accountHoldersCount;$i++)
                                        
                                        @php
                                            if ($accountDetails['constitution'] == 'NON_IND_HUF' && $i == 2) {
                                                $is_huf_display = true;
                                            }else{
                                                $is_huf_display = false;
                                            }
                                        @endphp
                                    <div id="reviewrisk-tab{{$i}}" class="reviewrisk-tab-content-cust">
                                                <div class="row">
                                                   
                                                    @if(isset($checkvisibleciid['data'][$i]['grossincome']) && $checkvisibleciid['data'][$i]['grossincome'] == 'Y')
                                                        <div class="row">
                                                    <div class="col-md-6">
                                                                @if((($is_review == 1) && (!isset($reviewDetails['gross_income-'.$i]) && (!isset($reviewDetails['gross_income-'.$i])))))
                                                            @php
                                                            $checked = "checked";
                                                                $display = "display-none";
                                                                $disabled = 'disabled';
                                                            @endphp
                                                        @else
                                                        @php
                                                                if($riskDetails[$i-1]->is_new_customer != 0){
                                                                $checked = "";
                                                                $display = "";
                                                                $disabled = '';
                                                                }else{
                                                                    $checked = "checked";
                                                                    $display = "display-none";
                                                                    $disabled = 'disabled';
                                                                }
                                                                @endphp
                                                        @endif
                                                        <div class="details-custcol-row">
                                                            <div class="details-custcol-row-top d-flex">
                                                                <div class="detaisl-left d-flex align-items-center" style="width:84%";>
                                                                                Gross Income :
                                                                        <span>
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
                                                        </div>
                                                    @endif

                                                    @if(isset($checkvisibleciid['data'][$i]['sourceoffund']) && $checkvisibleciid['data'][$i]['sourceoffund'] == 'Y')
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                @if((($is_review == 1) && (!isset($reviewDetails['source_of_funds-'.$i]) && (!isset($reviewDetails['source_of_funds-'.$i])))))
                                                                    @php
                                                                        $checked = "checked";
                                                                        $display = "display-none";
                                                                        $disabled = 'disabled';
                                                                    @endphp
                                                                @else
                                                                    @php
                                                                        if($riskDetails[$i-1]->is_new_customer != 0){
                                                                        $checked = "";
                                                                        $display = "";
                                                                        $disabled = '';
                                                                        }else{
                                                                            $checked = "checked";
                                                                            $display = "display-none";
                                                                            $disabled = 'disabled';
                                                                        }
                                                                        @endphp
                                                                @endif
                                                                <div class="details-custcol-row">
                                                                    <div class="details-custcol-row-top d-flex">
                                                                        <div class="detaisl-left d-flex align-items-center" style="width:84%";>
                                                                            Source of Funds :
                                                                            <span>
                                                                                {{strtoupper($riskDetails[$i-1]->source_of_funds)}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['source_of_funds-'.$i]))
                                                                                    <span class="review-comment">
                                                                                        <i class="fa fa-exclamation review-exclamation"></i>
                                                                                            {{$qcReviewDetails['source_of_funds-'.$i]}}
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
                                                                                    <input type="checkbox" name="source_of_funds_toggle-{{$i}}" class="mobileToggle reviewComments" id="source_of_funds_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="source_of_funds_toggle-{{$i}}"></label>
                                                                                </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="source_of_funds-{{$i}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif

                                              
                                                    @if($is_huf)
                                                    
                                                    @if(isset($checkvisibleciid['data'][$i]['networth']) && $checkvisibleciid['data'][$i]['networth'] == 'Y')
                                                    
                                                <div class="row">
                                                    <div class="col-md-6">
                                                            @if((($is_review == 1) && (!isset($reviewDetails['networth-'.$i]) && (!isset($reviewDetails['networth-'.$i])))))
                                                                @php
                                                                    $checked = "checked";
                                                                    $display = "display-none";
                                                                    $disabled = 'disabled';
                                                                @endphp
                                                            @else
                                                                @php
                                                                    if($riskDetails[$i-1]->is_new_customer != 0){
                                                                    $checked = "";
                                                                    $display = "";
                                                                    $disabled = '';
                                                                    }else{
                                                                        $checked = "checked";
                                                                        $display = "display-none";
                                                                        $disabled = 'disabled';
                                                                    }
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
                                                                    <div class="detaisl-right">
                                                                        <div class=" d-flex flex-row">
                                                                            <div class="switch-blck">
                                                                                <div class="toggleWrapper">
                                                                                    <input type="checkbox" name="networth_toggle-{{$i}}" class="mobileToggle reviewComments" id="networth_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="networth_toggle-{{$i}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="networth-{{$i}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                                  

                                                    @if((isset($checkvisibleciid['data'][$i]['otheroccupation']) && $checkvisibleciid['data'][$i]['otheroccupation'] == 'Y'))
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                        @if((($is_review == 1) && (!isset($reviewDetails['occupation-'.$i]) && (!isset($reviewDetails['other_occupation-'.$i])))))
                                                            @php
                                                            $checked = "checked";
                                                                $display = "display-none";
                                                                $disabled = 'disabled';
                                                            @endphp
                                                        @else
                                                        @php
                                                                if($riskDetails[$i-1]->is_new_customer != 0){
                                                                $checked = "";
                                                                $display = "";
                                                                $disabled = '';
                                                                }else{
                                                                    $checked = "checked";
                                                                    $display = "display-none";
                                                                    $disabled = 'disabled';
                                                                }
                                                                @endphp
                                                        @endif
                                                        <div class="details-custcol-row">
                                                            <div class="details-custcol-row-top d-flex">
                                                                        <div class="detaisl-left align-items-center" style="width:84%";>
                                                                            {{ $is_huf_display ? 'Nature of Business :' : 'Occupation :' }}
                                                                        <span>
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
                                                                            </span>
                                                                        </div>
                                                                        <div class="detaisl-right">
                                                                    <div class=" d-flex flex-row">
                                                                        <div class="switch-blck">
                                                                            <div class="toggleWrapper">
                                                                    <input type="checkbox" name="occupation_toggle-{{$i}}" class="mobileToggle reviewComments" id="occupation_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                                    <label for="occupation_toggle-{{$i}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="occupation-{{$i}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                @endif

                                @if((isset($checkvisibleciid['data'][$i]['otheroccupationcomment']) && $checkvisibleciid['data'][$i]['otheroccupationcomment'] == 'Y'))
                                <div class="row">
                                    <div class="col-md-6">
                                            @if((($is_review == 1) && (!isset($reviewDetails['other_occupation-'.$i]))))
                                            @php
                                                $checked = "checked";
                                                $display = "display-none";
                                                $disabled = 'disabled';
                                            @endphp
                                        @else
                                            @php
                                                if($riskDetails[$i-1]->is_new_customer != 0){
                                                $checked = "";
                                                $display = "";
                                                $disabled = '';
                                                }else{
                                                    $checked = "checked";
                                                    $display = "display-none";
                                                    $disabled = 'disabled';
                                                }
                                            @endphp
                                        @endif
                                        <div class="details-custcol-row">
                                            <div class="details-custcol-row-top d-flex">
                                                <div class="detaisl-left d-flex align-items-center">
                                                                            {{ $is_huf_display ? 'Other Business :' : 'Other Occupations :' }}
                                                    <span>
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
                                                    </span>
                                                </div>
                                                <div class="detaisl-right">
                                                    <div class=" d-flex flex-row">
                                                        <div class="switch-blck">
                                                <div class="toggleWrapper">
                                                    <input type="checkbox" name="other_occupation_toggle-{{$i}}" class="mobileToggle reviewComments" id="other_occupation_toggle-{{$i}}" {{$checked}} {{$disabled}}>
                                                    <label for="other_occupation_toggle-{{$i}}"></label>
                                                </div>
                                            </div>
                                            <div class="comments-blck {{$display}}">
                                                <input type="text" class="form-control commentsField" id="other_occupation-{{$i}}">
                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                            </div>
                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                        </div>
                                        </div>
                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                </div>
                                </div>
                            @endfor	
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