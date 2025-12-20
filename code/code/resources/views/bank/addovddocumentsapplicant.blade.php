@php
    //$enc_fields = ['Aadhaar Photocopy','Passport','Voter ID','Driving Licence'];
    $enc_fields = [1,2,3,6,7];
    $id_proof_list = '';
    $passport_driving_expire = '';
    $id_psprt_dri_issue='';
    $add_psprt_dri_issue='';
    $passport_driving_expire_permanent = '';
    $aadhar_link = '';
    $aadhar_link_permanent = '';
    $id_proof_card_number = '';
    $first_name = '';
    $middle_name = '';
    $last_name = '';
    $short_name = '';
    $mothers_maiden_name = '';
    $mother_full_name = '';
    $father_name = '';
    $title = '';
    $religion = '';
    $gender = '';
    $father_spouse = '';
    $id_proof_osv_check = '';
    $per_address_proof_list = '';
    $add_proof_card_number = '';
    $per_address_line1 = '';
    $per_address_line2 = '';
    $per_landmark = '';
    $per_country = '';
    $per_state = '';
    $per_city = '';
    $per_pincode = '';
    $add_proof_osv_check = '';
    $address_flag = '';
    $address_per_flag = '';
    $isCurrAccount = 1;
    $cur_address_proof_list = '';
    $current_add_proof_card_number = '';
    $current_address_line1 = '';
    $current_address_line2 = '';
    $current_landmark = '';
    $current_country = '';
    $current_state = '';
    $current_city = '';
    $current_pincode = '';
    $cur_add_proof_osv_check = '';
    $customer_signature_osv_check = '';
    $class = 'text-muted-lnavs';
    $display = "";
    $proof_of_address = "";
    $proof_of_current_address = "";
    $readonly = "";
    $is_review = 0;
    $enable = "display-none";
    $osv_check = "display-none";
    $folder = '';
    $account_id = "";
    $displayClass = '';
    $disabled = '';
    $etbreadonly = "";
    $etbdisabled = "";
    $customertype = "";
    $ekyc_field_class = "";
    $spouse_disabled = '';
    $spousedisableclass = '';
    $displayClass_per = '';
    if($customerOvdDetails[$i]["marital_status"]==1){
      $spouse_disabled="disabled";
      $spousedisableclass = 'single_ovd';
    }
    $huf_num_of_coparcenars = '1';
@endphp
@php
$coparcenertype = ["Member"=>"Member","Coparcenor"=>"Coparcenor"];
@endphp
@if(count($customerOvdDetails) > 0)
    @php
        //if(Session::get('customer_type') != "ETB")
        if($customerOvdDetails[$i]['is_new_customer'] == '1'){
            $id_proof_list = $customerOvdDetails[$i]['proof_of_identity'];
            if($id_proof_list != 9){
                $id_proof_image = explode(',',$customerOvdDetails[$i]['id_proof_image']);
                $add_proof_image = explode(',',$customerOvdDetails[$i]['add_proof_image']);
            }
            $id_proof_card_number = $customerOvdDetails[$i]['id_proof_card_number'];
            $short_name = $customerOvdDetails[$i]['short_name'];
            $mothers_maiden_name = $customerOvdDetails[$i]['mothers_maiden_name'];
            $mother_full_name = $customerOvdDetails[$i]['mother_full_name'];
            $father_name = $customerOvdDetails[$i]['father_name'];
            if(isset($customerOvdDetails[$i]['father_spouse']))
            {
                $father_spouse = $customerOvdDetails[$i]['father_spouse'];
            }
            $per_address_proof_list = $customerOvdDetails[$i]['proof_of_address'];
            $add_proof_card_number = $customerOvdDetails[$i]['add_proof_card_number'];
            $per_landmark = $customerOvdDetails[$i]['per_landmark'];
            $address_flag = $customerOvdDetails[$i]['address_flag'];
            $entity_address_flag = $customerOvdDetails[$i]['address_flag'];
            $address_per_flag = $customerOvdDetails[$i]['address_per_flag'];
            if($address_flag != 1){
                $cur_address_proof_list = $customerOvdDetails[$i]['proof_of_current_address'];
                if(isset($customerOvdDetails[$i]['current_add_proof_image']))
                {
                    $current_add_proof_image = $customerOvdDetails[$i]['current_add_proof_image'];
                }
                $current_add_proof_card_number = $customerOvdDetails[$i]['current_add_proof_card_number'];
                $displayClass = '';
            }else{
                $displayClass = 'display-none';
            }

            if($entity_address_flag != 1){
                $cur_address_proof_list = $customerOvdDetails[$i]['proof_of_current_address'];
                if(isset($customerOvdDetails[$i]['current_add_proof_image']))
                {
                    $current_add_proof_image = $customerOvdDetails[$i]['current_add_proof_image'];
                }
                $current_add_proof_card_number = $customerOvdDetails[$i]['current_add_proof_card_number'];
                $entitydisplayClass = '';
            }else{
                $entitydisplayClass = 'display-none';
            }

            $current_landmark = $customerOvdDetails[$i]['current_landmark'];
            $etbreadonly = "";
            $etbdisabled = "";
            $customertype = "";
        }else{
            $etbreadonly = "readonly";
            $etbdisabled = "disabled";
            $customertype = "etb";
            $ekyc_field_class = 'disabled';
            $osv_check = "display-none";
        }
        if($id_proof_list == 9)
        {
            $ekyc_field_class = "disabled";
            $etbdisabled = "disabled";
            $osv_check = "display-none";
        }else{
            $osv_check = '';
            $ekyc_field_class = '';
        }

        if (isset($customerOvdDetails[$i]['huf_num_of_coparcenars'])) {
            $huf_num_of_coparcenars = $customerOvdDetails[$i]['huf_num_of_coparcenars'];
        }
     
        if(isset($customerOvdDetails[$i]['first_name'])){
            $first_name = $customerOvdDetails[$i]['first_name'];
        }
        if(isset($customerOvdDetails[$i]['middle_name'])){
            $middle_name = $customerOvdDetails[$i]['middle_name'];
        }
        if(isset($customerOvdDetails[$i]['last_name'])){
            $last_name = $customerOvdDetails[$i]['last_name'];
        }
        if(isset($customerOvdDetails[$i]['short_name'])){
            $short_name = $customerOvdDetails[$i]['short_name'];
        }
        if(isset($customerOvdDetails[$i]['title'])){
            $title = $customerOvdDetails[$i]['title'];
            $gender = $customerOvdDetails[$i]['gender'];        
            $id_proof_osv_check = 1;        
            $per_address_line1 = $customerOvdDetails[$i]['per_address_line1'];
            $per_address_line2 = $customerOvdDetails[$i]['per_address_line2'];        
            $per_pincode = $customerOvdDetails[$i]['per_pincode'];
            $per_country = $customerOvdDetails[$i]['per_country'];
            $per_state = $customerOvdDetails[$i]['per_state'];
            $per_city = $customerOvdDetails[$i]['per_city'];
            $per_landmark = $customerOvdDetails[$i]['per_landmark'];
            $current_landmark = $customerOvdDetails[$i]['current_landmark'];
            $add_proof_osv_check = 1;
            if($id_proof_list == 2 || $id_proof_list == 3 ){
                $passport_driving_expire = Carbon\Carbon::parse($customerOvdDetails[$i]['passport_driving_expire'])->format('d-m-Y');
                $id_psprt_dri_issue = Carbon\Carbon::parse($customerOvdDetails[$i]['id_psprt_dri_issue'])->format('d-m-Y');
            }
            if($per_address_proof_list == 2 || $per_address_proof_list == 3 ){
                $passport_driving_expire_permanent = Carbon\Carbon::parse($customerOvdDetails[$i]['passport_driving_expire_permanent'])->format('d-m-Y');
                $add_psprt_dri_issue = Carbon\Carbon::parse($customerOvdDetails[$i]['add_psprt_dri_issue'])->format('d-m-Y');
            }
            if(($id_proof_list == 1) && (isset($customerOvdDetails[$i]['aadhar_link']))  ){
                $aadhar_link = $customerOvdDetails[$i]['aadhar_link'];
            }
            //echo $aadhar_link;exit;
            //if($per_address_proof_list == 1){
              //  $aadhar_link_permanent = $customerOvdDetails[$i]['aadhar_link_permanent'];
            //}
            $current_address_line1 = $customerOvdDetails[$i]['current_address_line1'];
            $current_address_line2 = $customerOvdDetails[$i]['current_address_line2'];        
            $current_pincode = $customerOvdDetails[$i]['current_pincode'];
            $current_country = $customerOvdDetails[$i]['current_country'];
            $current_state = $customerOvdDetails[$i]['current_state'];
            $current_city = $customerOvdDetails[$i]['current_city'];
        }
        if(isset($customerOvdDetails[$i]['religion']))
        {
            $religion = $customerOvdDetails[$i]['religion'];
        }
        $cur_add_proof_osv_check = 1;
        $customer_signature_osv_check = 1;
        $account_id = $AccountIds[$i];
        $display = "display-none";
        $class = '';
        $folder = "attachments";
    @endphp 
@endif
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $enable = "";
        $osv_check = "";
        $readonly = "readonly";
        $folder = "markedattachments";
        $disabled = "disabled";
    @endphp
@endif
@php
$image_mask_blur = "";
$def_blur_image = "";
if($is_review==1){
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
}
@endphp
@php
    $is_huf = false;
    if($accountDetails['constitution'] == 'NON_IND_HUF' && $i == 2) {
        $is_huf_display = true;
        $is_huf = true;
        $title = 10;
        $titles = [10 => $titles[10]];
      
    }elseif($accountDetails['constitution'] == 'NON_IND_HUF' && $i == 1){
        if(isset($customerOvdDetails[$i+1]['huf_signatory_relation']) && $customerOvdDetails[$i+1]['huf_signatory_relation']=="Manager"){
            $genderArray = ["F"=>"Female"];
        }
        $is_huf_display = false;
    }else{
        $is_huf_display = false;
    }

    if($is_huf){
        if ($address_per_flag != 1) {
            $per_address_proof_list = $customerOvdDetails[$i]['proof_of_address'];
            $add_proof_card_number = $customerOvdDetails[$i]['add_proof_card_number'];
            $displayClass_per = '';
        } else {
            $displayClass_per = 'display-none';
        }


        if ($address_per_flag != 1) {
            $namecom = 'Same as Karta Communication Address';
            $displayCom = 'display-none';
        }else{
            $namecom = ' Same as Registered Address';
            $displayCom = 'display-none';
        }
    }
@endphp
<style>
    #plus_button,
    #minus_button {
        border-radius: 0px !important;
        padding: 7px 15px !important;
    }
    .huf_co_num_input{
        border-radius: 0px !important;
    }
    .huf_co_num{
        max-width:300px !important;
    }
</style>

<div id="tab{{$i}}" class="tab-content-cust">
    <div class="card OvdDocumentForm" id={{$i}}>
        <span class="visibility_check" id="visibility_check-{{$i}}"></span>
        
        <input type="hidden" id="applicantId-{{$i}}" value="{{$account_id}}" customertype="{{$customertype}}">
        <div class="col-lg-12">
            <input type="hidden" id="formId" name="formId" value="{{$formId}}">
            <!-- Tab panes -->
             <!-- 22May23 - For BS5 - commented below line -->
             <!-- <div class="tab-content"> -->
            <div class="tab-content px-3">
                <div class="tab-pane active" id="home3" role="tabpanel">
                    <!-- Row start -->
                    <div class="row">
                        <div class="col-lg-12 col-xl-12">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs md-tabs tabs-left b-none left-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-id="proof-of-identity-{{$i}}" data-bs-toggle="tab" href="#proof-of-identity-{{$i}}" role="tab" >Identity <i class="fa fa-angle-right bc-arrow" aria-hidden="true"></i> </a>
                                    <div class="slide"></div>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link documentstabproof addresstab" data-id="proof-of-permanent-address-{{$i}}" data-bs-toggle="tab" href="#proof-of-permanent-address-{{$i}}" role="tab">
                                        {{ $is_huf_display ? 'Registered Address' : 'Address (As per OVD)' }}
                                        <i class="fa fa-angle-right bc-arrow" aria-hidden="true"></i> </a>
                                    <div class="slide"></div>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link caddresstab" data-id="proof-of-current-address-{{$i}}" data-bs-toggle="tab" href="#proof-of-current-address-{{$i}}" role="tab">Communication Address</a>
                                    <div class="slide"></div>
                                </li>
                            </ul>
                            <!-- <ul class="float-right adb-btn-inn cyke-button">
                                <button type="button" class="btn btn-outline-grey waves-effect tooltipp" id="submit_ekyc-{{$i}}">
                                    E-KYC
                                </button>
                            </ul> -->
                            <!-- Tab panes -->
                            <div class="tab-content tabs-left-content card-block">
                                <div class="tab-pane active documentstab" id="proof-of-identity-{{$i}}" role="tabpanel">
                                  <span class="visibility_check" id="visibility_check-{{$i}}"></span>
                                    <div class="proofs-blck">
                                        <!-- <h1>Proof of Identity</h1> -->
                                        <div class="row idProof">
                                            <div class="col-lg-4 col-md-12">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group" id="upload_id_proof-{{$i}}">
                                                           {{--  <label class="uploadLabel">Upload</label> --}}
                                                            <div class="detaisl-left align-content-center ">
                                                                 <label class="uploadLabel">
                                                                    {{ $is_huf_display ? 'HUF Declaration' : 'Upload' }}
                                                                </label>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['id_proof_image-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['id_proof_image-'.$i]}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                </span>
                                                            </div>
                                                            <div class="accordion" id="accordionExampleone-{{$i}}">
                                                                <div class="card-accordion">
                                                                    <div class="card-header-accordion" id="headingOne">
                                                                        <h2 class="mb-0">
                                                                        <button class="btn btn-link btn-block text-left front" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneid-{{$i}}" aria-expanded="true" aria-controls="collapseOneid-{{$i}}">
                                                                            Front side
                                                                        </button>
                                                                        </h2>
                                                                    </div>
                                                                    <div id="collapseOneid-{{$i}}" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExampleone-{{$i}}">
                                                                        <div class="card-body-accordion">
                                                                            <div class="add-document d-flex align-items-center justify-content-around id_image_div_front" id="id_proof_image_front-{{$i}}" table="customer_ovd_details" data-seq={{$i}} name="id_proof_image">
                                                                                @if(isset($id_proof_image[0]) && $id_proof_image[0] != '')
                                                                                    <div id="upload_id_proof_div-{{$i}}"
                                                                                      class="upload_id_proof_div-{{$i}}" table="customer_ovd_details" name="id_proof_image" data-seq={{$i}}>
                                                                                        @if($id_proof_image[0] != '')
                                                                                            @if($enable == 'display-none')
                                                                                                <div class="upload-delete">
                                                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            @else
                                                                                                @if(isset($reviewDetails['id_proof_image-'.$i]))
                                                                                                <div class="upload-delete">
                                                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                                @else
                                                                                                @endif
                                                                                            @endif
                                                                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                            <img class="uploaded_image image_crop" name="id_proof_image-{{$i}}" id="document_preview_id_proof_image_front-{{$i}}" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$id_proof_image[0])}}" onerror="imgNotFound('Id proof front')">
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                @endif
                                                                                @if(isset($id_proof_image[0]) && $id_proof_image[0] != '')
                                                                                    <div class="add-document-btn adb-btn-inn display-none">
                                                                                @else
                                                                                    <div class="add-document-btn adb-btn-inn">
                                                                                @endif
                                                                               
                                                                                    <button type="button" id="proof_of_identity" class="btn btn-outline-grey waves-effect upload_document upload_front_side ekyc_field-{{$i}} {{ $is_huf ? 'is_huf' : '' }}" data-toggle="modal" 
                                                                                    data-id="id_proof_image_front-{{$i}}" data-class="AddPanDetailsField" data-name="id_proof_image-{{$i}}" table="customer_ovd_details" name="id_proof_image" data-seq={{$i}}
                                                                                    
                                                                                    data-document="Image" data-target="#upload_proof" {{$etbdisabled}}>
                                                                                        <span class="adb-icon">
                                                                                            <i class="fa fa-plus-circle"></i>
                                                                                        </span>
                                                                                        Add
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                            <input type="text" style="opacity:0" name="id_proof_image_front" id="idProofImageFront-{{$i}}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="card-accordion">
                                                                    <div class="card-header-accordion" id="headingTwoid-{{$i}}">
                                                                        <h2 class="mb-0">
                                                                            <button id="collapse_id_proof-{{$i}}" class="btn btn-link btn-block text-left collapsed back" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwoid-{{$i}}" aria-expanded="false" aria-controls="collapseTwoid-{{$i}}">
                                                                                Back side
                                                                            </button>
                                                                        </h2>
                                                                    </div>
                                                                    <div id="collapseTwoid-{{$i}}" class="collapse" aria-labelledby="headingTwoid-{{$i}}" data-parent="#accordionExampleone-{{$i}}">
                                                                        <div class="card-body-accordion">
                                                                            <div class="add-document d-flex align-items-center justify-content-around id_image_div_back" id="id_proof_image_back-{{$i}}" table="customer_ovd_details" name="id_proof_image" data-seq={{$i}}>
                                                                                @if(isset($id_proof_image[1]))
                                                                                    <div id="upload_id_proof_div-{{$i}}" 
                                                                                    class="upload_id_proof_div-{{$i}}"
                                                                                    table="customer_ovd_details" name="id_proof_image" data-seq={{$i}}>
                                                                                        @if($enable == 'display-none')
                                                                                                <div class="upload-delete">
                                                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                    </button>
                                                                                                 </div>
                                                                                        @else
                                                                                            @if(isset($reviewDetails['id_proof_image-'.$i]))
                                                                                                <div class="upload-delete">
                                                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                    </button>
                                                                                                 </div>
                                                                                            @else
                                                                                            @endif
                                                                                        @endif
                                                                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img class="uploaded_image id_proof_image_back" name="id_proof_image-{{$i}}" id="document_preview_id_proof_image_back-{{$i}}" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$id_proof_image[1])}}" onerror="imgNotFound('Id proof back')">
                                                                                    </div>
                                                                                    </div>
                                                                                @endif
                                                                                @if(isset($id_proof_image[1]) && $id_proof_image[1] != '')
                                                                                    <div class="add-document-btn adb-btn-inn display-none">
                                                                                @else
                                                                                    <div class="add-document-btn adb-btn-inn">
                                                                                @endif
                                                                                    <button type="button" id="proof_of_identity" class="btn btn-outline-grey waves-effect upload_document upload_back_side ekyc_field-{{$i}} {{ $is_huf ? 'is_huf' : '' }}" data-toggle="modal" 
                                                                                    data-id="id_proof_image_back-{{$i}}" data-class="AddPanDetailsField" data-name="id_proof_image-{{$i}}"  data-document="Image" data-target="#upload_proof">
                                                                                        <span class="adb-icon">
                                                                                            <i class="fa fa-plus-circle"></i>
                                                                                        </span>
                                                                                        Add
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- <div class="osv-done-blck"> -->
                                                                @if (!$is_huf_display)
                                                            <div class="osv-done-blck {{$osv_check}}">
                                                                <label class="radio">
                                                                    <input type="checkbox" class="osv_done_check" name="id_proof_osv_check[{{$i}}]" id="id_proof_osv_check-{{$i}}" {{($id_proof_osv_check == '1')? "checked" : ""}} {{$disabled}} {{$etbdisabled}}>
                                                                    <span class="lbl padding-8">Confirm Original Seen and Verified</span>
                                                                </label>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="custom-col-review proof-of-identity col-lg-8 col-md-12">
                                                <div class="row">
                                                @if (!$is_huf_display)
                                                    <div class="details-custcol-row col-md-4">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Proof of Identity</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['proof_of_identity-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['proof_of_identity-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                {!! Form::select('id_proof_list',$idProofOVDs,$id_proof_list,array('class'=>'form-control id_proof AddOvdDetailsField id_proof_list',
                                                                    'table'=>'customer_ovd_details','id'=>'proof_of_identity-'.$i,'name'=>'proof_of_identity','placeholder'=>'','proof_type'=>'id')) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                 

                                                    <div class="details-custcol-row col-md-4">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Enter Number</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['id_proof_card_number-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['id_proof_card_number-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm" id="id_proof_number-{{$i}}">
                                                            <div class="comments-blck">
                                                                @if(in_array($id_proof_list ,$enc_fields))
                                                                    <input type="text" class="aadhaar_mask enc_input form-control AddOvdDetailsField {{$is_review==1 ?  "unmaskingfield": ""}}" {{ $is_review==1 ? 'style=display:none;':''}} table="customer_ovd_details" name="id_proof_card_number" id="id_proof_card_number-{{$i}}" value="{{$id_proof_card_number}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();"  oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');" > 
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i> 
                                                                    @if($is_review==1)
                                                                        <input type="text" class="form-control maskingfield" value="**********" {{$readonly}} {{$etbreadonly}}>
                                                                    @endif
                                                                @else
                                                                    <input type="text" class="aadhaar_mask enc_input form-control AddOvdDetailsField"  table="customer_ovd_details" name="id_proof_card_number" id="id_proof_card_number-{{$i}}" value="{{$id_proof_card_number}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();"  oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');" > 
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div> 
                                                @endif
                                                                                                        
                                                    @if(($i == 1) && (Session::get('accountType') != 3))
                                                        @if($id_proof_list == 1)
                                                            <div class="details-custcol-row col-md-4" id="aadhar_link-{{$i}}">
                                                        @else
                                                            <div class="details-custcol-row col-md-4 display-none" id="aadhar_link-{{$i}}">
                                                        @endif
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center">
                                                                    <span class="{{$enable}}">
                                                                        @if(isset($reviewDetails['aadhar_link-'.$i]))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['aadhar_link-'.$i]}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>                                                   
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck mt-4">
                                                                    @if(Session::get('is_review') == 1)
                                                                        <input type="hidden" class="form-control AddOvdDetailsField aadhar_link" table="customer_ovd_details" name="aadhar_link" id="aadhar_link-{{$i}}" value="Y" {{ ($aadhar_link== "1")? "checked" : "" }}  {{$readonly}} {{$etbreadonly}}>
                                                                    @else
                                                                        <input type="checkbox" class="form-control AddOvdDetailsField aadhar_link" table="customer_ovd_details" name="aadhar_link" id="aadhar_link-{{$i}}" value="Y" {{ ($aadhar_link== "1")? "checked" : "" }}  {{$readonly}} {{$etbreadonly}}>
                                                                    @endif
                                                                    <span class="lbl padding-8"> Link Aadhaar to Account</span>
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($id_proof_list == 2 || $id_proof_list == 3 )
                                                        <div class="details-custcol-row col-md-2" id="passport_driving-{{$i}}">
                                                    @else
                                                        <div class="details-custcol-row col-md-2 display-none" id="passport_driving-{{$i}}">
                                                    @endif
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Expiry Date</p> 
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['passport_driving_expire-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['passport_driving_expire-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>                                                   
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField passport_driving_expire" table="customer_ovd_details" onfocusout="simulatedatechange(this)" id="passport_driving_expire-{{$i}}" name="passport_driving_expire" value="{{$passport_driving_expire}}" {{$readonly}} {{$etbreadonly}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if($id_proof_list == 2 || $id_proof_list == 3 )
                                                        <div class="details-custcol-row col-md-2" id="passport_driving_issue-{{$i}}">
                                                    @else
                                                        <div class="details-custcol-row col-md-2 display-none" id="passport_driving_issue-{{$i}}">
                                                    @endif
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Issue Date</p> 
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['id_psprt_dri_issue-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['id_psprt_dri_issue-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>                                                   
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField id_psprt_dri_issue" table="customer_ovd_details" onfocusout="simulatedatechange(this)" id="id_psprt_dri_issue-{{$i}}" name="id_psprt_dri_issue" value="{{$id_psprt_dri_issue}}" {{$readonly}} {{$etbreadonly}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if($id_proof_list == 9)
                                                        <div class="details-custcol-row adb-btn-inn cyke-button col-md-4" id="ekycDiv-{{$i}}">
                                                    @else
                                                        <div class="details-custcol-row adb-btn-inn cyke-button col-md-4 display-none" id="ekycDiv-{{$i}}">
                                                    @endif
                                                        <button type="button" class="btn btn-outline-grey waves-effect tooltipp" id="submit_ekyc-{{$i}}">
                                                            E-KYC
                                                        </button>
                                                    </div>

                                                    @if (!$is_huf_display)
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Gender</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['gender-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['gender-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                {!! Form::select('gender',$genderArray,$gender,array('class'=>'form-control  gender AddOvdDetailsField ekyc_field-'.$i,
                                                                        'table'=>'customer_ovd_details','id'=>'gender-'.$i,'name'=>'gender','placeholder'=>'',$ekyc_field_class)) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Title</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['title-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['title-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                {!! Form::select('title',$titles,$title,array('class'=>
                                                                    'form-control title  AddOvdDetailsField ekyc_field-'.$i,'table'=>'customer_ovd_details','id'=>'title-'.$i,'name'=>'title','placeholder'=>'',$ekyc_field_class)) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                @if(isset($globaluser_dob_ms[$i-1]))
                                                                <input type="hidden" name="applicant_ms" id="applicant_ms-{{$i}}" value="{{$globaluser_dob_ms[$i-1]->marital_status}}" table="customer_ovd_details">

                                                                    <input type="hidden" name="applicant_dob" id="applicant_dob-{{$i}}" value="{{$globaluser_dob_ms[$i-1]->dob}}" table="customer_ovd_details">
                                                                @endif

                                                            </div>
                                                        </div>
                                                    </div>
                                                        
                                                    <div class="details-custcol-row {{ $is_huf_display ? 'col-md-6' : 'col-md-4' }}">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">
                                                                    {{ $is_huf_display ? 'HUF Name' : 'First Name' }}
                                                                </p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['first_name-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['first_name-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField first_name ekyc_field-{{$i}}" table="customer_ovd_details" name="first_name" id="first_name-{{$i}}" value="{{$first_name}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" 
                                                                @if($is_huf_display) 
                                                                oninput="this.value = this.value.replace(/[^a-zA-Z!@#$%^&*()_+={}\[\]:;,.?<>~`\\|/-\s]/g, '').replace(/(\..*)\./g, '$1');"
                                                                @else
                                                                oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" 
                                                                @endif
                                                                {{$ekyc_field_class}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                            
                                                        </div>
                                                    </div>

                                                    {{-- coparcner start here --}}

                                                    @if ($is_huf_display)
    <div class="details-custcol-row col-md-12">
        <div class="details-custcol-row-top d-flex editColumnDiv">
            <div class="detaisl-left d-flex align-content-center" >
                <p class="lable-cus">Number of Coparcenor</p>
                <span class="{{ $enable }}">
                    @if (isset($reviewDetails['huf_num_of_coparcenars-' . $i]))
                        <i class="fa fa-times"></i>
                        {{ $reviewDetails['huf_num_of_coparcenars-' . $i] }}
                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
            @else
                <i class="fa fa-check"></i>
@endif
</span>
</div>
</div>
<div class="details-custcol-row-bootm huf_co_num">
    <div class="comments-blck d-flex align-items-center">
        <button type="button" id ="minus_button" count="{{ $i }}"
            class="btn btn-primary ml-2 huf_co_num_bnt_remove">-</button>
        <input type="text"
            class="form-control AddOvdDetailsField huf_co_num_input"
            table="non_ind_huf" name="huf_num_of_coparcenars"
            id="huf_num_of_coparcenars-{{ $i }}" value="{{ $huf_num_of_coparcenars }}"
            readonly {{ $etbreadonly }} {{ $ekyc_field_class }}>
        <button type="button" id ="plus_button" count="{{ $i }}"
            class="btn btn-primary ml-2 huf_co_num_bnt">+</button>
    </div>
</div>
</div>
@if (!empty($huf_cop_row))
    @foreach ($huf_cop_row as $k => $val)
    @php 
    $val = (array) $val;
    $k1 = $k+1;
    @endphp
       <div class="details-custcol-row col-md-12">
        <input type="hidden" id="huf_co_name_id{{ $k1 }}-2" value = "{{$val["id"]}}">
            <div class="row m-0">
                <div class="col-3">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Coparcenor Name -{{ $k1 }}</p>
                            <span class="{{ $enable }}">
                                @if (isset($reviewDetails["coparcenar_name$k1-2"]))
                                    <i class="fa fa-times"></i>
                                    {{ $reviewDetails["coparcenar_name$k1-2"] }}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
                             @else
                            <i class="fa fa-check"></i>
                            @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control AddHufCoparcenarNameField huf_co_name"
                                table="non_ind_huf" name="coparcenar_name"
                                id="coparcenar_name_field{{ $k1 }}-2" value="{{ $val["coparcenar_name"]  }}"
                                onkeyup="this.value = this.value.toUpperCase();">
                                
                            <i title="save" coparcenar_id="{{$val["id"]}}" class="fa fa-floppy-o display-none updateColumn coparcenar c_name"></i>
                        </div>
                    </div>
                </div>
            
                <div class="col-3">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Type -{{ $k1 }}</p>
                         
                            <span class="{{ $enable }}">
                                @if (isset($reviewDetails["coparcener_type$k1-2"]))
                                    <i class="fa fa-times"></i>
                                    {{ $reviewDetails["coparcener_type$k1-2"] }}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
                          
                             @else
                            <i class="fa fa-check"></i>
                            @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('coparcener_type', $coparcenertype,$val["coparcener_type"], ['class' => 'form-control coparcener_type', 'table' => 'non_ind_huf', 'id' => "coparcenar_type_field$k1-2", 'name' => 'coparcener_type', 'placeholder' => '']) !!}

                            <i title="save" coparcenar_id="{{$val["id"]}}" class="fa fa-floppy-o display-none updateColumn coparcenar c_type"></i>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Coparcenor Relationship -{{ $k1 }}</p>
                         
                            <span class="{{ $enable }}">
                                @if (isset($reviewDetails["huf_relation$k1-2"]))
                                    <i class="fa fa-times"></i>
                                    {{ $reviewDetails["huf_relation$k1-2"] }}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
                          
                             @else
                            <i class="fa fa-check"></i>
                            @endif
                            </span>
                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            {!! Form::select('coparcenar_rel', $huf_relation,$val["huf_relation"], ['class' => 'form-control huf_relation', 'table' => 'non_ind_huf', 'id' => "coparcenar_rel_field$k1-2", 'name' => 'huf_relation', 'placeholder' => '']) !!}
                            <i title="save" coparcenar_id="{{$val["id"]}}" class="fa fa-floppy-o display-none updateColumn coparcenar c_rel"></i>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="details-custcol-row-top d-flex editColumnDiv">
                        <div class="detaisl-left d-flex align-content-center ">
                            <p class="lable-cus">Coparcenor DOB -{{ $k1 }}</p>
                           
                            <span class="{{ $enable }}">
                                @if (isset($reviewDetails["dob$k1-$i"]))
                                    <i class="fa fa-times"></i>
                                    {{ $reviewDetails["dob$k1-2"] }}
                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a>
                           
                             @else
                            <i class="fa fa-check"></i>
                            @endif
                            </span>

                        </div>
                    </div>
                    <div class="details-custcol-row-bootm">
                        <div class="comments-blck">
                            <input type="text" class="form-control dob"
                                table="non_ind_huf" name="dob" id="coparcenar_dob_field{{ $k1 }}-2"
                                value="{{$val["dob"]}}" onkeyup="this.value = this.value.toUpperCase();">
                            <i title="save" coparcenar_id="{{$val["id"]}}" class="fa fa-floppy-o display-none updateColumn coparcenar c_dob"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="details-custcol-row col-md-12">
        <div class="row m-0">
            <div class="col-3">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center ">
                        <p class="lable-cus">Coparcenor Name -1</p>
                       
                        

                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        <input type="text" class="form-control AddHufCoparcenarNameField huf_co_name"
                            table="non_ind_huf" name="coparcenar_name" id="coparcenar_name_field1-2"
                            value="" onkeyup="this.value = this.value.toUpperCase();">
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center ">
                        <p class="lable-cus">Type -1</p>
                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        {!! Form::select('coparcener_type', $coparcenertype,"", ['class' => 'form-control coparcener_type', 'table' => 'non_ind_huf', 'id' => "coparcenar_type_field1-2", 'name' => 'coparcener_type', 'placeholder' => '']) !!}
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>

                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center ">
                        <p class="lable-cus">Coparcenor Relationship -1</p>
                       
                       

                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                    
                        {!! Form::select('coparcenar_rel', $huf_relation,"", ['class' => 'form-control', 'table' => 'non_ind_huf', 'id' => "coparcenar_rel_field1-2", 'name' => 'huf_relation', 'placeholder' => '']) !!}
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center ">
                        <p class="lable-cus">Coparcenor DOB -1</p>
                        {{-- <span class="display-none">
                            <i class="fa fa-check"></i>
                        </span> --}}

                        
                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        <input type="text" class="form-control dob"
                            table="non_ind_huf" name="dob" id="coparcenar_dob_field1-2"
                            value="">
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endif

{{-- coparcner endhere --}}

                                                    @if (!$is_huf_display)
                                                    <div class="details-custcol-row col-md-4">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Middle Name (Optional)</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['middle_name-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['middle_name-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField ekyc_field-{{$i}}" table="customer_ovd_details" name="middle_name" id="middle_name-{{$i}}" value="{{$middle_name}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" {{$ekyc_field_class}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-4">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Last Name</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['last_name-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['last_name-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField last_name ekyc_field-{{$i}}" table="customer_ovd_details" name="last_name" id="last_name-{{$i}}" value="{{$last_name}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" {{$ekyc_field_class}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">                                                          
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                            @if(($customerOvdDetails[$i]['is_new_customer']==0))
                                                                <p class="lable-cus">Short Name / Emboss Name</p>
                                                            @else
                                                                <p class="lable-cus">Short Name</p>
                                                            @endif 
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['short_name-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['short_name-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField" table="customer_ovd_details" name="short_name" id="short_name-{{$i}}" value="{{$short_name}}" {{$readonly}}  onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" maxlength="24">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Mother's Maiden Name</p> 
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['mothers_maiden_name-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['mothers_maiden_name-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>                                                   
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField" table="customer_ovd_details" id="mothers_maiden_name-{{$i}}" name="mothers_maiden_name" value="{{$mothers_maiden_name}}" maxlength="50"  onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}} {{$etbreadonly}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-4">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Mother's Full Name</p> 
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['mother_full_name-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['mother_full_name-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>                                                   
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField" table="customer_ovd_details" id="mother_full_name-{{$i}}" name="mother_full_name" value="{{$mother_full_name}}"  onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}} {{$etbreadonly}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="details-custcol-row col-md-2">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <!-- <p class="lable-cus">Gender</p> -->
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['father_spouse-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['father_spouse-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck mt-2 ml-4 father_spouse_div">
                                                                <div class="radio-selection">
                                                                    <label class="radio d-block">
                                                                        <input class="AddOvdDetailsField father_spouse" type="radio" name="father_spouse[{{$i}}]" id="father_spouse-{{$i}}" value="01" {{ ($father_spouse=="01")? "checked" : "" }} {{$disabled}} {{$etbdisabled}}>
                                                                        <span class="lbl padding-8">Father </span>
                                                                    </label>
                                                                    <label class="radio">

                                                                        <input class="AddOvdDetailsField father_spouse {{$spousedisableclass}}" type="radio" name="father_spouse[{{$i}}]" id="father_spouse-{{$i}}" value="02" {{ ($father_spouse=="02")? "checked" : "" }} {{$disabled}} {{$spouse_disabled}} {{$etbdisabled}}>


                                                                        <span class="lbl padding-8">Spouse</span>
                                                                    </label>
                                                                </div>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Father / Spouse Name</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['father_name-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['father_name-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                <input type="text" class="form-control AddOvdDetailsField" table="customer_ovd_details" name="father_name" id="father_name-{{$i}}" value="{{$father_name}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z ]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Religion</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['religion-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['religion-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                {!! Form::select('religion',$religions,$religion,array('class'=>
                                                                    'form-control religion AddOvdDetailsField','table'=>'customer_ovd_details','id'=>'religion-'.$i,'name'=>'religion','placeholder'=>'')) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 text-center mt-3 mb-3">
                                                <!-- <div class="col-md-6"> -->
                                                    {{-- <a href="{{route('addaccount')}}" class="btn btn-outline-grey mr-3">Back</a> --}}
                                                <!-- </div> -->
                                                <!-- <div class="col-md-6"> -->
                                                    <!-- <a href="javascript:void(0)" class="btn btn-primary documents">Save and Continue</a> -->
                                                    <a href="javascript:void(0)" class="btn btn-primary identity huf_identity-{{$i}}" data-id="identity-{{$i}}" id="idProofNext" tab="proof-of-permanent-address-{{$i}}">Next</a>
                                                <!-- </div> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane documentstab" id="proof-of-permanent-address-{{$i}}" role="tabpanel">
                                  <span class="visibility_check" id="visibility_check-{{$i}}"></span>
                                  @if($is_huf)
                                  <label class="radio-selection mt-2 mb-2" for="address_per_flag-{{ $i }}">
                                    <input type="checkbox" class="AddOvdDetailsField address_per_flag_huf" id="address_per_flag-{{ $i }}" name="address_per_flag-{{ $i }}" {{ $address_per_flag == 1 ? 'checked' : '' }} {{ $disabled }}>
                                    <span class="lbl padding-8">Same as Karta Address</span>
                                </label>
                                @endif

                                    <div class="proofs-blck">
                                        <!-- <h1>Proof of Identity</h1> -->
                                        <div class="row perAddProof">
                                            <div class="col-md-4">
                                                <div class="form-group {{ $displayClass_per }}" id="upload_per_address_proof-{{$i}}">
                                                    {{-- <label class="uploadLabel">Upload</label> --}}
                                                    <div class="detaisl-left align-content-center ">
                                                       <label class="uploadLabel">Upload</label>
                                                       <span class="{{$enable}}">
                                                       @if(isset($reviewDetails['add_proof_image-'.$i]))
                                                       <i class="fa fa-times"></i>
                                                       {{$reviewDetails['add_proof_image-'.$i]}}
                                                       <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                       @else
                                                       <i class="fa fa-check"></i>
                                                       @endif
                                                       </span>
                                                    </div>
                                                    <div class="accordion" id="accordionExample-{{$i}}">
                                                                <div class="card-accordion">
                                                                    <div class="card-header-accordion" id="headingOne">
                                                                        <h2 class="mb-0">
                                                                        <button id="collapse_add_proof_front" class="btn btn-link btn-block text-left front"  type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{$i}}" aria-expanded="true" aria-controls="collapseOne-{{$i}}">
                                                                            Front side
                                                                        </button>
                                                                        </h2>
                                                                    </div>
                                                                    <div id="collapseOne-{{$i}}" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample-{{$i}}">
                                                                        <div class="card-body-accordion">
                                                                            <div class="add-document d-flex align-items-center justify-content-around id_image_div" id="add_proof_image_front-{{$i}}" table="customer_ovd_details" name="add_proof_image" data-seq={{$i}}>
                                                                                @if(isset($add_proof_image[0]) && $add_proof_image[0] != '')
                                                                                    <div id="upload_per_add_proof_frontdiv-{{$i}}" table="customer_ovd_details" name="add_proof_image" data-seq={{$i}}>
                                                                                @else
                                                                                    <div id="upload_per_add_proof_frontdiv-{{$i}}" class="display-none" table="customer_ovd_details" name="add_proof_image" data-seq="{{$i}}">
                                                                                @endif
                                                                                    @if($enable == 'display-none')
                                                                                                <div class="upload-delete">
                                                                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage" >
                                                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                </button>
                                                                                                </div>
                                                                                    @else
                                                                                        @if(isset($reviewDetails['add_proof_image-'.$i]))
                                            
                                                                                            <div class="upload-delete">
                                                                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        @else
                                                                                        @endif
                                                                                    @endif
                                                                                    @if((isset($add_proof_image[0])) && ($add_proof_image[0] != ''))
                                                                                        @php
                                                                                            $imagePath  = '/images'.$folder.'/'.$formId.'/'.$add_proof_image[0];
                                                                                            if(Session::get('is_review') == 1){
                                                                                                if(File::exists(storage_path('/uploads/markedattachments/'.$formId.'/'.$add_proof_image[0]))){
                                                                                                   $imagePath  = '/images'.$folder.'/'.$formId.'/'.$add_proof_image[0];
                                                                                                }else{
                                                                                                   $imagePath  = '/imagesattachments/'.$formId.'/'.$add_proof_image[0];
                                                                                                }
                                                                                            }
                                                                                        @endphp
                                                                                        <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img class="uploaded_image add_proof_image_front" name="add_proof_image-{{$i}}" id="document_preview_add_proof_image_front-{{$i}}" src="{{URL::to($imagePath)}}" onerror="imgNotFound('Add proof front')">
                                                                                         </div>
                                                                                    @else
                                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img class="uploaded_image add_proof_image_front" name="add_proof_image-{{$i}}" id="document_preview_add_proof_image_front-{{$i}}" src="">
                                                                                         </div>
                                                                                    @endif
                                                                                </div>
                                                                            
                                                                                @if((isset($add_proof_image[0])) && ($add_proof_image[0] != ''))
                                                                                    <div class="add-document-btn adb-btn-inn display-none">
                                                                                @else
                                                                                    <div class="add-document-btn adb-btn-inn">
                                                                                @endif
                                                                                    <button type="button" id="proof_of_address" class="btn btn-outline-grey waves-effect upload_document upload_front_side ekyc_field-{{$i}} perm_ekyc_field-{{$i}}" data-toggle="modal" 
                                                                                    data-id="add_proof_image_front-{{$i}}" data-class="AddPanDetailsField" data-name="add_proof_image-{{$i}}"  data-document="Image" data-target="#upload_proof" {{$etbdisabled}}  table="customer_ovd_details" name="add_proof_image" data-seq={{$i}}>
                                                                                        <span class="adb-icon">
                                                                                            <i class="fa fa-plus-circle"></i>
                                                                                        </span>
                                                                                        Add
                                                                                    </button>
                                                                                </div>                                               
                                                                            </div>
                                                                            <input type="text" style="opacity:0" name="add_proof_image_front" id="addProofImageFront-{{$i}}" >
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="card-accordion">
                                                                    <div class="card-header-accordion" id="headingTwo-{{$i}}">
                                                                        <h2 class="mb-0">
                                                                            <button id="collapse_add_proof-{{$i}}" class="btn btn-link btn-block text-left collapsed back" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwoo-{{$i}}" aria-expanded="false" aria-controls="collapseTwo-{{$i}}">
                                                                                back side
                                                                            </button>
                                                                        </h2>
                                                                    </div>
                                                                    <div id="collapseTwoo-{{$i}}" class="collapse" aria-labelledby="headingTwo-{{$i}}" data-parent="#accordionExample-{{$i}}">
                                                                        <div class="card-body-accordion">
                                                                            <div class="add-document d-flex align-items-center justify-content-around id_image_div" id="add_proof_image_back-{{$i}}" table="customer_ovd_details" name="add_proof_image" data-seq={{$i}}>
                                                                                @if(isset($add_proof_image[1]) && $add_proof_image[1] != '')
                                                                                    <div id="upload_per_add_proof_backdiv-{{$i}}">
                                                                                @else
                                                                                    <div id="upload_per_add_proof_backdiv-{{$i}}" class="display-none">
                                                                                @endif  
                                                                                 @if($enable == 'display-none')
                                                                                             <div class="upload-delete">
                                                                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                </button>
                                                                                             </div>
                                                                                    @else    
                                                                                        @if(isset($reviewDetails['add_proof_image-'.$i]))
                                                                                            <div class="upload-delete">
                                                                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        @else
                                                                                        @endif                                                            
                                                                                    @endif
                                                                                    @if((isset($add_proof_image[1])) && ($add_proof_image[1] != ''))
                                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img class="uploaded_image add_proof_image_back" name="add_proof_image-{{$i}}" id="document_preview_add_proof_image_back-{{$i}}" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$add_proof_image[1])}}" onerror="imgNotFound('Add proof back')">
                                                                                         </div>
                                                                                    @else
                                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img class="uploaded_image add_proof_image_back" name="add_proof_image-{{$i}}" id="document_preview_add_proof_image_back-{{$i}}" src="">
                                                                                         </div>
                                                                                    @endif
                                                                                </div>
                                                                                @if((isset($add_proof_image[1])) && ($add_proof_image[1] != ''))
                                                                                    <div class="add-document-btn adb-btn-inn display-none">
                                                                                @else
                                                                                    <div class="add-document-btn adb-btn-inn">
                                                                                @endif
                                                                                    <button type="button" id="proof_of_address" class="btn btn-outline-grey waves-effect upload_document upload_back_side ekyc_field-{{$i}} perm_ekyc_field-{{$i}}" data-toggle="modal" 
                                                                                    data-id="add_proof_image_back-{{$i}}" data-class="AddPanDetailsField" data-name="add_proof_image-{{$i}}"  data-document="Image" data-target="#upload_proof"{{$etbdisabled}} table="customer_ovd_details" name="add_proof_image" data-seq={{$i}}>
                                                                                        <span class="adb-icon">
                                                                                            <i class="fa fa-plus-circle"></i>
                                                                                        </span>
                                                                                        Add
                                                                                    </button>
                                                                                </div>                                               
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                                                                                                  
                                                        <div class="osv-done-blck {{$osv_check}}">
                                                            <label class="radio">
                                                                <input type="checkbox" class="osv_done_check" name="add_proof_osv_check[{{$i}}]" id="add_proof_osv_check-{{$i}}" {{($add_proof_osv_check == '1')? "checked" : ""}} {{$disabled}} {{$etbdisabled}}>
                                                                <span class="lbl padding-8">Confirm Original Seen and Verified</span>
                                                            </label>
                                                    </div>
                                                  
                                                </div>
                                            </div>
                                            <div class="custom-col-review proof-of-identity col-md-8 proof_of_address">
                                                <div class="row">
                                                    <div class="details-custcol-row {{ $displayClass_per }} {{ $is_huf_display ? 'col-md-6 per_address_proof_huf' : 'col-md-4' }}">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">
                                                                    {{ $is_huf_display ? 'Proof of Registered Address' : 'Proof of Permanent Address' }}
                                                                </p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['proof_of_address-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['proof_of_address-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            @if($is_huf)
                                                            <div class="comments-blck">
                                                                {!! Form::select('proof_of_address',$huf_karta_curnt_add,$per_address_proof_list,array('class'=>'form-control per_address_proof AddOvdDetailsField per_address_proof_list ekyc_field-'.$i,
                                                                        'table'=>'customer_ovd_details','id'=>'proof_of_address-'.$i,'name'=>'proof_of_address','placeholder'=>'','proof_type'=>'per_address',$ekyc_field_class)) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                            @else
                                                            <div class="comments-blck">
                                                                {!! Form::select('proof_of_address',$addressProofOVDs,$per_address_proof_list,array('class'=>'form-control per_address_proof AddOvdDetailsField per_address_proof_list ekyc_field-'.$i,
                                                                        'table'=>'customer_ovd_details','id'=>'proof_of_address-'.$i,'name'=>'proof_of_address','placeholder'=>'','proof_type'=>'per_address',$ekyc_field_class)) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="details-custcol-row {{ $displayClass_per }} {{ $is_huf_display ? 'col-md-6' : 'col-md-4' }}">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Enter Number</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['add_proof_card_number-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['add_proof_card_number-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm" id="per_address_proof_number-{{$i}}">
                                                            <div class="comments-blck">
                                                                @if(in_array($per_address_proof_list,$enc_fields))
                                                                <input type="text" class="aadhaar_mask enc_input form-control AddOvdDetailsField ekyc_field-{{$i}} {{$is_review==1 ?  "unmaskingfield": ""}}" {{ $is_review==1 ? 'style=display:none;':''}} table="customer_ovd_details" name="add_proof_card_number" id="add_proof_card_number-{{$i}}" value="{{$add_proof_card_number}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');" {{$ekyc_field_class}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                 @if($is_review==1)
                                                                <input type="text" class="form-control maskingfield" value="*************" {{$readonly}} {{$etbreadonly}}>
                                                                @endif
                                                                @else
                                                                <input type="text" class="aadhaar_mask enc_input form-control AddOvdDetailsField ekyc_field-{{$i}}" table="customer_ovd_details" name="add_proof_card_number" id="add_proof_card_number-{{$i}}" value="{{$add_proof_card_number}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');" {{$ekyc_field_class}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                @endif
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                              
                                                @if(($i == 1) && (Session::get('accountType') != 3))
                                                    @if($proof_of_address == 1)
                                                        <div class="details-custcol-row col-md-4" id="aadhar_link_permanent-{{$i}}">
                                                    @else
                                                        <div class="details-custcol-row col-md-4 display-none" id="aadhar_link_permanent-{{$i}}">
                                                    @endif
                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                    <div class="detaisl-left d-flex align-content-center ">
                                                    <span class="{{$enable}}">
                                                        @if(isset($reviewDetails['aadhar_link_permanent-'.$i]))
                                                            <i class="fa fa-times"></i>
                                                            {{$reviewDetails['aadhar_link_permanent-'.$i]}}
                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                        @else
                                                            <i class="fa fa-check"></i>
                                                        @endif
                                                    </span>
                                                </div>                                                   
                                            </div>
                                            <div class="details-custcol-row-bootm">
                                                <div class="comments-blck mt-4">
                                                    <input type="checkbox" class="form-control AddOvdDetailsField aadhar_link_permanent" table="customer_ovd_details" name="aadhar_link_permanent" id="aadhar_link_permanent-{{$i}}" value="1" {{ ($aadhar_link_permanent==1)? "checked" : "" }}  {{$readonly}} {{$etbreadonly}}>
                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                         @if($per_address_proof_list == 9)
                                                <div class="details-custcol-row col-md-4" id="ekyc_permanent-{{$i}}">
                                            @else
                                                <div class="details-custcol-row col-md-4 display-none" id="ekyc_permanent-{{$i}}">
                                            @endif

                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                <div class="detaisl-left d-flex align-content-center ">
                                                    <div class="details-custcol-row adb-btn-inn cyke-button col-md-4">
                                                        <button type="button" class="btn btn-outline-grey waves-effect tooltipp submit_ekyc_perm_add" id="submit_perm_ekyc-{{$i}}">
                                                            E-KYC
                                                        </button>
                                                    </div>
                                                </div>                                                   
                                            </div>
                                        </div>        


                                        @if($per_address_proof_list == 2 || $per_address_proof_list == 3 )
                                                <div class="details-custcol-row col-md-2 {{ $displayClass_per}}" id="passport_driving_permanent-{{$i}}">
                                            @else
                                                <div class="details-custcol-row col-md-2 display-none" id="passport_driving_permanent-{{$i}}">
                                            @endif

                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                    <div class="detaisl-left d-flex align-content-center ">
                                                        <p class="lable-cus">Expiry Date</p>
                                                        <span class="{{$enable}}">
                                                            @if(isset($reviewDetails['passport_driving_expire_permanent-'.$i]))
                                                                <i class="fa fa-times"></i>
                                                                {{$reviewDetails['passport_driving_expire_permanent-'.$i]}}
                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                            @else
                                                                <i class="fa fa-check"></i>
                                                            @endif
                                                        </span>
                                                    </div>                                                   
                                                </div>
                                                    <div class="details-custcol-row-bootm">
                                                        <div class="comments-blck">
                                                            <input type="text" class="form-control AddOvdDetailsField passport_driving_expire_permanent" table="customer_ovd_details" onfocusout="simulatedatechange(this)" id="passport_driving_expire_permanent-{{$i}}" name="passport_driving_expire_permanent" value="{{$passport_driving_expire_permanent}}" {{$readonly}} {{$etbreadonly}}>
                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                        </div>
                                                    </div>
                                                </div>

                                            @if($per_address_proof_list == 2 || $per_address_proof_list == 3 )
                                            <div class="details-custcol-row col-md-2 {{ $displayClass_per}}" id="passport_driving_issue_permanent-{{$i}}">
                                            @else
                                                <div class="details-custcol-row col-md-2 display-none" id="passport_driving_issue_permanent-{{$i}}">
                                            @endif

                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                    <div class="detaisl-left d-flex align-content-center ">
                                                        <p class="lable-cus">Issue Date</p>
                                                        <span class="{{$enable}}">
                                                            @if(isset($reviewDetails['add_psprt_dri_issue-'.$i]))
                                                                <i class="fa fa-times"></i>
                                                                {{$reviewDetails['add_psprt_dri_issue-'.$i]}}
                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                            @else
                                                                <i class="fa fa-check"></i>
                                                            @endif
                                                        </span>
                                                    </div>                                                   
                                                </div>
                                                    <div class="details-custcol-row-bootm">
                                                        <div class="comments-blck">
                                                            <input type="text" class="form-control AddOvdDetailsField add_psprt_dri_issue" table="customer_ovd_details" onfocusout="simulatedatechange(this)" id="add_psprt_dri_issue-{{$i}}" name="add_psprt_dri_issue" value="{{$add_psprt_dri_issue}}" {{$readonly}} {{$etbreadonly}}>
                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                        </div>

                                                    </div>
                                                </div>

                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Address Line1</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['per_address_line1-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['per_address_line1-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{ $displayClass_per }}">
                                                                <input type="text" class="form-control input-capitalize AddOvdDetailsField ekyc_field-{{$i}} perm_ekyc_field-{{$i}}" table="customer_ovd_details" id="per_address_line1-{{$i}}" name="per_address_line1" value="{{$per_address_line1}}"  onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = addressFieldValidation(this);" {{$readonly}} {{$etbreadonly}} {{$ekyc_field_class}} maxlength="45">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                <span class="error-message" style="color: red; display: none;">Total address length should be more than 18 characters.</span>
                                                            </div>
                                                            @if ($customerOvdDetails[$i]['is_new_customer'] == '1' || $address_per_flag == 1)
                                                            <div class="form-read-only" id="address_per_line1-{{ $i }}">
                                                                <p>{{ $per_address_line1 }}</p>
                                                        </div>
                                                            @endif
                                                    </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Address Line2</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['per_address_line2-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['per_address_line2-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{ $displayClass_per }}">
                                                                <input type="text" class="form-control input-capitalize AddOvdDetailsField ekyc_field-{{$i}} perm_ekyc_field-{{$i}}" table="customer_ovd_details" id="per_address_line2-{{$i}}" name="per_address_line2" value="{{$per_address_line2}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = addressFieldValidation(this);" {{$readonly}} {{$etbreadonly}} {{$ekyc_field_class}} maxlength="45">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                <span class="error-message" style="color: red; display: none;">Total address length should be more than 18 characters.</span>
                                                                
                                                            </div>
                                                            @if ($customerOvdDetails[$i]['is_new_customer'] == '1' || $address_per_flag == 1)
                                                            <div class="form-read-only" id="address_per_line2-{{ $i }}">
                                                                <p>{{ $per_address_line2 }}</p>
                                                        </div>
                                                            @endif
                                                    </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Country</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['per_country-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['per_country-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{ $displayClass_per }}">
                                                                {!! Form::select('country',$countries,$per_country,array('class'=>'form-control country per_country  AddOvdDetailsField ekyc_field-'.$i.' perm_ekyc_field-'.$i,
                                                                    'table'=>'customer_ovd_details','id'=>'per_country-'.$i,'name'=>'per_country',$ekyc_field_class,'placeholder'=>''))  !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                            @if ($customerOvdDetails[$i]['is_new_customer'] == '1' || $address_per_flag == 1)
                                                            <div class="form-read-only" id="per_a_country-{{ $i }}">
                                                                <p>{{ $countries[$per_country] ?? '' }}</p>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Pincode </p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['per_pincode-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['per_pincode-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{ $displayClass_per }}">
                                                                <input type="text" class="form-control AddOvdDetailsField per_pincode ekyc_field-{{$i}} perm_ekyc_field-{{$i}}"  table="customer_ovd_details" id="per_pincode-{{$i}}" name="per_pincode" value="{{$per_pincode}}" minlength="6" maxlength="6" {{$readonly}} {{$etbreadonly}} oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" {{$ekyc_field_class}}>
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                            @if ($customerOvdDetails[$i]['is_new_customer'] == '1' || $address_per_flag == 1)
                                                            <div class="form-read-only" id="per_a_pincode-{{ $i }}">
                                                                <p>{{ $per_pincode }}</p>
                                                        </div>
                                                        @endif
                                                        </div>
                                                    </div>                                                    
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">State</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['per_state-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['per_state-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{ $displayClass_per }}">
                                                                <input type="text" class="form-control AddOvdDetailsField state ekyc_field-{{$i}} perm_ekyc_field-{{$i}}" table="customer_ovd_details" id="per_state-{{$i}}" name="per_state" value="{{$per_state}}" readonly style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^a-z]/gi, '').replace(/(\..*)\./g, '$1');" {{$ekyc_field_class}}>
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                        </div>
                                                            @if ($customerOvdDetails[$i]['is_new_customer'] == '1' || $address_per_flag == 1)
                                                            <div class="form-read-only" id="per_a_state-{{ $i }}">
                                                                <p>{{ $per_state }}</p>
                                                    </div>
                                                        @endif 
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">City</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['per_city-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['per_city-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{ $displayClass_per }}">
                                                                <input type="text" class="form-control AddOvdDetailsField city ekyc_field-{{$i}} perm_ekyc_field-{{$i}}" table="customer_ovd_details" id="per_city-{{$i}}" name="per_city" value="{{$per_city}}" readonly style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');" {{$ekyc_field_class}}>
                                                                 <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                            @if ($customerOvdDetails[$i]['is_new_customer'] == '1' || $address_per_flag == 1)
                                                            <div class="form-read-only" id="per_a_city-{{ $i }}">
                                                                <p>{{ $per_city }}</p>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    </div>

                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Land Mark</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['per_landmark-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['per_landmark-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{ $displayClass_per }}">
                                                                <input type="text" class="form-control input-capitalize AddOvdDetailsField ekyc_field-{{$i}} perm_ekyc_field-{{$i}}" table="customer_ovd_details" id="per_landmark-{{$i}}" name="per_landmark" value="{{$per_landmark}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9 _@.#&',()\/-]/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}} {{$etbreadonly}} {{$ekyc_field_class}}  maxlength="45">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                            @if ($customerOvdDetails[$i]['is_new_customer'] == '1' || $address_per_flag == 1)
                                                            <div class="form-read-only" id="per_a_landmark-{{ $i }}">
                                                                <p>{{ $per_landmark }}</p>
                                                        </div>
                                                            @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 text-center mt-3 mb-3">
                                                <a href="javascript:void(0)" class="btn btn-outline-grey mr-3 previoustab" tab="proof-of-identity-{{$i}}">Back</a>
                                                <a href="javascript:void(0)" class="btn btn-primary asperovd {{ $is_huf ? 'is_huf' : '' }}"  id="ovdnext-{{$i}}" tab="proof-of-current-address-{{$i}}" data-seq="{{$i}}">Next</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane documentstab" id="proof-of-current-address-{{$i}}" role="tabpanel">
                                  <span class="visibility_check" id="visibility_check-{{$i}}"></span>

                                    <div class="proofs-blck">
                                        <div class="radio-selection mt-2 mb-2">
                                            @if($is_huf)
                                            <label class="chekbox {{$displayCom}}" for="address_flag-{{ $i }}">
                                                <input type="checkbox" class="AddOvdDetailsField comm_flag huf_input_same_add" id="address_flag-{{ $i }}" name="address_flag-{{ $i }}"
                                                {{ $address_flag == 1 ? 'checked' : '' }} {{ $disabled }}>
                                            </label>
                                            <span class="lbl padding-8" id="name-container">{{$namecom }}</span>
                                            @else
                                            <label class="chekbox ekyccount-{{$i}}">
                                                @if(isset($reviewDetails['current_add_proof_image-'.$i]) || isset($reviewDetails['current_add_proof_image-'.$i]) || isset($reviewDetails['current_add_proof_card_number-'.$i]) || isset($reviewDetails['current_address_line1-'.$i]) || isset($reviewDetails['current_address_line2-'.$i]) || isset($reviewDetails['current_country-'.$i]) || isset($reviewDetails['current_pincode-'.$i]) || isset($reviewDetails['current_state-'.$i]) || isset($reviewDetails['current_city-'.$i]) || isset($reviewDetails['current_landmark-'.$i]))
                                                <input type="checkbox" class="AddOvdDetailsField comm_flag" id="address_flag-{{$i}}" name="address_flag-{{$i}}" {{ ($address_flag == 1) ? 'checked':''}} >
                                                @else
                                                <input type="checkbox" class="AddOvdDetailsField comm_flag" id="address_flag-{{$i}}" name="address_flag-{{$i}}" {{ ($address_flag == 1) ? 'checked':''}} {{$disabled}} {{$etbdisabled}}>
                                                @endif

                                                @if (!$is_huf_display)
                                                <span class="lbl padding-8">Same as Address (As per OVD)</span>
                                                @endif
                                            </label>
                                            @endif
                                        </div>  
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group {{$displayClass}}" id="upload_cur_address_proof-{{$i}}">
                                                   {{--  <label class="uploadLabel">Upload</label> --}}
                                                   <div class="detaisl-left align-content-center ">
                                                       <label class="uploadLabel">Upload</label>
                                                       <span class="{{$enable}}">
                                                       @if(isset($reviewDetails['current_add_proof_image-'.$i]))
                                                           <i class="fa fa-times"></i>
                                                           {{$reviewDetails['current_add_proof_image-'.$i]}}
                                                           <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                       @else
                                                            <i class="fa fa-check"></i>
                                                       @endif
                                                       </span>
                                                    </div>
                                                    <div class="add-document d-flex align-items-center justify-content-around id_image_div" id="current_add_proof_image-{{$i}}" table="customer_ovd_details" name="current_add_proof_image" data-seq={{$i}}>
                                                        @if(isset($current_add_proof_image) && $current_add_proof_image != '')
                                                            <div id="upload_cur_address_proof_div-{{$i}}" table="customer_ovd_details" name="current_add_proof_image" data-seq="{{$i}}">
                                                                @if($enable == 'display-none')
                                                                    <div class="upload-delete">
                                                                        <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                                        </button>
                                                                    </div>
                                                                @else
                                                                    @if(isset($reviewDetails['current_add_proof_image-'.$i]))
                                                                        <div class="upload-delete">
                                                                            <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                                            </button>
                                                                        </div>
                                                                    @else
                                                                    @endif
                                                                @endif
                                                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                <img class="uploaded_image current_add_proof_image" name="current_add_proof_image-{{$i}}" id="document_preview_cur_add_proof-{{$i}}" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$current_add_proof_image)}}" onerror="imgNotFound('Current add proof front')">
                                                            </div>
                                                            </div>
                                                        @endif
                                                        @if(isset($current_add_proof_image) && $current_add_proof_image != '')
                                                        <div class="add-document-btn adb-btn-inn display-none">
                                                        @else
                                                        <div class="add-document-btn adb-btn-inn">
                                                        @endif
                                                            <button type="button" id="proof_of_current_address" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                                            data-id="current_add_proof_image-{{$i}}" data-class="AddPanDetailsField" data-name="current_add_proof_image-{{$i}}"  data-document="Image" data-target="#upload_proof" {{$etbdisabled}} table="customer_ovd_details" name="add_proof_image" data-seq={{$i}}>
                                                                <span class="adb-icon">
                                                                    <i class="fa fa-plus-circle"></i>
                                                                </span>
                                                                Add
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input type="text" style="opacity:0" name="current_add_proof_image" id="currentAddProofImage-{{$i}}">
                                                    <div class="osv-done-blck {{$osv_check}}">
                                                        <label class="radio">
                                                            <input type="checkbox" class="osv_done_check" name="cur_add_proof_osv_check[{{$i}}]" id="cur_add_proof_osv_check-{{$i}}" {{($cur_add_proof_osv_check == '1')? "checked" : ""}} {{$disabled}} {{$etbdisabled}}>
                                                            <span class="lbl padding-8">Confirm Original Seen and Verified</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="custom-col-review proof-of-identity col-md-8 proof_of_current_address">
                                                <div class="row">
                                                    <div class="details-custcol-row col-md-6 {{$displayClass}}">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv"  id="cur_address_proof-{{$i}}">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Proof of Communication Address</p>
                                                                 <span role="tooltip" aria-label=" Customer must submit OVD with updated current address within 3 months" data-microtip-position="top" data-microtip-size="medium"><i class="fa fa-info-circle" class="tooltip" ></i></span>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['proof_of_current_address-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['proof_of_current_address-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        @php
                                                        if($is_huf){
                                                            $currentdropdown = $huf_karta_curnt_add;
                                                        }else{
                                                            $currentdropdown = $currentAddressProofOVDs;
                                                        }
                                                        @endphp
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck">
                                                                {!! Form::select('proof_of_current_address',$currentdropdown,$cur_address_proof_list,array('class'=>'form-control  current_address_proof AddOvdDetailsField cur_address_proof_list',
                                                                'table'=>'customer_ovd_details','id'=>'proof_of_current_address-'.$i,'name'=>'proof_of_current_address','placeholder'=>'','proof_type'=>'cur_address')) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6 {{$displayClass}} current_add_proof_card_number-{{$i}}">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv" >
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Enter number</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['current_add_proof_card_number-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['current_add_proof_card_number-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm" id="cur_address_proof_number-{{$i}}">
                                                            <div class="comments-blck">
                                                                <input type="text" class="aadhaar_mask form-control AddOvdDetailsField" table="customer_ovd_details" name="current_add_proof_card_number" id="current_add_proof_card_number-{{$i}}" value="{{$current_add_proof_card_number}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Address Line1</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['current_address_line1-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['current_address_line1-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{$displayClass}}">
                                                                <input type="text" class="form-control input-capitalize AddOvdDetailsField" table="customer_ovd_details" id="current_address_line1-{{$i}}" name="current_address_line1" value="{{$current_address_line1}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = addressFieldValidation(this);" {{$readonly}} {{$etbreadonly}} maxlength="45">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                <span class="error-message" style="color: red; display: none;">Total address length should be more than 18 characters.</span>
   
                                                            </div>

                                                            @if($customerOvdDetails[$i]['is_new_customer'] == '1' || ($address_flag == 1 ))
                                                            <div class="form-read-only" id="address_line1-{{$i}}">
                                                                <p>{{$current_address_line1}}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>


                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Address Line2</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['current_address_line2-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['current_address_line2-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{$displayClass}}">
                                                                <input type="text" class="form-control input-capitalize AddOvdDetailsField" table="customer_ovd_details" id="current_address_line2-{{$i}}" name="current_address_line2" value="{{$current_address_line2}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = addressFieldValidation(this);" {{$readonly}} {{$etbreadonly}} maxlength="45">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                <span class="error-message" style="color: red; display: none;">Total address length should be more than 18 characters.</span>
   
                                                            </div>
                                                            @if($customerOvdDetails[$i]['is_new_customer'] == '1' || ($address_flag == 1 ))
                                                            <div class="form-read-only" id="address_line2-{{$i}}">
                                                                <p>{{$current_address_line2}}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Country</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['current_country-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['current_country-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{$displayClass}}">
                                                                {!! Form::select('country',$countries,$current_country,array('class'=>'form-control country current_country AddOvdDetailsField',
                                                                    'table'=>'customer_ovd_details','id'=>'current_country-'.$i,'name'=>'current_country')) !!}
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                            @if($customerOvdDetails[$i]['is_new_customer'] == '1' || ($address_flag == 1 ))
                                                            <div class="form-read-only" id="country-{{$i}}">
                                                                <p>{{$current_country}}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Pincode </p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['current_pincode-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['current_pincode-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{$displayClass}}">
                                                                <input type="text" class="form-control AddOvdDetailsField current_pincode" table="customer_ovd_details" id="current_pincode-{{$i}}" name="current_pincode" value="{{$current_pincode}}" {{$readonly}} {{$etbreadonly}} oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" minlength="6" maxlength="6">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                             @if($customerOvdDetails[$i]['is_new_customer'] == '1' || ($address_flag == 1 ))
                                                            <div class="form-read-only" id="pincode-{{$i}}">
                                                                <p>{{$current_pincode}}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>                                                    
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">State</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['current_state-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['current_state-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{$displayClass}}">
                                                                <input type="text" class="form-control AddOvdDetailsField state" table="customer_ovd_details" id="current_state-{{$i}}" name="current_state" value="{{$current_state}}" readonly style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^a-z]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                             @if($customerOvdDetails[$i]['is_new_customer'] == '1' || ($address_flag == 1 ))
                                                            <div class="form-read-only" id="state-{{$i}}">
                                                                <p>{{$current_state}}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">City</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['current_city-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['current_city-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{$displayClass}}">
                                                                <input type="text" class="form-control AddOvdDetailsField city" table="customer_ovd_details" id="current_city-{{$i}}" name="current_city" value="{{$current_city}}" readonly  style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                             @if($customerOvdDetails[$i]['is_new_customer'] == '1' || ($address_flag == 1 ))
                                                            <div class="form-read-only" id="city-{{$i}}">
                                                                <p>{{$current_city}}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row col-md-6">
                                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center ">
                                                                <p class="lable-cus">Land Mark</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['current_landmark-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['current_landmark-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck {{$displayClass}}">
                                                                <input type="text" class="form-control input-capitalize AddOvdDetailsField" table="customer_ovd_details" id="current_landmark-{{$i}}" name="current_landmark" value="{{$current_landmark}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^a-z0-9 _@.#&',()\/-]/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}} {{$etbreadonly}} maxlength="45">
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                             @if($customerOvdDetails[$i]['is_new_customer'] == '1' || ($address_flag == 1 ))
                                                            <div class="form-read-only" id="landmark-{{$i}}">
                                                                <p>{{$current_landmark}}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                      <div class="row">
                                            <div class="col-md-12 text-center mt-3 mb-3">
                                                @if((isset($AccountIds)) && (count($AccountIds) > 0))
                                                     @if($i == $accountHoldersCount && $accountDetails['account_type'] == '2' && $accountDetails['flow_tag_1'] != 'INDI')
                                                            <a href="javascript:void(0)" class="btn btn-outline-grey mr-3 mb-3 previoustab" tab="proof-of-permanent-address-{{$i}}">Back</a>
                                                            <a href="javascript:void(0)" class="btn btn-primary nexttaphotographsignature mb-3 check-all-ovd-applicant" data-id="signature-{{$i}}" id="idProofNext" tab="entitydetailstab">
                                                                Next
                                                            </a>
                                                     @else
                                                       @if($i <= $accountHoldersCount && $accountDetails['account_type'] == '2'&& $accountDetails['flow_tag_1'] != 'INDI')
                                                                <a href="javascript:void(0)" class="btn btn-outline-grey mr-3 mb-3 previoustab" tab="proof-of-permanent-address-{{$i}}">Back</a>
                                                                <a href="javascript:void(0)" class="btn btn-primary nexttab mb-3  check-ovd-applicant" data-id="signature-{{$i}}" id="nextapplicant-{{$i}}" tab="nextapplicant">Next</a>
                                                        @endif    
                                              
                                                    @if($i ==$accountHoldersCount && $accountDetails['account_type'] != '2') 
                                                    <a href="javascript:void(0)" class="btn btn-outline-grey mr-3 mb-3 previoustab"tab="proof-of-permanent-address-{{$i}}">Back</a>
                                                    <a href="javascript:void(0)" class="btn btn-primary nexttaphotographsignature mb-3 check-all-ovd-applicant" id="idProofNext" data-id="signature-{{$i}}" tab="photographsignature">Next </a>
                                                    @else
                                                        @if($i <= $accountHoldersCount && $accountDetails['account_type'] != '2' )
                                                            <a href="javascript:void(0)" class="btn btn-outline-grey mr-3 mb-3 previoustab" tab="proof-of-permanent-address-{{$i}}">Back</a>
                                                            <a href="javascript:void(0)" class="btn btn-primary nexttab mb-3  check-ovd-applicant" data-id="signature-{{$i}}" id="nextapplicant-{{$i}}" tab="nextapplicant">Next</a>
                                                    @endif
                                                        @endif
                                                    @endif

                                                    @if($i == $accountHoldersCount && $accountDetails['account_type'] == '2' && $accountDetails['flow_tag_1'] == 'INDI')
                                                    <a href="javascript:void(0)" class="btn btn-outline-grey mr-3 mb-3 previoustab"tab="proof-of-permanent-address-{{$i}}">Back</a>
                                                            <a href="javascript:void(0)" data-id="signature-{{$i}}" class="btn btn-primary nexttaphotographsignature mb-3 check-all-ovd-applicant {{ $is_huf ? 'is_huf' : '' }}" id="idProofNext" tab="photographsignature">Next </a>
                                                    @else
                                                    @if($accountDetails['constitution'] == 'NON_IND_HUF' && $i == 1 && $accountDetails['account_type'] == '2' &&
                                                        $accountDetails['flow_tag_1'] != 'PROP')
                                                        <a href="javascript:void(0)" class="btn btn-outline-grey mr-3 mb-3 previoustab" tab="proof-of-permanent-address-{{$i}}">Back</a>
                                                        <a href="javascript:void(0)" class="btn btn-primary nexttab mb-3 check-ovd-applicant"  data-id="signature-{{$i}}" id="nextapplicant-{{$i}}" tab="nextapplicant">Next</a>
                                                @endif
                                                    @endif

                                                @endif
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
</div>