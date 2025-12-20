@php
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
@endphp
<div class="card" id="declarations">
    <div class="card-block">
        <!-- Row start -->
        <div class="row">
            <div class="col-lg-12 declarations-review">
                <h4 class="sub-title">Declarations</h4>
                @if((count($declarations) !== 0) && (!empty($declarations)))
                     @foreach($declarations as $npcdeclarations)
                        @if($npcdeclarations->blade_id == 'acknowledgement_receipt' || $npcdeclarations->blade_id == 'delight_kit_photograph')
                        @else
                            @include('npc.npcdeclaration')
                        @endif
                    @endforeach
                @endif
                <div class="row">
                    @if(($is_review == 1) && (!isset($reviewDetails['other'])))
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
                    <div class="col-md-4">
                        <div class="details-custcol-row">
                            <div class="details-custcol-row-top">
                                <div class="detaisl-left d-flex align-items-center">
                                    Any Other Applicable Documents :
                                    @if(isset($reviewDetails['other']))
                                        <span>{{$reviewDetails['other']}}</span>
                                    @else
                                        <span>NO</span>
                                    @endif
                                    <span>
                                        @if(count($qcReviewDetails) > 0)
                                            @if(isset($qcReviewDetails['other']))
                                                 <span class="review-comment">
                                                    <i class="fa fa-exclamation review-exclamation"></i>
                                                    {{$qcReviewDetails['other']}}
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
                                                <input type="checkbox" name="other_declaration_toggle" class="mobileToggle reviewComments" id="other_declaration_toggle" {{$checked}} {{$disabled}}>
                                                <label for="other_declaration_toggle"></label>
                                            </div>
                                        </div>
                                        <div class="comments-blck {{$display}}">
                                            <input type="text" class="form-control commentsField" id="other">
                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                        </div>
                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="custom-col-review col-md-4">
                        @if(isset($declarations['other']))
                            @if(($is_review == 1) && (!isset($reviewDetails['other_proof'])))
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
                            @foreach($declarations['other_proof'] as $other_proof)
                                <div class="form-group">
                                    <div class="proof-of-identity">
                                        <h4>Other Declaration</h4>
                                       <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                            <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$declarations['other']) }}" class="img-fluid ovd_image">
                                        </div>
                                        <div class="detaisl-right" style="margin-top:15px; width:100%;">
                                            <div class=" d-flex flex-row">
                                                <div class="switch-blck" style="margin-right: 20px;">
                                                    <div class="toggleWrapper">
                                                        <input type="checkbox" name="other_proof_toggle" class="mobileToggle reviewComments" id="other_proof_toggle" {{$checked}} {{$disabled}}>
                                                        <label for="other_proof_toggle"></label>
                                                    </div>
                                                </div>
                                                <div class="comments-blck {{$display}}" style="width:100%;">
                                                    <input type="text" class="form-control commentsField" id="other_proof" name="other_proof">
                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                </div>
                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
