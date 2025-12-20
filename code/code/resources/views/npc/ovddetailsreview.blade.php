<ul id="reviewProofs-tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb">
    @php
        $applicantId = array();

    @endphp
    @for($j = 1;$j<=$accountHoldersCount;$j++)
    @php
   
    if($customerOvdDetails[$j-1]->is_new_customer == 1){
        $nextapplicant = (isset($customerOvdDetails[$j-1]->applicant_sequence) && $customerOvdDetails[$j-1]->applicant_sequence != ''? $customerOvdDetails[$j-1]->applicant_sequence : '');
        array_push($applicantId,$nextapplicant);
    }
    
    $customerOvd = (array) $customerOvdDetails[$j-1];
 
   
    @endphp
    @if($customerOvd['is_new_customer'] == 1)
        @if($j == 1)
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
            $secondTab = $is_huf ? 'HUF' : 'Applicant'.$j;
    @endphp
        <li class="nav-item {{$class}}">
            @if($j == 1)
                <a href="#reviewProofs-tab{{$j}}" data-id="tab_applicant_{{$j}}" class="nav-link">{{ $firstTab }}</a>
            @else
                <a href="#reviewProofs-tab{{$j}}" data-id="tab_applicant_{{$j}}" class="nav-link">{{ $secondTab }}</a>
            @endif
        </li>
        @else 
    @php
       $class = "active";
    @endphp
        @endif
    @endfor
   
    <li class="nav-item ">
        <a href="#photograph-tab" class="nav-link photog" data-id="photographsignature" data-toggle="tab" role="tab">CUBE AOF</a>
    </li>
</ul>
@php
        $customerOvd = array();
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
@endphp
<div id="reviewProofs-tabs-content-cust" class="reviewProofs-tabs-content-cust">
    @for($i = 1;$i<= count($applicantId);$i++)
        @php
            $j = $applicantId[$i-1];
            for($k=0;count($customerOvdDetails)>$k;$k++){

                if($customerOvdDetails[$k]->applicant_sequence == $j){
        
                    $customerOvd = (array) $customerOvdDetails[$k];
                    $customerOvd['id_proof_image'] = explode(',',$customerOvd['id_proof_image']);
                    $customerOvd['add_proof_image'] = explode(',',$customerOvd['add_proof_image']);
                    $appdemo =  isset($applicantId[$i]) && $applicantId[$i] != ''?$applicantId[$i]:'N';

                    if ($accountDetails['constitution'] == 'NON_IND_HUF' && $j == 2) {
                        $is_huf_display = true;
                    }else{
                        $is_huf_display = false;
                    }
            
        @endphp
       
       
        <div id="reviewProofs-tab{{$j}}" class="reviewProofs-tab-content-cust">
            <div class="card" id="ovd_proofs">
                <div class="card-block">
                    <div class="row">
                        <div class="col-lg-12">
                            <h4 class="sub-title">Proofs</h4>
                            <!-- Row start -->
                            <div class="row">
                                <div class="col-lg-12 col-xl-12">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs md-tabs tabs-left b-none left-tabs" role="tablist" style="width: 15%; display: inline-block;">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-id="proof-of-identity-{{$j}}"  data-bs-toggle="tab" href="#proof-of-identity-{{$j}}" role="tab">Proof of Identity <i class="fa fa-angle-right bc-arrow" aria-hidden="true"></i> </a>
                                            <div class="slide"></div>
                                        </li>
                                        <li class="nav-item proof-of-permanent-address-zoom">
                                            <a class="nav-link" data-bs-toggle="tab" data-id="proof-of-permanent-address-{{$j}}" href="#proof-of-permanent-address-{{$j}}" role="tab">
                                                
                                                {{ $is_huf_display ? 'Proof of Registered address' : 'Proof of permanent address' }}

                                                <i class="fa fa-angle-right bc-arrow" aria-hidden="true"></i> </a>
                                            <div class="slide"></div>
                                        </li>
                                        @if($customerOvd['address_flag'] != 1)
                                       
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" data-id="proof-of-current-address-{{$j}}"  href="#proof-of-current-address-{{$j}}" role="tab">Proof of current address</a>
                                            <div class="slide"></div>
                                        </li>
                                        @elseif($customerOvd['address_flag'] = 1)
                                      
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" data-id="proof-of-current-address-{{$j}}"  href="#proof-of-current-address-{{$j}}" role="tab">Proof of current address</a>
                                            <div class="slide"></div>
                                        </li>
                                        @endif
                                    </ul>
                                    <!-- Tab panes -->
                                    <div class="tab-content tabs-left-content card-block" style="width:84%; display: inline-block;">
                                        <div class="tab-pane active" id="proof-of-identity-{{$j}}" role="tabpanel">
                                            <div class="proofs-blck">
                                                <div class="row">
                                                    <div class="custom-col-review col-md-4">
                                                        <div class="form-group">
                                                            @if($customerOvd['id_proof_image'][0] != '')
                                                                <div class="proof-of-identity">

                                                                            <div class="row" style="margin-bottom: 8px;">
                                                                    <h4>{{$customerOvd['proof_of_identity']}}</h4>

                                                                     <!-- 22May23 - For BS5 - commented below line -->
                                                                                        <!-- <button id="rotate" class="rotate" style="margin-left: 356px;"><i class="fa fa-rotate-right"></i></button> -->
                                                                                        <button id="rotate" class="rotate col-sm-1" style="margin-left:356px"><i class="fa fa-rotate-right"></i></button>
                                                                                    </div>
                                                                    <div class="accordion" id="accordionExample">
                                                                        <div class="card-accordion">
                                                                            <div class="card-header-accordion" id="headingOne">
                                                                                <h2 class="mb-0">
                                                                                <button class="btn btn-link btn-block text-left" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                                                    {{$customerOvd['proof_of_identity']}} front side
                                                                                </button>
                                                                                </h2>
                                                                            </div>
                                                                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                                                                                <div class="card-body-accordion">
                                                                                     
                                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$customerOvd['id_proof_image'][0]) }}" class="img-fluid ovd_image rotate_image">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @if(isset($customerOvd['id_proof_image'][1]))
                                                                            <div class="card-accordion">
                                                                                <div class="card-header-accordion" id="headingTwo">
                                                                                    <h2 class="mb-0">
                                                                                        <button class="btn btn-link btn-block text-left collapsed ovd_image_zoom" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                                                            {{$customerOvd['proof_of_identity']}} back side
                                                                                        </button>
                                                                                    </h2>
                                                                                </div>
                                                                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                                                                                    <div class="card-body-accordion">
                                                                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                            <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$customerOvd['id_proof_image'][1]) }}" class="img-fluid ovd_image rotate_image">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    @if(($is_review == 1) && (!isset($reviewDetails['id_proof_image-'.$j])))
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
                                                                                @if(isset($qcReviewDetails['id_proof_image-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['id_proof_image-'.$j]}}
                                                                                    </span> 
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                        <div class=" d-flex flex-row">
                                                                            <div class="switch-blck" style="margin-right: 20px;">
                                                                                <div class="toggleWrapper">
                                                                                    <input type="checkbox" name="id_proof_image_toggle-{{$j}}" class="mobileToggle reviewComments" id="id_proof_image_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="id_proof_image_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}" style="width:100%;">
                                                                                <input type="text" class="form-control commentsField" id="id_proof_image-{{$j}}" name="id_proof_image-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if(isset($customerOvd['ekyc_photo']) && $customerOvd['ekyc_photo'] != '' && ($customerOvd['proof_of_identity'] == 'E-KYC'))
                                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                <img  width="160px" alt="" src="{{ 'data: image/jpeg;base64,' .$customerOvd['ekyc_photo'] }}"/>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="custom-col-review proof-of-identity col-md-8">
                                                        <h4>Verify Identity Details</h4>
                                                        <div class="details-custcol">
                                                            @if(($is_review == 1) && (!isset($reviewDetails['proof_of_identity-'.$j])))
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
                                                            @if(!$is_huf_display)
                                                            <div class="details-custcol-row">
                                                                <div class="details-custcol-row-top d-flex">
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        Proof of Identity :
                                                                        <span>
                                                                            {{strtoupper($customerOvd['proof_of_identity'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['proof_of_identity-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                        {{$qcReviewDetails['proof_of_identity-'.$j]}}
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
                                                                                    <input type="checkbox" name="proof_of_identity_toggle-{{$j}}" class="mobileToggle reviewComments" id="proof_of_identity_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="proof_of_identity_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="proof_of_identity-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if(($is_review == 1) && (!isset($reviewDetails['id_proof_card_number-'.$j])))
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
                                                                        {{$customerOvd['proof_of_identity']}} number :
                                                                        <div class="d-inline">
                                                                        @if(in_array($customerOvd['proof_of_identity'],['Aadhaar Photocopy','Passport','Voter ID','Driving Licence']))
                                                                            <label class="maskingfield">
                                                                                <label>**********</label>
                                                                            </label>
                                                                            <span class="unmaskingfield" style="display: none;">
                                                                                <label>{{$customerOvd['id_proof_card_number']}}</label>
                                                                            </span>
                                                                        @else
                                                                            <span class="">
                                                                                <label>{{$customerOvd['id_proof_card_number']}}</label>
                                                                            </span>
                                                                        @endif
                                                                        
                                            

                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['id_proof_card_number-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                        {{$qcReviewDetails['id_proof_card_number-'.$j]}}
                                                                                    </span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            @endif
                                                                    </div>
                                                                    </div>
                                                                    <div class="detaisl-right">
                                                                        <div class=" d-flex flex-row">
                                                                            <div class="switch-blck">
                                                                                <div class="toggleWrapper">
                                                                                    <input type="checkbox" name="id_proof_card_number_toggle-{{$j}}" class="mobileToggle reviewComments" id="id_proof_card_number_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="id_proof_card_number_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField idtype_{{$customerOvd['proof_of_identity_id']}}" id="id_proof_card_number-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if(($is_review == 1) && (!isset($reviewDetails['passport_driving_expire-'.$j])))
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
                                                            @if($customerOvd['proof_of_identity'] == "Passport" || $customerOvd['proof_of_identity'] == "Driving Licence")
                                                            <div class="details-custcol-row">
                                                                <div class="details-custcol-row-top d-flex">
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        {{$customerOvd['proof_of_identity']}} Expire Date :
                                                                        <span>
                                                                            {{strtoupper(Carbon\Carbon::parse($customerOvd['passport_driving_expire'])->format('d-M-Y'))}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['passport_driving_expire-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                        {{$qcReviewDetails['passport_driving_expire-'.$j]}}
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
                                                                                    <input type="checkbox" name="passport_driving_expire_toggle-{{$j}}" class="mobileToggle reviewComments" id="id_proof_card_number_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="passport_driving_expire_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="passport_driving_expire-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                            @endif
                                                            @if(($is_review == 1) && (!isset($reviewDetails['id_psprt_dri_issue-'.$j])))
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
                                                            @if($customerOvd['proof_of_identity'] == "Passport" || $customerOvd['proof_of_identity'] == "Driving Licence")
                                                            <div class="details-custcol-row">
                                                                <div class="details-custcol-row-top d-flex">
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        {{$customerOvd['proof_of_identity']}} Issue Date :
                                                                        <span>
                                                                            {{strtoupper(Carbon\Carbon::parse($customerOvd['id_psprt_dri_issue'])->format('d-M-Y'))}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['id_psprt_dri_issue-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                        {{$qcReviewDetails['id_psprt_dri_issue-'.$j]}}
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
                                                                                    <input type="checkbox" name="id_psprt_dri_issue_toggle-{{$j}}" class="mobileToggle reviewComments" id="id_psprt_dri_issue_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="id_psprt_dri_issue_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="id_psprt_dri_issue-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            @if((($is_review == 1) && (!isset($reviewDetails['title-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                        Title :
                                                                        <span>
                                                                            {{strtoupper($customerOvd['title'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['title-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['title-'.$j]}}
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
                                                                                    <input type="checkbox" name="title_toggle-{{$j}}" class="mobileToggle reviewComments" id="title_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="title_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="title-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if(!$is_huf_display)
                                                            @if((($is_review == 1) && (!isset($reviewDetails['gender-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                        Gender :
                                                                        <span>
                                                                            {{strtoupper(config('constants.GENDER.'.$customerOvd['gender']))}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['gender-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                        {{$qcReviewDetails['gender-'.$j]}}
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
                                                                                    <input type="checkbox" name="gender_toggle-{{$j}}" class="mobileToggle reviewComments" id="gender_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="gender_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="gender-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif

                                                            @if((($is_review == 1) && (!isset($reviewDetails['first_name-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                        {{ $is_huf_display ? 'HUF Name:' : 'First Name :' }}
                                                                        <span>
                                                                            {{strtoupper($customerOvd['first_name'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['first_name-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['first_name-'.$j]}}
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
                                                                                    <input type="checkbox" name="first_name_toggle-{{$j}}" class="mobileToggle reviewComments" id="first_name_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="first_name_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="first_name-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if($is_huf_display)
                                                            @foreach ($huf_cop_row as $key => $co)
                                                            @php
                                                            $co = (array) $co;
                                                            @endphp
                                                            @if((($is_review == 1) && (!isset($reviewDetails['coparcenar_name'.($key+1).'-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                    <div class="detaisl-left d-flex">
                                                                        Coparcenor Name -{{$key+1}} :
                                                                        <span>
                                                                            {{strtoupper($co['coparcenar_name'])}}

                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['coparcenar_name-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['coparcenar_name-'.$j]}}
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
                                                                                    <input type="checkbox" name="coparcenar_name_toggle{{$key+1}}-{{$j}}" class="mobileToggle reviewComments" id="coparcenar_name_toggle{{$key+1}}-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="coparcenar_name_toggle{{$key+1}}-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="coparcenar_name{{$key+1}}-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['coparcener_type'.($key+1).'-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                    <div class="detaisl-left d-flex">
                                                                        Coparcenor Type -{{$key+1}} :
                                                                        <span>
                                                                            {{strtoupper($co['coparcener_type'])}}

                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['coparcener_type-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['coparcener_type-'.$j]}}
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
                                                                                    <input type="checkbox" name="coparcener_type_toggle{{$key+1}}-{{$j}}" class="mobileToggle reviewComments" id="coparcener_type_toggle{{$key+1}}-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="coparcener_type_toggle{{$key+1}}-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="coparcener_type{{$key+1}}-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['huf_relation'.($key+1).'-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                    <div class="detaisl-left d-flex">
                                                                        Coparcenor Relation -{{$key+1}} :
                                                                        <span>
                                                                            {{strtoupper($co['relation'])}}

                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['relation-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['relation-'.$j]}}
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
                                                                                    <input type="checkbox" name="relation_toggle{{$key+1}}-{{$j}}" class="mobileToggle reviewComments" id="relation_toggle{{$key+1}}-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="relation_toggle{{$key+1}}-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="huf_relation{{$key+1}}-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            @if((($is_review == 1) && (!isset($reviewDetails['dob'.($key+1).'-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))

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
                                                                    <div class="detaisl-left d-flex">
                                                                        Coparcenor DOB -{{$key+1}} :
                                                                        <span>
                                                                            {{strtoupper($co['dob'])}}

                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['dob-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['dob-'.$j]}}
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
                                                                                    <input type="checkbox" name="dob_toggle{{$key+1}}-{{$j}}" class="mobileToggle reviewComments" id="dob_toggle_{{$key+1}}-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="dob_toggle{{$key+1}}-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="dob{{$key+1}}-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @endforeach
                                                            @endif
                                                            
                                                            @if(!$is_huf_display)
                                                            @if((($is_review == 1) && (!isset($reviewDetails['middle_name-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                             @if($customerOvd['middle_name'] != "")
                                                                <div class="details-custcol-row-top d-flex">
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        Middle Name :
                                                                        <span>
                                                                            {{strtoupper($customerOvd['middle_name'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['middle_name-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['middle_name-'.$j]}}
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
                                                                                    <input type="checkbox" name="middle_name_toggle-{{$j}}" class="mobileToggle reviewComments" id="middle_name_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="middle_name_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="middle_name-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endif
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['last_name-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                        Last Name :
                                                                        <span>
                                                                            {{strtoupper($customerOvd['last_name'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['last_name-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['last_name-'.$j]}}
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
                                                                                    <input type="checkbox" name="last_name_toggle-{{$j}}" class="mobileToggle reviewComments" id="last_name_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="last_name_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="last_name-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if(($is_review == 1) && (!isset($reviewDetails['father_name-'.$j])))
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
                                                                        @if($customerOvd['father_spouse'] == '01')
                                                                            Father Name :
                                                                        @else
                                                                            Spouse Name :
                                                                        @endif
                                                                        <span>
                                                                            {{strtoupper($customerOvd['father_name'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['father_name-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['father_name-'.$j]}}
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
                                                                                    <input type="checkbox" name="father_name_toggle-{{$j}}" class="mobileToggle reviewComments" id="father_name_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="father_name_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="father_name-{{$j}}">
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
                                                <div class="row">
                                                    <div class="col-md-12 text-center mt-3 mb-3">
                                    
                                                        <button class="btn btn-primary" onclick = "nexttab('proof-of-identity-{{$j}}','{{$appdemo}}')" >Next</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="proof-of-permanent-address-{{$j}}" role="tabpanel">
                                            <div class="proofs-blck">
                                                <div class="row">
                                                    <div class="custom-col-review col-md-4">
                                                        <div class="form-group">
                                                            @if($customerOvd['add_proof_image'][0] != '')
                                                                <div class="proof-of-identity">
                                                                    <div class="row" style="margin-bottom: 8px;">
                                                                        <h4>{{$customerOvd['proof_of_address']}}</h4>

                                                                         <!-- 22May23 - For BS5 - commented below line -->
                                                                        <!-- <button id="rotate" class="rotate" style="margin-left: 356px;"><i class="fa fa-rotate-right"></i></button> -->
                                                                        
                                                                        <button id="rotate" class="rotate col-sm-1" style="margin-left:356px"><i class="fa fa-rotate-right"></i></button>
                                                                                    </div>
                                                                    <div class="accordion" id="accordionExample-{{$j}}">
                                                                        <div class="card-accordion">
                                                                            <div class="card-header-accordion" id="headingOne">
                                                                                <h2 class="mb-0">
                                                                                <button class="btn btn-link btn-block text-left" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{$j}}" aria-expanded="true" aria-controls="collapseOne-{{$j}}">
                                                                                    {{$customerOvd['proof_of_address']}} front side
                                                                                </button>
                                                                                </h2>
                                                                            </div>
                                                                            <div id="collapseOne-{{$j}}" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample-{{$j}}">
                                                                                <div class="card-body-accordion">
                                                                                   <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$customerOvd['add_proof_image'][0]) }}" class="img-fluid  rotate_image proof_of_address-zoom">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @if(isset($customerOvd['add_proof_image'][1]))
                                                                            <div class="card-accordion">
                                                                                <div class="card-header-accordion" id="headingTwo-{{$j}}">
                                                                                    <h2 class="mb-0">
                                                                                        <button class="btn btn-link btn-block text-left collapsed proof_of_address-back" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo-{{$j}}" aria-expanded="false" aria-controls="collapseTwo">
                                                                                        {{$customerOvd['proof_of_address']}} back side
                                                                                        </button>
                                                                                    </h2>
                                                                                </div>
                                                                                <div id="collapseTwo-{{$j}}" class="collapse" aria-labelledby="headingTwo-{{$j}}" data-parent="#accordionExample-{{$j}}">
                                                                                    <div class="card-body-accordion">
                                                                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                            <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$customerOvd['add_proof_image'][1]) }}" class="img-fluid proof_of_address-back-zoom rotate_image ovd_image">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    @if((($is_review == 1) && (!isset($reviewDetails['add_proof_image-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                                @if(isset($qcReviewDetails['add_proof_image-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                        {{$qcReviewDetails['add_proof_image-'.$j]}}
                                                                                    </span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                        <div class=" d-flex flex-row">
                                                                            <div class="switch-blck" style="margin-right: 20px;">
                                                                                <div class="toggleWrapper">
                                                                                    <input type="checkbox" name="add_proof_image_toggle-{{$j}}" class="mobileToggle reviewComments" id="add_proof_image_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="add_proof_image_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}" style="width:100%;">
                                                                                <input type="text" class="form-control commentsField" id="add_proof_image-{{$j}}" name="add_proof_image-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            
                                                            @if(isset($customerOvd['ekyc_photo']) && $customerOvd['ekyc_photo'] != '' && (strtoupper($customerOvd['proof_of_address']) == 'E-KYC'))
                                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                <img  width="160px" alt="" src="{{ 'data: image/jpeg;base64,' .$customerOvd['ekyc_photo'] }}"/>
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
                                                                        Proof of Address :
                                                                        <span>
                                                                             @if(strtoupper($customerOvd['proof_of_identity']) == "E-KYC")
                                                                             {{strtoupper($customerOvd['proof_of_identity'])}}
                                                                            @else
                                                                            {{strtoupper($customerOvd['proof_of_address'])}}
                                                                            @endif
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['proof_of_address-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['proof_of_address-'.$j]}}
                                                                                    </span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    @if((($is_review == 1) && (!isset($reviewDetails['proof_of_address-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                                    <input type="checkbox" name="proof_of_address_toggle-{{$j}}" class="mobileToggle reviewComments" id="proof_of_address_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="proof_of_address_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="proof_of_address-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @if((($is_review == 1) && (!isset($reviewDetails['add_proof_card_number-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                    @if(strtoupper($customerOvd['proof_of_identity']) == "E-KYC")
                                                                        <div class="detaisl-left d-flex align-items-center">
                                                                            {{$customerOvd['proof_of_identity']}} Number :
                                                                            <span>
                                                                                <label>{{$customerOvd['id_proof_card_number']}}</label>
                                                                            </span>
                                                                        </div>
                                                                    @else
                                                                    <div class="detaisl-left d-flex align-items-center">
                                                                        {{$customerOvd['proof_of_address']}} Number :
                                                                        <div class="d-inline">
                                                                            @if(in_array($customerOvd['proof_of_address'],['Aadhaar Photocopy','Passport','Voter ID','Driving Licence']))
                                                                                <lable class="maskingfield">
                                                                                    <label>************</label>
                                                                                </lable>
                                                                                <span class="unmaskingfield" style="display: none;">
                                                                                    <label>{{($customerOvd['add_proof_card_number'])}}</label>
                                                                                </span>
                                                                            @else
                                                                        <span>
                                                                                    <label>{{($customerOvd['add_proof_card_number'])}}</label>
                                                                                </span>
                                                                            @endif

                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['add_proof_card_number-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['add_proof_card_number-'.$j]}}
                                                                                    </span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            @endif
                                                                    </div>
                                                                    </div>
                                                                     @endif
                                                                    <div class="detaisl-right">
                                                                        <div class=" d-flex flex-row">
                                                                            <div class="switch-blck">
                                                                                <div class="toggleWrapper">
                                                                                   <input type="checkbox" name="add_proof_card_number_toggle-{{$j}}" class="mobileToggle reviewComments" id="add_proof_card_number_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                   <label for="add_proof_card_number_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="add_proof_card_number-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if($customerOvd['proof_of_address'] == "Passport" || $customerOvd['proof_of_address'] == "Driving Licence")
                                                                @if((($is_review == 1) && (!isset($reviewDetails['passport_driving_expire_permanent-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                            {{$customerOvd['proof_of_address']}} Expire Date :
                                                                            <span>
                                                                                {{strtoupper(Carbon\Carbon::parse($customerOvd['passport_driving_expire_permanent'])->format('d-M-Y'))}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['passport_driving_expire_permanent-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['passport_driving_expire_permanent-'.$j]}}
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
                                                                                        <input type="checkbox" name="passport_driving_expire_permanent_toggle-{{$j}}" class="mobileToggle reviewComments" id="passport_driving_expire_permanent_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="passport_driving_expire_permanent_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="passport_driving_expire_permanent-{{$j}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if($customerOvd['proof_of_address'] == "Passport" || $customerOvd['proof_of_address'] == "Driving Licence")
                                                                @if((($is_review == 1) && (!isset($reviewDetails['add_psprt_dri_issue-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                            {{$customerOvd['proof_of_address']}} Issue Date :
                                                                            <span>
                                                                                {{strtoupper(Carbon\Carbon::parse($customerOvd['add_psprt_dri_issue'])->format('d-M-Y'))}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['add_psprt_dri_issue-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['add_psprt_dri_issue-'.$j]}}
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
                                                                                        <input type="checkbox" name="add_psprt_dri_issue_toggle-{{$j}}" class="mobileToggle reviewComments" id="add_psprt_dri_issue_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="add_psprt_dri_issue_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="add_psprt_dri_issue-{{$j}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            @if((($is_review == 1) && (!isset($reviewDetails['per_address_line1-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                            {{strtoupper($customerOvd['per_address_line1'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['per_address_line1-'.$j]))
                                                                                    <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['per_address_line1-'.$j]}}
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
                                                                                    <input type="checkbox" name="per_address_line1_toggle-{{$j}}" class="mobileToggle reviewComments" id="per_address_line1_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="per_address_line1_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="per_address_line1-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['per_address_line2-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                            {{strtoupper($customerOvd['per_address_line2'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['per_address_line2-'.$j]))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['per_address_line2-'.$j]}}
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
                                                                                   <input type="checkbox" name="per_address_line2_toggle-{{$j}}" class="mobileToggle reviewComments" id="per_address_line2_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_address_line2_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="per_address_line2-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['per_pincode-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                            {{strtoupper($customerOvd['per_pincode'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['per_pincode-'.$j]))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['per_pincode-'.$j]}}
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
                                                                                   <input type="checkbox" name="per_pincode_toggle-{{$j}}" class="mobileToggle reviewComments" id="per_pincode_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_pincode_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="per_pincode-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['per_country-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                            {{strtoupper($customerOvd['per_country'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['per_country-'.$j]))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['per_country-'.$j]}}
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
                                                                                   <input type="checkbox" name="per_country_toggle-{{$j}}" class="mobileToggle reviewComments" id="per_country_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_country_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="per_country-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['per_state-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                            {{strtoupper($customerOvd['per_state'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['per_state-'.$j]))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                    {{$qcReviewDetails['per_state-'.$j]}}
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
                                                                                   <input type="checkbox" name="per_state_toggle-{{$j}}" class="mobileToggle reviewComments" id="per_state_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_state_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="per_state-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if((($is_review == 1) && (!isset($reviewDetails['per_city-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                            {{strtoupper($customerOvd['per_city'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['per_city-'.$j]))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                     {{$qcReviewDetails['per_city-'.$j]}}
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
                                                                                   <input type="checkbox" name="per_city_toggle-{{$j}}" class="mobileToggle reviewComments" id="per_city_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                   <label for="per_city_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="per_city-{{$j}}">
                                                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                          
                                                            @if((($is_review == 1) && (!isset($reviewDetails['per_landmark-'.$j]))) || ($customerOvd['proof_of_identity_id'] == 9))
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
                                                                        Landmark :
                                                                        <span>
                                                                            {{strtoupper($customerOvd['per_landmark'])}}
                                                                            @if(count($qcReviewDetails) > 0)
                                                                                @if(isset($qcReviewDetails['per_landmark-'.$j]))
                                                                                <span class="review-comment">
                                                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                                                    {{$qcReviewDetails['per_landmark-'.$j]}}
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
                                                                                    <input type="checkbox" name="per_landmark_toggle-{{$j}}" class="mobileToggle reviewComments" id="per_landmark_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                    <label for="per_landmark_toggle-{{$j}}"></label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="comments-blck {{$display}}">
                                                                                <input type="text" class="form-control commentsField" id="per_landmark-{{$j}}">
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
                                                 <div class="row">
                                                    <div class="col-md-12 text-center mt-3 mb-3">
                                                        <button class="btn btn-outline-grey mr-3" onclick = "previoustab('proof-of-permanent-address-{{$j}}','{{$accountHoldersCount}}')" >Back</button>
                                                            
                                                        <button class="btn btn-primary" onclick = "nexttab('proof-of-permanent-address-{{$j}}','{{$appdemo}}')" >Next</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                         <!-- For L1 Edit Review Fields-->
                                    
                                            <div class="tab-pane" id="proof-of-current-address-{{$j}}" role="tabpanel">
                                                <div class="proofs-blck">
                                                    <div class="row">
                                                        <div class="custom-col-review col-md-4">
                                                            <div class="form-group">
                                                                @if($customerOvd['current_add_proof_image'] != '')
                                                                    <div class="proof-of-identity">
                                                                    <div class="row" style="margin-bottom: 8px;">
                                                                        <h4>{{$customerOvd['proof_of_current_address']}} Card</h4>

                                                                         <!-- 22May23 - For BS5 - commented below line -->
                                                                        <!-- <button id="rotate" class="rotate" style="margin-left: 356px;"><i class="fa fa-rotate-right"></i></button> -->

                                                                        <button id="rotate" class="rotate col-sm-1" style="margin-left:356px"><i class="fa fa-rotate-right"></i></button>
                                                                    </div>


                                                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                            <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$customerOvd['current_add_proof_image']) }}" class="img-fluid ovd_image rotate_image">
                                                                        </div>
                                                                        @if(($is_review == 1) && (!isset($reviewDetails['current_add_proof_image-'.$j])))
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
                                                                                    @if(isset($qcReviewDetails['current_add_proof_image-'.$j]))
                                                                                    <span class="review-comment">
                                                                                          <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['current_add_proof_image-'.$j]}}
                                                                                    </span>
                                                                                    @else
                                                                                        <i class="fa fa-check"></i>
                                                                                    @endif
                                                                                </span>
                                                                            </div>
                                                                            <div class=" d-flex flex-row">
                                                                                <div class="switch-blck" style="margin-right: 20px;">
                                                                                    <div class="toggleWrapper">
                                                                                        <input type="checkbox" name="current_add_proof_image_toggle-{{$j}}" class="mobileToggle reviewComments" id="current_add_proof_image_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="current_add_proof_image_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}" style="width:100%;">
                                                                                    <input type="text" class="form-control commentsField" id="current_add_proof_image-{{$j}}" name="current_add_proof_image-{{$j}}">
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
                                                            <h4>Verify Current Address Details</h4>
                                                            <div class="details-custcol">
                                                         
                                                                    @if((($is_review == 1) && (!isset($reviewDetails['proof_of_current_address-'.$j]))) ||  (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                                Proof of Current Address :
                                                                                <span>
                                                                                    {{strtoupper($customerOvd['proof_of_current_address'])}}
                                                                                    @if(count($qcReviewDetails) > 0)
                                                                                        @if(isset($qcReviewDetails['proof_of_current_address-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                             {{$qcReviewDetails['proof_of_current_address-'.$j]}}
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
                                                                                            <input type="checkbox" name="proof_of_current_address_toggle-{{$j}}" class="mobileToggle reviewComments" id="proof_of_current_address_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                            <label for="proof_of_current_address_toggle-{{$j}}"></label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="comments-blck {{$display}}">
                                                                                        <input type="text" class="form-control commentsField" id="proof_of_current_address-{{$j}}">
                                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                    </div>
                                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    @if((($is_review == 1) && (!isset($reviewDetails['current_add_proof_card_number-'.$j]))) || (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                                {{$customerOvd['proof_of_current_address']}} Number :
                                                                                <span>
                                                                                    {{strtoupper($customerOvd['current_add_proof_card_number'])}}
                                                                                    @if(count($qcReviewDetails) > 0)
                                                                                        @if(isset($qcReviewDetails['current_add_proof_card_number-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                        {{$qcReviewDetails['current_add_proof_card_number-'.$j]}}
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
                                                                                            <input type="checkbox" name="current_add_proof_card_number_toggle-{{$j}}" class="mobileToggle reviewComments" id="current_add_proof_card_number_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                            <label for="current_add_proof_card_number_toggle-{{$j}}"></label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="comments-blck {{$display}}">
                                                                                        <input type="text" class="form-control commentsField" id="current_add_proof_card_number-{{$j}}">
                                                                                        <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                    </div>
                                                                                    <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                               

                                                                @if((($is_review == 1) && (!isset($reviewDetails['current_address_line1-'.$j]))) ||  (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                                {{strtoupper($customerOvd['current_address_line1'])}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['current_address_line1-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['current_address_line1-'.$j]}}
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
                                                                                        <input type="checkbox" name="current_address_line1_toggle-{{$j}}" class="mobileToggle reviewComments" id="current_address_line1_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="current_address_line1_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="current_address_line1-{{$j}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if((($is_review == 1) && (!isset($reviewDetails['current_address_line2-'.$j]))) ||  (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                                {{strtoupper($customerOvd['current_address_line2'])}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['current_address_line2-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['current_address_line2-'.$j]}}
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
                                                                                        <input type="checkbox" name="current_address_line2_toggle-{{$j}}" class="mobileToggle  reviewComments" id="current_address_line2_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="current_address_line2_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="current_address_line2-{{$j}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if((($is_review == 1) && (!isset($reviewDetails['current_pincode-'.$j]))) ||  (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                                {{strtoupper($customerOvd['current_pincode'])}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['current_pincode-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['current_pincode-'.$j]}}
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
                                                                                        <input type="checkbox" name="current_pincode_toggle-{{$j}}" class="mobileToggle reviewComments" id="current_pincode_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="current_pincode_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="current_pincode-{{$j}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if((($is_review == 1) && (!isset($reviewDetails['current_country-'.$j]))) ||  (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                                {{strtoupper($customerOvd['current_country'])}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['current_country-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['current_country-'.$j]}}
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
                                                                                        <input type="checkbox" name="current_country_toggle-{{$j}}" class="mobileToggle reviewComments" id="current_country_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="current_country_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="current_country-{{$j}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if((($is_review == 1) && (!isset($reviewDetails['current_state-'.$j]))) ||  (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                                {{strtoupper($customerOvd['current_state'])}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['current_state-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['current_state-'.$j]}}
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
                                                                                        <input type="checkbox" name="current_state_toggle-{{$j}}" class="mobileToggle reviewComments" id="current_state_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="current_state_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="current_state-{{$j}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if((($is_review == 1) && (!isset($reviewDetails['current_city-'.$j]))) ||  (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                                {{strtoupper($customerOvd['current_city'])}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['current_city-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['current_city-'.$j]}}
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
                                                                                        <input type="checkbox" name="current_city_toggle-{{$j}}" class="mobileToggle reviewComments" id="current_city_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="current_city_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="current_city-{{$j}}">
                                                                                    <i title="save" class="fa fa-floppy-o saveComments"></i>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @if((($is_review == 1) && (!isset($reviewDetails['current_landmark-'.$j]))) ||  (($customerOvd['proof_of_identity_id'] == 9 || strtoupper($customerOvd['proof_of_address']) == "E-KYC") && ($customerOvd['address_flag'] == 1)))
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
                                                                            Landmark :
                                                                            <span>
                                                                                {{strtoupper($customerOvd['current_landmark'])}}
                                                                                @if(count($qcReviewDetails) > 0)
                                                                                    @if(isset($qcReviewDetails['current_landmark-'.$j]))
                                                                                        <span class="review-comment">
                                                                                              <i class="fa fa-exclamation review-exclamation"></i>
                                                                                         {{$qcReviewDetails['current_landmark-'.$j]}}
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
                                                                                        <input type="checkbox" name="current_landmark_toggle-{{$j}}" class="mobileToggle reviewComments" id="current_landmark_toggle-{{$j}}" {{$checked}} {{$disabled}}>
                                                                                        <label for="current_landmark_toggle-{{$j}}"></label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="comments-blck {{$display}}">
                                                                                    <input type="text" class="form-control commentsField" id="current_landmark-{{$j}}">
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
                                                    <div class="row">
                                                        <div class="col-md-12 text-center mt-3 mb-3">
                                                            <button class="btn btn-outline-grey mr-3" onclick = "previoustab('proof-of-current-address-{{$j}}',{{$accountHoldersCount}})" >Back</button>
                                                            
                                                            <button class="btn btn-primary" onclick = "nexttab('proof-of-current-address-{{$j}}','{{$appdemo}}')" >Next</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <!--Same As Address Proof-->
                                                     
                                                        </div>
                                                    </div>
                                                </div>
                            <!-- Row end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @php
            }
        }
        @endphp
    @endfor
    <!-- <p>Existing Customer</p> -->
    <div id="photograph-tab" class="proof-tab-content-cust">
        <div class="card" id="ovd_proofs">
            <div class="card-block">
                <div class="row">
                    <div class="custom-col-review col-md-4">
                        <div class="form-group">
                            <div class="proof-of-identity">
                                <h4>Photograph & Signatures</h4>
                                <div class="row" style="margin-bottom: 8px;">
                                    @if(substr($accountDetails['customers_photograph'],0,11) == "_DONOTSIGN_")
                                        @php
                                            $customer_image = $accountDetails['customers_photograph'];
                                        @endphp
                                    @else
                                        @php
                                            $customer_image = '_DONOTSIGN_'.$accountDetails['customers_photograph'];
                                        @endphp
                                    @endif

                                     <!-- 22May23 - For BS5 - commented below line -->
                                    <!-- <button id="rotate" class="rotate" style="margin-left: 356px;"><i class="fa fa-rotate-right"></i></button> -->
                                    <button id="rotate" class="rotate col-sm-1" style="margin-left:356px"><i class="fa fa-rotate-right"></i></button>
                                </div>
                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                    
                                    <img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$customer_image) }}" class="img-fluid photograph-zoom rotate_image">
                                </div>
                                @if(($is_review == 1) && (!isset($reviewDetails['customer_image'])))
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
                                            @if(isset($qcReviewDetails['customer_image']))
                                                <span class="review-comment">
                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                    {{$qcReviewDetails['customer_image']}}
                                                </span>
                                            @else
                                                <i class="fa fa-check"></i>
                                            @endif
                                        </span>
                                    </div>
                                    <div class=" d-flex flex-row">
                                        <div class="switch-blck" style="margin-right: 20px;">
                                            <div class="toggleWrapper">
                                                <input type="checkbox" name="customer_image_toggle" class="mobileToggle reviewComments" id="customer_image_toggle" {{$checked}} {{$disabled}}>
                                                <label for="customer_image_toggle"></label>
                                            </div>
                                        </div>
                                        <div class="comments-blck {{$display}}" style="width:100%;">
                                            <input type="text" class="form-control commentsField" id="customer_image" name="customer_image">
                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                        </div>
                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                    </div>
                                </div>
                            </div>
                       </div>
                    </div>
                    <!-- Row end -->
                    <div class="col-md-6">
                        @if(($is_review == 1) && (!isset($reviewDetails['mode_of_operation'])))
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
                                    Mode of operation :
                                    <span>
                                        {{strtoupper($accountDetails['mode_of_operation'])}}
                                        @if(count($qcReviewDetails) > 0)
                                            @if(isset($qcReviewDetails['mode_of_operation']))
                                                <span class="review-comment">
                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                     {{$qcReviewDetails['mode_of_operation']}}
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
                                                <input type="checkbox" name="mode_of_operation_toggle" class="mobileToggle reviewComments" id="mode_of_operation_toggle" {{$checked}} {{$disabled}}>
                                                <label for="mode_of_operation_toggle"></label>
                                            </div>
                                        </div>
                                        <div class="comments-blck {{$display}}">
                                            <input type="text" class="form-control commentsField" id="mode_of_operation">
                                            <i title="save" class="fa fa-floppy-o saveComments"></i>
                                        </div>
                                        <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @for($m = 1;$m<=$accountHoldersCount;$m++)
                            @php
                                $customerOvdData = (array) $customerOvdDetails[$m-1];
                            @endphp
                            @if(($is_review == 1) && (!isset($reviewDetails['mobile_number-'.$m])))
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
                                    {{ $is_huf && $m == 1 ? 'Karta/Manager Mobile No :' : ($is_huf && $m == 2 ? 'Huf Mobile No :' : "Applicant-{$m} Mobile No :") }}
                                        <div class="d-inline">
                                            <label class="maskingfield">
                                                <label>***************</label>
                                            </label>
                                            <span class="unmaskingfield" style="display: none;">
                                                <label>{{$customerOvdData['mobile_number']}}</label>
                                            </span>
                                            @if(count($qcReviewDetails) > 0)
                                                @if(isset($qcReviewDetails['mobile_number-'.$m]))
                                                <span class="review-comment">
                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                     {{$qcReviewDetails['mobile_number-'.$m]}}
                                                </span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            @endif
                                    </div>
                                    </div>
                                    <div class="detaisl-right">
                                        <div class=" d-flex flex-row">
                                            <div class="switch-blck">
                                                <div class="toggleWrapper">
                                                    <input type="checkbox" name="mobile_number_toggle-{{$m}}" class="mobileToggle reviewComments" id="mobile_number_toggle-{{$m}}" {{$checked}} {{$disabled}}>
                                                    <label for="mobile_number_toggle-{{$m}}"></label>
                                                </div>
                                            </div>
                                            <div class="comments-blck {{$display}}">
                                                <input type="text" class="form-control commentsField" id="mobile_number-{{$m}}">
                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                            </div>
                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endfor
                            @for($m = 1;$m<=$accountHoldersCount;$m++)
                            @php
                                $customerOvdData = (array) $customerOvdDetails[$m-1];
                            @endphp
                            @if(($is_review == 1) && (!isset($reviewDetails['email-'.$m])))
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
                           
                                    {{ $is_huf && $m == 1 ? 'Karta/Manager Email :' : ($is_huf && $m == 2 ? 'Huf Email :' : "Applicant-{$m} Email :") }}

                                        <div class="d-inline">
                                            <label class="maskingfield">
                                                <label>*************</label>
                                            </label>
                                            <span class="unmaskingfield" style="display: none;">
                                                <label>{{$customerOvdData['email']}}</label>
                                            </span>
                                        
                                            @if(count($qcReviewDetails) > 0)
                                                @if(isset($qcReviewDetails['email-'.$m]))
                                                <span class="review-comment">
                                                      <i class="fa fa-exclamation review-exclamation"></i>
                                                     {{$qcReviewDetails['email-'.$m]}}
                                                </span>
                                                @else
                                                    <i class="fa fa-check"></i>
                                                @endif
                                            @endif
                                    </div>
                                    </div>
                                    <div class="detaisl-right">
                                        <div class=" d-flex flex-row">
                                            <div class="switch-blck">
                                                <div class="toggleWrapper">
                                                    <input type="checkbox" name="email_toggle-{{$m}}" class="mobileToggle reviewComments" id="email_toggle-{{$m}}" {{$checked}} {{$disabled}}>
                                                    <label for="email_toggle-{{$m}}"></label>
                                                </div>
                                            </div>
                                            <div class="comments-blck {{$display}}">
                                                <input type="text" class="form-control commentsField" id="email-{{$m}}">
                                                <i title="save" class="fa fa-floppy-o saveComments"></i>
                                            </div>
                                            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
                                        </div>
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