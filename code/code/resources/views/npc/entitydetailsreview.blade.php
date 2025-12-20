@inject('provider','App\Helpers\labelCode')
@php
    $getSchemeCode = $accountDetails['scheme_code'];
    $label_land_mark    = $provider::getLabel($getSchemeCode,'label_land_mark');  
    $label_entity_name  = $provider::getLabel($getSchemeCode,'label_entity_name');  
    $label_proof_of_entity_address = $provider::getLabel($getSchemeCode,'label_proof_of_entity_address');
    $label_entity_mobile_number = $provider::getLabel($getSchemeCode,'label_entity_mobile_number');
    $label_entity_email = $provider::getLabel($getSchemeCode,'label_entity_email');
    $label_entity_details = $provider::getLabel($getSchemeCode,'label_entity_details');

    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";

@endphp
<div class="card" id="ovd_proofs">
        <div class="card-block">
            <!-- Row start -->
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="sub-title">{{$label_entity_details}}</h4>
                    <div>
                        <button id="rotate" class="rotate" style="margin-left:356px"><i class="fa fa-rotate-right"></i></button>
                    </div>
                        <div class="tab-pane" id="proof-of-permanent-address" role="tabpanel">
                                            <div class="proofs-blck">
                                                <div class="row">
                                                    <div class="custom-col-review col-md-4">
                                                        <div class="form-group">
                                                            @if($entityDetails['entity_add_proof_image'] != '')
                                                                <div class="proof-of-identity">
                                                                    <h4>{{$entityDetails['ovd']}}</h4>
                                                                    <div class="accordion" id="accordionExample">
                                                                        <div class="card-accordion">
                                                                            <div class="card-header-accordion" id="headingOne">
                                                                                <h2 class="mb-0">
                                                                                <button class="btn btn-link btn-block collapsed text-left" type="button" data-bs-toggle="collapse" data-bs-target="#entitycollapseOne1" aria-expanded="true" aria-controls="collapseOne">
                                                                                    {{$entityDetails['ovd']}}
                                                                                </button>
                                                                                </h2>
                                                                            </div>
                                                                            <div id="entitycollapseOne1" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                                                                <div class="card-body-accordion">
                                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$entityDetails['entity_add_proof_image']) }}" class="img-fluid proof_of_entity_address-zoom rotate_image">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                         @if(isset($entityDetails['entity_add_proof_back_image']) && $entityDetails['entity_add_proof_back_image'] != '')
                                                                            <div class="card-accordion">
                                                                                <div class="card-header-accordion" id="headingTwo">
                                                                                    <h2 class="mb-0">
                                                                                        <button class="btn btn-link btn-block text-left collapsed proof_of_entity_address-back" type="button" data-bs-toggle="collapse" data-bs-target="#entitycollapseOne2" aria-expanded="false" aria-controls="collapseTwo">
                                                                                        Entity back side
                                                                                        </button>
                                                                                    </h2>
                                                                                </div>
                                                                                <div id="entitycollapseOne2" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                                                                    <div class="card-body-accordion">
                                                                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                            <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$entityDetails['entity_add_proof_back_image']) }}" class="img-fluid proof_of_entity_address-back-zoom rotate_image">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    @if((($is_review == 1) && (!isset($reviewDetails['entity_add_proof_image']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                                @if(isset($qcReviewDetails['entity_add_proof_image']))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                        {{$qcReviewDetails['entity_add_proof_image']}}
                                                                                    </span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                        <div class=" d-flex flex-row">
                                                                            <div class="switch-blck" style="margin-right: 20px;">
                                                                                <div class="toggleWrapper">
                                                                                    <input type="checkbox" name="add_proof_image_toggle" class="mobileToggle reviewComments" id="add_proof_image_toggle" {{$checked}} {{$disabled}}>
                                                                                    <label for="add_proof_image_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}" style="width:100%;">
                                                                                <input type="text" class="form-control commentsField" id="entity_add_proof_image" name="entity_add_proof_image">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="custom-col-review proof-of-identity col-md-8">
                                                        <h4>Verify Address Details</h4>
                                                        <div class="details-custcol">
                                                            <div class="details-custcol-row">
                                                                <div class="details-custcol-row-top d-flex">
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        {{$label_proof_of_entity_address}}:
                                                                        <span>
                                                                             @if(strtoupper($entityDetails['ovd']) == "E-KYC")
                                                                             {{strtoupper($entityDetails['ovd'])}}
                                                                            @else
                                                                            {{strtoupper($entityDetails['ovd'])}}
                                                                            @endif
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['proof_of_entity_address']))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['proof_of_entity_address']}}
                                                                                    </span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    @if((($is_review == 1) && (!isset($reviewDetails['proof_of_entity_address']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                    <div class="detaisl-right">
                                                                        <div class=" d-flex flex-row">
                                                                            <div class="switch-blck">
                                                                                <div class="toggleWrapper">
                                                                                    <input type="checkbox" name="proof_of_address_toggle" class="mobileToggle reviewComments" id="proof_of_address_toggle" {{$checked}} {{$disabled}}>
                                                                                    <label for="proof_of_address_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="proof_of_entity_address">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if((($is_review == 1) && (!isset($reviewDetails['entity_add_proof_card_number']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                    @if(strtoupper($entityDetails['ovd']) == "E-KYC")
                                                                        <div class="detaisl-left d-flex align-items-center">
                                                                            {{$entityDetails['ovd']}} Number :
                                                                            <span>
                                                                                {{strtoupper($entityDetails['id_proof_card_number'])}}
                                                                            </span>
                                                                        </div>
                                                                    @else
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        {{$entityDetails['ovd']}} Number :
                                                                        <span>
                                                                            {{strtoupper($entityDetails['entity_add_proof_card_number'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_add_proof_card_number-']))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['entity_add_proof_card_number']}}
                                                                                    </span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                     @endif
                                                                    <div class="detaisl-right">
                                                                        <div class=" d-flex flex-row">
                                                                            <div class="switch-blck">
                                                                                <div class="toggleWrapper">
                                                                                   <input type="checkbox" name="add_proof_card_number_toggle-" class="mobileToggle reviewComments" id="add_proof_card_number_toggle" {{$checked}} {{$disabled}}>
                                                                                   <label for="add_proof_card_number_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_add_proof_card_number">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if($entityDetails['proof_of_entity_address'] == "Passport" || $entityDetails['proof_of_entity_address'] == "Driving Licence")
                                                                @if((($is_review == 1) && (!isset($reviewDetails['passport_driving_expire_permanent']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                            {{$entityDetails['proof_of_entity_address']}} Expire Date :
                                                                            <span>
                                                                                {{strtoupper(Carbon\Carbon::parse($entityDetails['passport_driving_expire_permanent'])->format('d-M-Y'))}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['passport_driving_expire_permanent']))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['passport_driving_expire_permanent']}}
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
                                                                                        <input type="checkbox" name="passport_driving_expire_permanent_toggle" class="mobileToggle reviewComments" id="passport_driving_expire_permanent_toggle" {{$checked}} {{$disabled}}>
                                                                                        <label for="passport_driving_expire_permanent_toggle"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="passport_driving_expire_permanent">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if((($is_review == 1) && (!isset($reviewDetails['entity_address_line1']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                        Address line1 :
                                                                        <span>
                                                                            {{strtoupper($entityDetails['entity_address_line1'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_address_line1']))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['entity_address_line1']}}
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
                                                                                    <input type="checkbox" name="per_address_line1_toggle" class="mobileToggle reviewComments" id="per_address_line1_toggle" {{$checked}} {{$disabled}}>
                                                                                    <label for="per_address_line1_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_address_line1">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['entity_address_line2']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                        Address line2 :
                                                                        <span>
                                                                            {{strtoupper($entityDetails['entity_address_line2'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_address_line2']))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['entity_address_line2']}}
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
                                                                                   <input type="checkbox" name="per_address_line2_toggle" class="mobileToggle reviewComments" id="per_address_line2_toggle" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_address_line2_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_address_line2">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['entity_pincode']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                            {{strtoupper($entityDetails['entity_pincode'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_pincode']))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['entity_pincode']}}
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
                                                                                   <input type="checkbox" name="per_pincode_toggle" class="mobileToggle reviewComments" id="per_pincode_toggle" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_pincode_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_pincode">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['entity_country']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                            {{strtoupper($entityDetails['name'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_country']))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['entity_country']}}
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
                                                                                   <input type="checkbox" name="per_country_toggle" class="mobileToggle reviewComments" id="per_country_toggle" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_country_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_country">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['entity_state']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                            {{strtoupper($entityDetails['entity_state'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_state']))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                    {{$qcReviewDetails['entity_state']}}
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
                                                                                   <input type="checkbox" name="per_state_toggle" class="mobileToggle reviewComments" id="per_state_toggle" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_state_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_state">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['entity_city']))) || ($entityDetails['proof_of_entity_address'] == 9))
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
                                                                            {{strtoupper($entityDetails['entity_city'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_city']))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['entity_city']}}
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
                                                                                   <input type="checkbox" name="per_city_toggle" class="mobileToggle reviewComments" id="per_city_toggle" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_city_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_city">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <h4>Verify Other Details</h4>
                                                            @if(($is_review == 1) && (!isset($reviewDetails['entity_name'])))
                                                                @php
                                                                    $checked = "checked";
                                                                    $disabled = "disabled";
                                                                    $display = "display-none";
                                                                @endphp
                                                            @else 
                                                                @php
                                                                    $checked = "";
                                                                    $disabled = "";
                                                                    $display = "";
                                                                @endphp
                                                            @endif
                                                            <div class="details-custcol-row">
                                                                <div class="details-custcol-row-top d-flex">
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        {{$label_entity_name}} :
                                                                        <span>
                                                                            {{strtoupper($entityDetails['entity_name'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_name']))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                    {{$qcReviewDetails['entity_name']}}
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
                                                                                    <input type="checkbox" name="entity_name_toggle" class="mobileToggle reviewComments" id="entity_name_toggle" {{$checked}} {{$disabled}}>
                                                                                    <label for="entity_name_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_name">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                             @if(($is_review == 1) &&(!isset($reviewDetails['entity_mobile_number'])))
                                                                @php
                                                                    $checked = "checked";
                                                                    $disabled = "disabled";
                                                                    $display = "display-none";
                                                                @endphp
                                                            @else 
                                                                @php
                                                                    $checked = "";
                                                                    $disabled = "";
                                                                    $display = "";
                                                                @endphp
                                                            @endif
                                                           <div class="details-custcol-row">
                                                                <div class="details-custcol-row-top d-flex">
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        {{$label_entity_mobile_number}} :
                                                                        <span>
                                                                        <label class="unmaskingfield enc_label" style="display: none;">
                                                                            {{($entityDetails['entity_mobile_number'])}}
                                                                        </label>
                                                                        <label class="maskingfield">
                                                                            ***********
                                                                        </label>
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_mobile_number']))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                    <label class="unmaskingfield enc_label" style="display: none;">
                                                                                        {{($qcReviewDetails['entity_mobile_number'])}}
                                                                                    </label>
                                                                                    <label class="maskingfield">
                                                                                        ***********
                                                                                    </label>
                                                                                    
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
                                                                                    <input type="checkbox" name="entity_mobile_number_toggle" class="mobileToggle reviewComments" id="entity_mobile_number_toggle" {{$checked}} {{$disabled}}>
                                                                                    <label for="entity_mobile_number_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_mobile_number">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if(($is_review == 1) &&(!isset($reviewDetails['entity_email_id'])))
                                                                @php
                                                                    $checked = "checked";
                                                                    $disabled = "disabled";
                                                                    $display = "display-none";
                                                                @endphp
                                                            @else 
                                                                @php
                                                                    $checked = "";
                                                                    $disabled = "";
                                                                    $display = "";
                                                                @endphp
                                                            @endif
                                                            <div class="details-custcol-row">
                                                                <div class="details-custcol-row-top d-flex">
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        {{$label_entity_email}} :
                                                                        <span>

                                                                                    <label class="unmaskingfield enc_label" style="display: none;">
                                                                                        {{($entityDetails['entity_email_id'])}}
                                                                                    </label>
                                                                                    <label class="maskingfield">
                                                                                        ***********
                                                                                    </label>
                                                                            
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['entity_email_id']))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                      <label class="unmaskingfield enc_label" style="display: none;">
                                                                                    {{$qcReviewDetails['entity_email_id']}}
                                                                                    </label>
                                                                                    <label class="maskingfield">
                                                                                        ***********
                                                                                    </label>
                                                                                    
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
                                                                                    <input type="checkbox" name="entity_email_id_toggle" class="mobileToggle reviewComments" id="entity_email_id_toggle" {{$checked}} {{$disabled}}>
                                                                                    <label for="entity_email_id_toggle"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="entity_email_id">
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
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>




