

<div class="custom-col-review proof-of-identity col-md-8">
    <h4>Verify Tenure Details</h4>
    <div class="details-custcol mt-3">
        @if(($is_review == 1) && (!isset($reviewDetails['td_amount'])))
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
                   Tenure Amount :
                    <span>
                        {{$customerOvdDetails[0]->td_amount}}
                        @if(count($qcReviewDetails) > 0)
                            @if(isset($qcReviewDetails['td_amount']))
                                <span class="review-comment">
                                    <i class="fa fa-exclamation review-exclamation"></i>
                                    {{$qcReviewDetails['td_amount']}}
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
                                <input type="checkbox" name="td_amount_toggle" class="mobileToggle reviewComments" id="td_amount_toggle" {{$checked}} {{$disabled}}>
                                <label for="td_amount_toggle"></label>
                            </div>
                        </div>
                        <div class="comments-blck {{$display}}">
                            <input type="text" class="form-control commentsField" id="td_amount">
                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                        </div>
                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                    </div>
                </div>
            </div>
        </div>

        @if(($is_review == 1) && (!isset($reviewDetails['years'])))
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
                   Tenure Years :
                    <span>
                        {{$customerOvdDetails[0]->years}}
                        @if(count($qcReviewDetails) > 0)
                            @if(isset($qcReviewDetails['years']))
                                <span class="review-comment">
                                    <i class="fa fa-exclamation review-exclamation"></i>
                                    {{$qcReviewDetails['years']}}
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
                                <input type="checkbox" name="years_toggle" class="mobileToggle reviewComments" id="years_toggle" {{$checked}} {{$disabled}}>
                                <label for="years_toggle"></label>
                            </div>
                        </div>
                        <div class="comments-blck {{$display}}">
                            <input type="text" class="form-control commentsField" id="years">
                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                        </div>
                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                    </div>
                </div>
            </div>
        </div>

        @if(($is_review == 1) && (!isset($reviewDetails['months'])))
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
                   Tenure Months :
                    <span>
                        {{$customerOvdDetails[0]->months}}
                        @if(count($qcReviewDetails) > 0)
                            @if(isset($qcReviewDetails['months']))
                                <span class="review-comment">
                                    <i class="fa fa-exclamation review-exclamation"></i>
                                    {{$qcReviewDetails['months']}}
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
                                <input type="checkbox" name="months_toggle" class="mobileToggle reviewComments" id="months_toggle" {{$checked}} {{$disabled}}>
                                <label for="months_toggle"></label>
                            </div>
                        </div>
                        <div class="comments-blck {{$display}}">
                            <input type="text" class="form-control commentsField" id="months">
                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                        </div>
                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                    </div>
                </div>
            </div>
        </div>

        @if(($is_review == 1) && (!isset($reviewDetails['days'])))
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
                   Tenure Days :
                    <span>
                        {{$customerOvdDetails[0]->days}}
                        @if(count($qcReviewDetails) > 0)
                            @if(isset($qcReviewDetails['days']))
                                <span class="review-comment">
                                    <i class="fa fa-exclamation review-exclamation"></i>
                                    {{$qcReviewDetails['days']}}
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
                                <input type="checkbox" name="days_toggle" class="mobileToggle reviewComments" id="days_toggle" {{$checked}} {{$disabled}}>
                                <label for="days_toggle"></label>
                            </div>
                        </div>
                        <div class="comments-blck {{$display}}">
                            <input type="text" class="form-control commentsField" id="days">
                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                        </div>
                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
            
