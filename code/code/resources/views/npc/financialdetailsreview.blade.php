@php
    $customerOvd = (array) current($customerOvdDetails);
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";


    $is_huf_display = false;
            if($accountDetails['constitution'] == 'NON_IND_HUF'){
                $is_huf_display = true;
            }
@endphp
@if(in_array($customerOvd['initial_funding_type'],[1,2]))
    <div class="card" id="initial_funding">
        <div class="card-block">
            <!-- Row start -->
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="sub-title">Initial Funding</h4>
                    <div class="row">
                        @if(isset($customerOvd['cheque_image']) && $customerOvd['initial_funding_type'] == 1)
                            <div class="custom-col-review col-md-4">
                                <div class="form-group">
                                    <div class="proof-of-identity">
                                         <div class="row" style="margin-bottom: 8px;">
                                        <h4>{{config('constants.INITIAL_FUNDING_TYPE.'.$customerOvdDetails[0]->initial_funding_type)}}</h4>
                                                                         <!-- 22May23 - For BS5 - commented below line -->
                                                                        <!-- <button id="rotate" class="rotate" style="margin-left: 356px;"><i class="fa fa-rotate-right"></i></button> -->
                                                                       <button id="rotate" class="rotate col-sm-1" style="margin-left:356px"><i class="fa fa-rotate-right"></i></button>
                                                                    </div>

                                        <div class="uploaded-img-ovd">
                                            @if(substr($customerOvdDetails[0]->cheque_image,0,11) == "_DONOTSIGN_")
                                                @php
                                                    $cheque_image = $customerOvdDetails[0]->cheque_image;
                                                @endphp
                                            @else
                                                @php
                                                    $cheque_image = '_DONOTSIGN_'.$customerOvdDetails[0]->cheque_image;
                                                @endphp
                                            @endif
                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                            <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$cheque_image) }}" class="img-fluid ovd_image rotate_image">
                                        </div>
                                        </div>
                                        @if(($is_review == 1) && (!isset($reviewDetails['cheque_image'])))
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
                                                    @if(isset($qcReviewDetails['cheque_image']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['cheque_image']}}
                                                        </span>
                                                    @else
                                                        <i class="fa fa-check"></i>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class=" d-flex flex-row">
                                                <div class="switch-blck" style="margin-right: 20px;">
                                                    <div class="toggleWrapper">
                                                        <input type="checkbox" name="cheque_image_toggle" class="mobileToggle reviewComments" id="cheque_image_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="cheque_image_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}" style="width:100%;">
                                                    <input type="text" class="form-control commentsField" id="cheque_image" name="cheque_image">
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
                            <h4>Verify Initial Funding Details</h4>
                            <div class="details-custcol">
                                @if(($is_review == 1) && (!isset($reviewDetails['initial_funding_type'])))
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
                                    <div class="details-custcol-row-top d-flex mt-3">
                                        <div class="detaisl-left d-flex align-items-center">
                                            Initial Funding Type :
                                            <span>
                                                {{strtoupper(config('constants.INITIAL_FUNDING_TYPE.'.$customerOvdDetails[0]->initial_funding_type))}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['initial_funding_type']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['initial_funding_type']}}
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
                                                        <input type="checkbox" name="initial_funding_type_toggle" class="mobileToggle reviewComments" id="initial_funding_type_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="initial_funding_type_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}">
                                                    <input type="text" class="form-control commentsField" id="initial_funding_type">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div> -->
                                    </div>
                                </div>
                                @if(($is_review == 1) && (!isset($reviewDetails['initial_funding_date'])))
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
                                            Date :
                                            <span>
                                                {{strtoupper(Carbon\Carbon::parse($customerOvdDetails[0]->initial_funding_date)->format('d-M-Y'))}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['initial_funding_date']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['initial_funding_date']}}
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
                                                        <input type="checkbox" name="initial_funding_date_toggle" class="mobileToggle reviewComments" id="initial_funding_date_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="initial_funding_date_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}">
                                                    <input type="text" class="form-control commentsField" id="initial_funding_date">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(($is_review == 1) && (!isset($reviewDetails['amount'])))
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
                                            Amount :
                                            <span>
                                                {{$customerOvdDetails[0]->amount}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['amount']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['amount']}}
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
                                                        <input type="checkbox" name="amount_toggle" class="mobileToggle reviewComments" id="amount_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="amount_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}">
                                                    <input type="text" class="form-control commentsField" id="amount">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(($is_review == 1) && (!isset($reviewDetails['reference'])))
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
                                            Neft/Reference #:
                                            <span>
                                                {{strtoupper($customerOvdDetails[0]->reference)}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['reference']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['reference']}}
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
                                                         <input type="checkbox" name="reference_toggle" class="mobileToggle reviewComments" id="reference_toggle" {{$checked}} {{$disabled}}>
                                                         <label for="reference_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}">
                                                    <input type="text" class="form-control commentsField" id="reference">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(($is_review == 1) && (!isset($reviewDetails['bank_name'])))
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
                                            Bank Name :
                                            <span>
                                                {{strtoupper($customerOvdDetails[0]->bank_name)}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['bank_name']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['bank_name']}}
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
                                                        <input type="checkbox" name="bank_name_toggle" class="mobileToggle reviewComments" id="bank_name_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="bank_name_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}">
                                                    <input type="text" class="form-control commentsField" id="bank_name">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(($is_review == 1) && (!isset($reviewDetails['ifsc_code'])))
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
                                            IFSC Code :
                                            <span>
                                                {{strtoupper($customerOvdDetails[0]->ifsc_code)}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['ifsc_code']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['ifsc_code']}}
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
                                                        <input type="checkbox" name="ifsc_code_toggle" class="mobileToggle reviewComments" id="ifsc_code_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="ifsc_code_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}">
                                                    <input type="text" class="form-control commentsField" id="ifsc_code">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(($is_review == 1) && (!isset($reviewDetails['account_number'])))
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
                                            Account # :
                                            <span>
                                                {{strtoupper($customerOvdDetails[0]->account_number)}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['account_number']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['account_number']}}
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
                                                        <input type="checkbox" name="account_number_toggle" class="mobileToggle reviewComments" id="account_number_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="account_number_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}">
                                                    <input type="text" class="form-control commentsField" id="account_number">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if(($is_review == 1) && (!isset($reviewDetails['account_name'])))
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
                                            Account Name:
                                            <span>
                                                {{strtoupper($customerOvdDetails[0]->account_name)}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['account_name']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['account_name']}}
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
                                                        <input type="checkbox" name="account_name_toggle" class="mobileToggle reviewComments" id="account_name_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="account_name_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}">
                                                    <input type="text" class="form-control commentsField" id="account_name">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    @if($customerOvdDetails[0]->initial_funding_type == 1 || $customerOvdDetails[0]->initial_funding_type == 2)
                                        @if(($is_review == 1) && (!isset($reviewDetails['self_thirdparty'])))
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
                                                {{ $is_huf_display ? 'HUF/Third party :' : 'Self/Third party :' }}
                                                    <span>
                                                        @if($is_huf_display && $customerOvdDetails[0]->self_thirdparty == 'self')
                                                        HUF
                                                        @else
                                                        {{strtoupper($customerOvdDetails[0]->self_thirdparty)}}
                                                        @endif
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['self_thirdparty']))
                                                                <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['self_thirdparty']}}
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
                                                                <input type="checkbox" name="self_thirdparty_toggle" class="mobileToggle reviewComments" id="self_thirdparty_toggle" {{$checked}} {{$disabled}}>
                                                                <label for="self_thirdparty_toggle"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="self_thirdparty">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                

                                @if($customerOvd['self_thirdparty'] == "thirdparty")
                                    @if($customerOvdDetails[0]->initial_funding_type == 4 || $customerOvdDetails[0]->initial_funding_type == 1 || $customerOvdDetails[0]->initial_funding_type == 2)
                                        @if(($is_review == 1) && (!isset($reviewDetails['relationship'])))
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
                                                    Relationship :
                                                    <span>
                                                        {{strtoupper($customerOvdDetails[0]->relationship)}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['relationship']))
                                                                <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['relationship']}}
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
                                                                <input type="checkbox" name="relationship_toggle" class="mobileToggle reviewComments" id="relationship_toggle" {{$checked}} {{$disabled}}>
                                                                <label for="relationship_toggle"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="relationship">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @if(in_array($accountDetails['account_type'],['Term Deposit','Savings & TD']))
                        @include('npc.tenurereviewfield')
                        @endif
                    </div>
               </div>
            </div>
            <!-- Row end -->
        </div>
    </div>
@else
<div class="card" id="initial_funding">
    <div class="card-block">
         <!-- Row start -->
           <div class="row">
                <div class="col-lg-12">
                    <h4 class="sub-title">Initial Funding</h4>
                    <div class="row">
                    <div class="custom-col-review proof-of-identity col-md-8">
                            <h4>Verify Initial Funding Details</h4>
                            <div class="details-custcol mt-3">
                                @if(($is_review == 1) && (!isset($reviewDetails['initial_funding_type'])))
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
                                            Initial Funding Type :
                                            <span>
                                                {{strtoupper(config('constants.INITIAL_FUNDING_TYPE.'.$customerOvdDetails[0]->initial_funding_type))}}
                                                @if(count($qcReviewDetails) > 0)
                                                    @if(isset($qcReviewDetails['initial_funding_type']))
                                                        <span class="review-comment">
                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                         {{$qcReviewDetails['initial_funding_type']}}
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
