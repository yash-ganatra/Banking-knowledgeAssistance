@php
    $showWitnessDeclaration = false;
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
@endphp
@if(count($nomineeDetails) > 0)
    @for($k = 1;$k <= count($nomineeDetails);$k++)
        @php
            $nominee = (array) $nomineeDetails[$k-1];
        @endphp
        <div class="card" id="nominee_details">
            <div class="card-block">
                <!-- Row start -->
                <div class="row">
                    <div class="col-lg-12">
                        @if($k == 1)
                            <h4 class="sub-title">Nominee Details</h4>
                        @else
                            <h4 class="sub-title">TD Nominee Details</h4>
                        @endif
                        @if($nominee['nominee_exists'] == "yes")
                            <div class="row">
                                @if($nominee['nominee_age'] < 18)
                                    @if(($is_review == 1) && (!isset($reviewDetails['witness1_signature_image'])))
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
                                    @if($showWitnessDeclaration)
                                    <div class="custom-col-review col-md-4">
                                        <div class="form-group">
                                            <div class="proof-of-identity">
                                            <div class="row" style="margin-bottom: 8px;">

                                                <h4>Witness Declaration</h4>
                                                
                                                            <!-- 22May23 - For BS5 - commented below line -->
                                                                        <!-- <button id="rotate" class="rotate" style="margin-left: 356px;"><i class="fa fa-rotate-right"></i></button> -->
                                                                        <button id="rotate" class="rotate col-sm-1" style="margin-left: 356px;"><i class="fa fa-rotate-right"></i></button>
                                                                    </div>
                                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                    <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$nominee['witness1_signature_image']) }}" class="img-fluid ovd_image rotate_image">
                                                </div>
                                                <div class="detaisl-right" style="margin-top:15px; width:100%;">
                                                    <div class=" d-flex flex-row">
                                                        <div class="switch-blck" style="margin-right: 20px;">
                                                            <div class="toggleWrapper">
                                                                <input type="checkbox" name="witness1_signature_toggle-{{$k}}" class="mobileToggle reviewComments" id="witness1_signature_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="witness1_signature_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}" style="width:100%;">
                                                            <input type="text" class="form-control commentsField" id="witness1_signature" name="witness1_signature">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endif
                                <div class="custom-col-review proof-of-identity col-md-8">
                                    <h4>Verify Nominee Details</h4>
                                    <div class="details-custcol">
                                        @if(($is_review == 1) && (!isset($reviewDetails['nominee_name-'.$k])))
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
                                                    Nominee Name :
                                                    <span>
                                                        {{strtoupper($nominee['nominee_name'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_name-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_name-'.$k]}}
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
                                                                <input type="checkbox" name="nominee_name_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_name_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="nominee_name_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_name-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if(($is_review == 1) && (!isset($reviewDetails['relationship-'.$k])))
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
                                                        {{strtoupper($nominee['relatinship_applicant'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['relationship-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['relationship-'.$k]}}
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
                                                                <input type="checkbox" name="relatinship_applicant_toggle-{{$k}}" class="mobileToggle reviewComments" id="relatinship_applicant_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="relatinship_applicant_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="relationship-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if(($is_review == 1) && (!isset($reviewDetails['nominee_address_line1-'.$k])))
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
                                                    Address Line1 :
                                                    <span>
                                                        {{strtoupper($nominee['nominee_address_line1'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_address_line1-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_address_line1-'.$k]}}
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
                                                                <input type="checkbox" name="nominee_address_line1_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_address_line1_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="nominee_address_line1_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_address_line1-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                         @if(($is_review == 1) && (!isset($reviewDetails['nominee_address_line2-'.$k])))
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
                                                    Address Line2 :
                                                    <span>
                                                        {{strtoupper($nominee['nominee_address_line2'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_address_line2-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_address_line2-'.$k]}}
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
                                                                <input type="checkbox" name="nominee_address_line2_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_address_line2_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="nominee_address_line2_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_address_line2-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if(($is_review == 1) && (!isset($reviewDetails['nominee_country-'.$k])))
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
                                                    Country :
                                                    <span>
                                                        {{strtoupper($nominee['nominee_country'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_country-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_country-'.$k]}}
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
                                                                <input type="checkbox" name="nominee_country_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_country_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="nominee_country_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_country-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(($is_review == 1) && (!isset($reviewDetails['nominee_state-'.$k])))
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
                                                    State :
                                                    <span>
                                                        {{strtoupper($nominee['nominee_state'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_state-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_state-'.$k]}}
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
                                                                <input type="checkbox" name="nominee_state_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_state_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="nominee_state_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_state-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(($is_review == 1) && (!isset($reviewDetails['nominee_city-'.$k])))
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
                                                    City :
                                                    <span>
                                                        {{strtoupper($nominee['nominee_city'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_city-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_city-'.$k]}}
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
                                                                <input type="checkbox" name="nominee_city_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_city_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="nominee_city_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_city-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(($is_review == 1) && (!isset($reviewDetails['nominee_pincode-'.$k])))
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
                                                    Pincode :
                                                    <span>
                                                        {{strtoupper($nominee['nominee_pincode'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_pincode-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_pincode-'.$k]}}
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
                                                                <input type="checkbox" name="nominee_pincode_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_pincode_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="nominee_pincode_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_pincode-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(($is_review == 1) && (!isset($reviewDetails['nominee_dob-'.$k])))
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
                                                    Nominee DOB:
                                                    <span>
                                                        {{strtoupper(Carbon\Carbon::parse($nominee['nominee_dob'])->format('d-M-Y'))}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_dob-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_dob-'.$k]}}
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
                                                                 <input type="checkbox" name="nominee_dob_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_dob_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                 <label for="nominee_dob_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_dob-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if(($is_review == 1) && (!isset($reviewDetails['nominee_age-'.$k])))
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
                                                    Nominee Age :
                                                    <span>
                                                        {{$nominee['nominee_age']}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['nominee_age-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['nominee_age-'.$k]}}
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
                                                                <input type="checkbox" name="nominee_age_toggle-{{$k}}" class="mobileToggle reviewComments" id="nominee_age_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="nominee_age_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="nominee_age-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div> -->
                                                </div>
                                            </div>
                                        </div>

                                        @if($nominee['nominee_age'] < 18)
                                            <h4 class="mt-4"> Guardian / Appointee Details</h4>
                                            @if(($is_review == 1) && (!isset($reviewDetails['guardian_name-'.$k])))
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
                                                        Guardian Name :
                                                        <span>
                                                            {{strtoupper($nominee['guardian_name'])}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['guardian_name-'.$k]))
                                                                <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['guardian_name-'.$k]}}
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
                                                                    <input type="checkbox" name="guardian_name_toggle-{{$k}}" class="mobileToggle reviewComments" id="guardian_name_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                    <label for="guardian_name_toggle-{{$k}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="guardian_name-{{$k}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(($is_review == 1) && (!isset($reviewDetails['relatinship_applicant_guardian-'.$k])))
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
                                                        Relationship with Nominee 
                                                        <span>
                                                            {{strtoupper($nominee['relatinship_applicant_guardian'])}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['relationship-'.$k]))
                                                                <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['relationship-'.$k]}}
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
                                                                    <input type="checkbox" name="relatinship_applicant_guardian_toggle-{{$k}}" class="mobileToggle reviewComments" id="relatinship_applicant_guardian_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                    <label for="relatinship_applicant_guardian_toggle-{{$k}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="relatinship_applicant_guardian-{{$k}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(($is_review == 1) && (!isset($reviewDetails['guardian_address_line1-'.$k])))
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
                                                        Guardian Address1 :
                                                        <span>
                                                            {{strtoupper($nominee['guardian_address_line1'])}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['guardian_address_line1-'.$k]))
                                                                <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['guardian_address_line1-'.$k]}}
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
                                                                    <input type="checkbox" name="guardian_address_line1_toggle-{{$k}}" class="mobileToggle reviewComments" id="guardian_address_line1_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                    <label for="guardian_address_line1_toggle-{{$k}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="guardian_address_line1-{{$k}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(($is_review == 1) && (!isset($reviewDetails['guardian_address_line2-'.$k])))
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
                                                        Guardian Address2 :
                                                        <span>
                                                            {{strtoupper($nominee['guardian_address_line2'])}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['guardian_address_line2-'.$k]))
                                                                <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['guardian_address_line2-'.$k]}}
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
                                                                    <input type="checkbox" name="guardian_address_line2_toggle-{{$k}}" class="mobileToggle reviewComments" id="guardian_address_line2_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                    <label for="guardian_address_line2_toggle-{{$k}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="guardian_address_line2-{{$k}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(($is_review == 1) && (!isset($reviewDetails['guardian_country-'.$k])))
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
                                                        Country :
                                                        <span>
                                                            {{strtoupper($nominee['guardian_country'])}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['guardian_country-'.$k]))
                                                                <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['guardian_country-'.$k]}}
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
                                                                    <input type="checkbox" name="guardian_country_toggle-{{$k}}" class="mobileToggle reviewComments" id="guardian_country_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                    <label for="guardian_country_toggle-{{$k}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="guardian_country-{{$k}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                             @if(($is_review == 1) && (!isset($reviewDetails['guardian_state-'.$k])))
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
                                                        State :
                                                        <span>
                                                            {{strtoupper($nominee['guardian_state'])}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['guardian_state-'.$k]))
                                                                <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['guardian_state-'.$k]}}
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
                                                                    <input type="checkbox" name="guardian_state_toggle-{{$k}}" class="mobileToggle reviewComments" id="guardian_state_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                    <label for="guardian_state_toggle-{{$k}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="guardian_state-{{$k}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(($is_review == 1) && (!isset($reviewDetails['guardian_city-'.$k])))
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
                                                        City :
                                                        <span>
                                                            {{strtoupper($nominee['guardian_city'])}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['guardian_city-'.$k]))
                                                                <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['guardian_city-'.$k]}}
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
                                                                    <input type="checkbox" name="guardian_city_toggle-{{$k}}" class="mobileToggle reviewComments" id="guardian_city_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                    <label for="guardian_city_toggle-{{$k}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="guardian_city-{{$k}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(($is_review == 1) && (!isset($reviewDetails['guardian_pincode-'.$k])))
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
                                                        Pincode :
                                                        <span>
                                                            {{strtoupper($nominee['guardian_pincode'])}}
                                                            @if(count($qcReviewDetails) > 0)
                                                                @if(isset($qcReviewDetails['guardian_pincode-'.$k]))
                                                                <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                     {{$qcReviewDetails['guardian_pincode-'.$k]}}
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
                                                                    <input type="checkbox" name="guardian_pincode_toggle-{{$k}}" class="mobileToggle reviewComments" id="guardian_pincode_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                    <label for="guardian_pincode_toggle-{{$k}}"></label>
                                                                </div>
                                                            </div>
                                                            <div class="comments-blck {{$display}}">
                                                                <input type="text" class="form-control commentsField" id="guardian_pincode-{{$k}}">
                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                            </div>
                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if(($is_review == 1) && (!isset($reviewDetails['name_as_per_passbook-'.$k])))
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
                                                    Nominee name to be printed on passbook, statement and DCA ?:
                                                    <span>
                                                        {{strtoupper($nominee['name_as_per_passbook'])}}
                                                        @if(count($qcReviewDetails) > 0)
                                                            @if(isset($qcReviewDetails['name_as_per_passbook-'.$k]))
                                                            <span class="review-comment">
                                                                  <i class="fa fa-exclamation review-exclamation"></i>
                                                                 {{$qcReviewDetails['name_as_per_passbook-'.$k]}}
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
                                                                <input type="checkbox" name="name_as_per_passbook_toggle-{{$k}}" class="mobileToggle reviewComments" id="name_as_per_passbook_toggle-{{$k}}" {{$checked}} {{$disabled}}>
                                                                <label for="name_as_per_passbook_toggle-{{$k}}"></label>
                                                            </div>
                                                        </div>
                                                        <div class="comments-blck {{$display}}">
                                                            <input type="text" class="form-control commentsField" id="name_as_per_passbook-{{$k}}">
                                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                        </div>
                                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="row">
                                <div class="custom-col-review col-md-4">
                                    Customer Comment: ( No, I/We do not wish to nominate anyone. )
                                </div>
                            </div>
                        @endif
                   </div>
                </div>
                <!-- Row end -->
            </div>
        </div>

        @if($nominee['lti_declaration_image'] != '')
            <div class="card" id="lti_decarations">
                <div class="card-block">
                    <!-- Row start -->
                    <div class="row">
                        <div class="col-lg-12">
                            @if($k == 1)
                                <h4 class="sub-title">TD LTI/RTI Declarations</h4>
                            @else
                                <h4 class="sub-title">LTI/RTI Declarations</h4>
                            @endif
                            <div class="row">
                                @if(($is_review == 1) && (!isset($reviewDetails['lti_declaration_image'])))
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
                                <div class="custom-col-review col-md-4">
                                    <div class="form-group">
                                        <div class="proof-of-identity">
                                            <h4>LTI/RTI Declaration</h4>
                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$nominee['lti_declaration_image']) }}" class="img-fluid ovd_image">
                                            </div>
                                            <div class="detaisl-right" style="margin-top:15px; width:100%;">
                                                <div class=" d-flex flex-row">
                                                    <div class="switch-blck" style="margin-right: 20px;">
                                                        <div class="toggleWrapper">
                                                            <input type="checkbox" name="lti_declaration_toggle" class="mobileToggle reviewComments" id="lti_declaration_toggle" {{$checked}} {{$disabled}}>
                                                            <label for="lti_declaration_toggle"></label>
                                                        </div>
                                                    </div>
                                                    <div class="comments-blck {{$display}}" style="width:100%;">
                                                        <input type="text" class="form-control commentsField" id="lti_declaration" name="lti_declaration">
                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                    </div>
                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
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
        @endif
    @endfor
@endif
