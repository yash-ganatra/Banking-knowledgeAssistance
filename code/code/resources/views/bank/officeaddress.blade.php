
@inject('provider', 'App\Helpers\labelCode')

@php
	$i=1; // added to fix next button click 
    $entity_address_flag = '';
	$entity_address_proof_list = '';
    $entity_add_proof_card_number = '';
    $entity_name = "";
    $entity_add_proof_osv_check = '';
    $entity_address_line1 = '';
    $entity_address_line2 = '';
    $entity_landmark = '';
    $entity_country = '';
    $entity_state = '';
    $entity_city = '';
    $entity_pincode = '';
    $formId = '';
    $folder = '';
    $account_id = "";
    $displayClass = '';
    $disabled = '';
    $etbreadonly = "";
    $etbdisabled = "";
    $customertype = "";
    $ekyc_field_class = ""; 
    $entitydisplayClass = '';
    $osv_check = '';
    $entity_add_proof_osv_check = '';
    $entity_mobile_number='';
    $entity_email_id='';
	$scheme_code='';
	$scheme_code = $getSchemeDetails['scheme_code'];
	$readonly='';

	$label_land_mark 	= $provider::getLabel($scheme_code,'label_land_mark');  
	$label_entity_name 	= $provider::getLabel($scheme_code,'label_entity_name');  
	$label_upload_proof = $provider::getLabel($scheme_code,'label_upload_proof');
	$label_entity_address_number = $provider::getLabel($scheme_code,'label_entity_address_number');
	$label_proof_of_entity_address = $provider::getLabel($scheme_code,'label_proof_of_entity_address');
	$label_entity_mobile_number = $provider::getLabel($scheme_code,'label_entity_mobile_number');
	$label_entity_email = $provider::getLabel($scheme_code,'label_entity_email');

	$entity_kyclist = $provider::getKYClist($scheme_code);	
	
@endphp
@if($accountDetails['account_type'] == '2')
@php
	// echo "<pre>";print_r($entityDetails);exit;
	$entity_add_proof_image = $entityDetails['entity_add_proof_image'];
	$entity_add_proof_back_image = $entityDetails['entity_add_proof_back_image'];

    $entity_address_proof_list = $entityDetails['proof_of_entity_address'];
    $entity_add_proof_card_number = $entityDetails['entity_add_proof_card_number'];
    $entity_name = $entityDetails['entity_name'];
    // $entity_add_proof_osv_check = $entityDetails['entity_address_line1'];
    $entity_address_line1 = $entityDetails['entity_address_line1'];
    $entity_address_line2 = $entityDetails['entity_address_line2'];;
    $entity_landmark = $entityDetails['entity_landmark'];
    $entity_country = $entityDetails['entity_address_line1'];
    $entity_state = $entityDetails['entity_state'];
    $entity_city = $entityDetails['entity_city'];
    $entity_pincode = $entityDetails['entity_pincode'];
    $entity_mobile_number=$entityDetails['entity_mobile_number'];
    $entity_email_id=$entityDetails['entity_email_id'];
    
    if($entity_mobile_number=='' && isset($userDetails['customerOvdDetails']['1']['mobile_number'])){	
     $entity_mobile_number=$userDetails['customerOvdDetails']['1']['mobile_number'];
    }
    if($entity_email_id=='' && isset($userDetails['customerOvdDetails']['1']['email'])){	
     $entity_email_id=$userDetails['customerOvdDetails']['1']['email'];
    }

    $formId = $entityDetails['form_id'];
 	// $display = "display-none";
  //   $class = '';
    $folder = "attachments";
    
  if($entityDetails['entity_add_proof_image'] != ''){
        $entity_add_proof_osv_check = 1;
       }


    // echo"<pre>";print_r($formId);exit;
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
        $entity_add_proof_osv_check = 1;
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
<div id="tab-entity" class="tab-content-cust">
    <div class="card">
        <div class="col-lg-12">
            <input type="hidden" id="formId" name="formId" value="{{$formId}}">
            <div class="tab-content px-3">
					 <span class="visibility_check" id="visibility_check"></span>
				    <div class="proofs-blck">
				        <div class="radio-selection mt-2 mb-2">
				            <label class="chekbox">
				               {{--  <input type="checkbox" class="entity_flag EntItyDetailsField" id="entity_address_flag" name="entity_address_flag" {{ ($entity_address_flag == 1) ? 'checked':''}} {{$disabled}} {{$etbdisabled}} >
				                <span class="lbl padding-8">Same as Address (As per OVD)</span>
				             --}}
				        </label>
				        </div>  
				        <div class="row">
				            <div class="col-md-4">
				                <div class="form-group {{$entitydisplayClass}}" id="upload_entity_address_proof">
				                   {{--  <label class="uploadLabel">Upload</label> --}}
				                   <div class="detaisl-left align-content-center ">
				                       <label class="uploadLabel">{{$label_upload_proof}}</label>
				                       <span class="{{$enable}}">
				                       @if(isset($reviewDetails['entity_add_proof_image']))
				                           <i class="fa fa-times"></i>
				                           {{$reviewDetails['entity_add_proof_image']}}
				                           <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                       @else
				                            <i class="fa fa-check"></i>
				                       @endif
				                       </span>
				                    </div>
									<div class="accordion" id="accordionExampleone">
                                                                <div class="card-accordion">
                                                                    <div class="card-header-accordion" id="headingOne">
                                                                        <h2 class="mb-0">
                                                                        <button class="btn btn-link btn-block text-left front" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneid" aria-expanded="true" aria-controls="collapseOneid">
                                                                            Front side
                                                                        </button>
                                                                        </h2>
                                                                    </div>
                                                                    <div id="collapseOneid" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExampleone">
                                                                        <div class="card-body-accordion">
                                                                            <div class="add-document d-flex align-items-center justify-content-around id_image_div_front" id="entity_add_proof_image">
				                        @if(isset($entity_add_proof_image) && $entity_add_proof_image != '')
                                                                                    <div id="upload_id_proof_div">
                                                                                        @if($entity_add_proof_image != '')
				                                @if($enable == 'display-none')
				                                    <div class="upload-delete">
				                                        <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
				                                            <i class="fa fa-trash" aria-hidden="true"></i>
				                                        </button>
				                                    </div>
				                                @else
				                                    @if(isset($reviewDetails['entity_add_proof_image']))
				                                        <div class="upload-delete">
				                                            <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
				                                                <i class="fa fa-trash" aria-hidden="true"></i>
				                                            </button>
				                                        </div>
				                                    @else
				                                    @endif
				                                @endif
																				<div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                            <img class="uploaded_image  entity_add_proof_image" name="entity_add_proof_image" id="entity_add_proof_image" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$entity_add_proof_image)}}" onerror="imgNotFound('Entity add proof front')">
																				</div>	
                                                                                        @endif
				                            </div>
				                        @endif
				                        @if(isset($entity_add_proof_image) && $entity_add_proof_image != '')
				                        <div class="add-document-btn adb-btn-inn display-none">
				                        @else
				                        <div class="add-document-btn adb-btn-inn">
				                        @endif
                                                                               
                                                                                    <button type="button" id="entity_add_proof_image_id" class="btn btn-outline-grey waves-effect upload_document upload_front_side" data-toggle="modal" 
				                            data-id="entity_add_proof_image" data-class="AddPanDetailsField" data-name="entity_add_proof_image"  data-document="Image" data-target="#upload_proof" {{$etbdisabled}}>
				                                <span class="adb-icon">
				                                    <i class="fa fa-plus-circle"></i>
				                                </span>
				                                Add
				                            </button>
				                        </div>
				                    </div>
				                    <input type="text" style="opacity:0" name="entity_add_proof_image" id="entityAddProofImage">

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="card-accordion">
                                                                    <div class="card-header-accordion" id="headingTwoid">
                                                                        <h2 class="mb-0">
                                                                            <button id="collapse_id_proof" class="btn btn-link btn-block text-left collapsed back" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwoid" aria-expanded="false" aria-controls="collapseTwoid">
                                                                                Back side
                                                                            </button>
                                                                        </h2>
                                                                    </div>
                                                                    <div id="collapseTwoid" class="collapse" aria-labelledby="headingTwoid" data-parent="#accordionExampleone">
                                                                        <div class="card-body-accordion">
                                                                            <div class="add-document d-flex align-items-center justify-content-around id_image_div_back" id="entity_add_proof_back_image">
                                                                                @if(isset($entity_add_proof_back_image))
                                                                                    <div id="upload_back_id_proof_div">
                                                                                        @if($enable == 'display-none')
                                                                                                <div class="upload-delete">
                                                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                    </button>
                                                                                                 </div>
                                                                                        @else
                                                                                            @if(isset($reviewDetails['entity_add_proof_image']))
                                                                                                <div class="upload-delete">
                                                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                                    </button>
                                                                                                 </div>
                                                                                            @else
                                                                                            @endif
                                                                                        @endif
																																												<div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                        <img class="uploaded_image entity_add_proof_back_image" name="entity_add_proof_back_image" id="document_preview_entity_add_proof_back_image" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$entity_add_proof_back_image)}}" onerror="imgNotFound('Id proof back')">
                                                                                    </div>
                                                                                    </div>
                                                                                @endif
                                                                                @if(isset($entity_add_proof_back_image) && $entity_add_proof_back_image != '')
                                                                                    <div class="add-document-btn adb-btn-inn display-none">
                                                                                @else
                                                                                    <div class="add-document-btn adb-btn-inn">
                                                                                @endif
                                                                                    <button type="button" id="entity_add_proof_image_id" class="btn btn-outline-grey waves-effect upload_document upload_back_side" data-toggle="modal" 
                                                                                    data-id="entity_add_proof_back_image" data-class="AddPanDetailsField" data-name="entity_add_proof_back_image"  data-document="Image" data-target="#upload_proof">
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
				                    <div class="osv-done-blck {{$osv_check}}">
				                        <label class="radio">
				                            <input type="checkbox" class="osv_done_check" name="entity_add_proof_osv_check" id="entity_add_proof_osv_check" {{($entity_add_proof_osv_check == '1')? "checked" : ""}} {{$disabled}} {{$etbdisabled}}>

				                            <span class="lbl padding-8">Confirm Original Seen and Verified</span>
				                        </label>
				                    </div>
				                </div>
				            </div>
				                    
				            <div class="custom-col-review proof-of-entity col-md-8 proof_of_entity_address">
				                <div class="row">
				                    <div class="details-custcol-row col-md-6 {{$entitydisplayClass}}">
				                        <div class="details-custcol-row-top d-flex editColumnDiv"  id="entity_address_proof">
				                            <div class="detaisl-left d-flex align-content-center">
				                                <p class="lable-cus">{{$label_proof_of_entity_address}}</p>
				                                 <span role="tooltip" aria-label=" Customer must submit OVD with updated entity address within 3 months" data-microtip-position="top" data-microtip-size="medium"><i class="fa fa-info-circle" class="tooltip" ></i></span>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['proof_of_entity_address']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['proof_of_entity_address']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm">
				                            <div class="comments-blck">
					                            {!! Form::select('proof_of_entity_address',$entity_kyclist,$entity_address_proof_list,array('class'=>'form-control  entity_address_proof EntItyDetailsField entity_address_proof_list',
					                                'table'=>'entity_details','id'=>'proof_of_entity_address-'.$i,'name'=>'proof_of_entity_address','placeholder'=>'','proof_type'=>'entity_address',$disabled)) !!}
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                        </div>
				                    </div>
				                    <div class="details-custcol-row col-md-6 {{$entitydisplayClass}} entity_add_proof_card_number">
				                        <div class="details-custcol-row-top d-flex editColumnDiv" >
				                            <div class="detaisl-left d-flex align-content-center">
				                                <p class="lable-cus">{{$label_entity_address_number}}</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_add_proof_card_number']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_add_proof_card_number']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm" id="cur_address_proof_number">
				                            <div class="comments-blck">
				                                <input type="text" class="aadhaar_mask form-control EntItyDetailsField gst" table="entity_details" name="entity_add_proof_card_number" maxlength="25" id="entity_add_proof_card_number" value="{{$entity_add_proof_card_number}}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^ a-zA-Z0-9]/, '').replace(/(\..*)\./g, '$1');">
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                        </div>
				                    </div>
				                    <div class="details-custcol-row col-md-6 {{$entitydisplayClass}} entity_name">
				                        <div class="details-custcol-row-top d-flex editColumnDiv" >
				                            <div class="detaisl-left d-flex align-content-center">
				                                <p class="lable-cus">{{ $label_entity_name }}</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_name']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_name']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm" id="entity_address_proof_number">
				                            <div class="comments-blck">
				                                <input type="text" class="aadhaar_mask form-control EntItyDetailsField" table="entity_details" name="entity_name" id="entity_name" value="{{ $entity_name }}" {{$readonly}} {{$etbreadonly}} onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^ a-zA-Z0-9!@#^%*,`_-\/\$\&\)\\(‘.\/\/]+$/gi, '').replace(/(\..*)\./g, '$1');">
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                        </div>
				                    </div>
				                    <div class="details-custcol-row col-md-6">
				                        <div class="details-custcol-row-top d-flex editColumnDiv">
				                            <div class="detaisl-left d-flex align-content-center ">
				                                <p class="lable-cus">Address Line1</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_address_line1']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_address_line1']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm">
				                            <div class="comments-blck {{$entitydisplayClass}}">
				                                <input type="text" class="form-control input-capitalize EntItyDetailsField" table="entity_details" id="entity_address_line1" name="entity_address_line1" value="{{$entity_address_line1}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^ a-zA-Z0-9!@#^%*,`_-\/\$\&\)\\(‘.\/\/]+$/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}} {{$etbreadonly}} maxlength="45">
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>

				                            @if((Session::get('customer_type') != "ETB"))
				                            <div class="form-read-only" id="entity_address_line1">
				                                {{-- <p>{{$entity_address_line1}}</p> --}}
				                            </div>
				                            @endif
				                        </div>
				                    </div>


				                    <div class="details-custcol-row col-md-6">
				                        <div class="details-custcol-row-top d-flex editColumnDiv">
				                            <div class="detaisl-left d-flex align-content-center ">
				                                <p class="lable-cus">Address Line2</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_address_line2']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_address_line2']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm">
				                            <div class="comments-blck {{$entitydisplayClass}}">
				                                <input type="text" class="form-control input-capitalize EntItyDetailsField" table="entity_details" id="entity_address_line2" name="entity_address_line2" value="{{$entity_address_line2}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^ a-zA-Z0-9!@#^%*,`_-\/\$\&\)\\(‘.\/\/]+$/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}} {{$etbreadonly}} maxlength="45">
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                            @if((Session::get('customer_type') != "ETB" ) || ($entity_address_flag == 1 ))
				                            <div class="form-read-only" id="entity_address_line2">
				                                {{-- <p>{{$entity_address_line2}}</p> --}}
				                            </div>
				                            @endif
				                        </div>
				                    </div>
				                    <div class="details-custcol-row col-md-6">
				                        <div class="details-custcol-row-top d-flex editColumnDiv">
				                            <div class="detaisl-left d-flex align-content-center">
				                                <p class="lable-cus">Country</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_country']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_country']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm">
				                            <div class="comments-blck {{$entitydisplayClass}}">
				                                {!! Form::select('country',$countries,$entity_country,array('class'=>'form-control country entity_country EntItyDetailsField ',
				                                    'table'=>'entity_details','id'=>'entity_country-'.$i,'name'=>'entity_country',$disabled)) !!}
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                            @if((Session::get('customer_type') != "ETB"))
				                            <div class="form-read-only" id="entity_country">
				                                {{-- <p>{{$entity_country}}</p> --}}
				                            </div>
				                            @endif
				                        </div>
				                    </div>
				                    <div class="details-custcol-row col-md-6">
				                        <div class="details-custcol-row-top d-flex editColumnDiv">
				                            <div class="detaisl-left d-flex align-content-center ">
				                                <p class="lable-cus">Pincode </p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_pincode']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_pincode']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm">
				                            <div class="comments-blck {{$entitydisplayClass}}">
				                                <input type="text" class="form-control EntItyDetailsField entity_pincode" table="entity_details" id="entity_pincode" name="entity_pincode" value="{{$entity_pincode}}" {{$readonly}} {{$etbreadonly}} oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');">
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                             @if((Session::get('customer_type') != "ETB" ) || ($entity_address_flag == 1 ))
				                            <div class="form-read-only" id="office_pincode">
				                                {{-- <p>{{$entity_pincode}}</p> --}}
				                            </div>
				                            @endif
				                        </div>
				                    </div>                                                    
				                    <div class="details-custcol-row col-md-6">
				                        <div class="details-custcol-row-top d-flex editColumnDiv">
				                            <div class="detaisl-left d-flex align-content-center ">
				                                <p class="lable-cus">State</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_state']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_state']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm">
				                            <div class="comments-blck {{$entitydisplayClass}}">
				                                <input type="text" class="form-control EntItyDetailsField state" table="entity_details" id="entity_state" name="entity_state" readonly value="{{$entity_state}}"  style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^a-z]/gi, '').replace(/(\..*)\./g, '$1');">
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                             @if((Session::get('customer_type') != "ETB" ) || ($entity_address_flag == 1 ))
				                            <div class="form-read-only" id="entity_state">
				                                {{-- <p>{{$entity_state}}</p> --}}
				                            </div>
				                            @endif
				                        </div>
				                    </div>
				                    <div class="details-custcol-row col-md-6">
				                        <div class="details-custcol-row-top d-flex editColumnDiv">
				                            <div class="detaisl-left d-flex align-content-center ">
				                                <p class="lable-cus">City</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_city']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_city']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm">
				                            <div class="comments-blck {{$entitydisplayClass}}">
				                                <input type="text" class="form-control EntItyDetailsField city" table="entity_details" id="entity_city" name="entity_city" readonly value="{{$entity_city}}"   style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^a-z0-9]/gi, '').replace(/(\..*)\./g, '$1');">
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                             @if((Session::get('customer_type') != "ETB" ) || ($entity_address_flag == 1 ))
				                            <div class="form-read-only" id="office_city">
				                                {{-- <p>{{$entity_city}}</p> --}}
				                            </div>
				                            @endif
				                        </div>
				                    </div>
				                    <div class="details-custcol-row col-md-6 display-none">
				                        <div class="details-custcol-row-top d-flex editColumnDiv">
				                            <div class="detaisl-left d-flex align-content-center ">
				                                <p class="lable-cus">{{$label_land_mark}}</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_landmark']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_landmark']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm">
				                            <div class="comments-blck {{$entitydisplayClass}}">
				                                <input type="text" class="form-control EntItyDetailsField input-capitalize " table="customer_ovd_details" id="entity_landmark" name="entity_landmark" value="{{$entity_landmark}}" onkeyup="this.value = this.value.toUpperCase();" oninput="this.value = this.value.replace(/[^ a-zA-Z0-9!_@#\$\&\')\(-]/gi, '').replace(/(\..*)\./g, '$1');" {{$readonly}} {{$etbreadonly}}>
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
				                            </div>
				                             @if((Session::get('customer_type') != "ETB" ))
				                            <div class="form-read-only" id="entity_landmark">
				                                {{-- <p>{{$entity_landmark}}</p> --}}
				                            </div>
				                            @endif
				                        </div>
				                    </div>
				                    <div class="details-custcol-row col-md-6 {{$entitydisplayClass}} entity_mobile_number">
				                        <div class="details-custcol-row-top d-flex editColumnDiv" >
				                            <div class="detaisl-left d-flex align-content-center">
				                                <p class="lable-cus">{{$label_entity_mobile_number}}</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_mobile_number']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_mobile_number']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm" id="cur_address_proof_number">
				                            <div class="comments-blck">
				                                <input type="text" class="form-control EntItyDetailsField enc_input mobile {{$is_review==1 ? "unmaskingfield": ''}}" {{ $is_review==1 ? 'style=display:none;' : ''}} table="entity_details" name="entity_mobile_number" id="entity_mobile_number" value="{{$entity_mobile_number}}" {{$readonly}} {{$etbreadonly}} maxlength="10" minlength="10">
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
																				@if($is_review==1)
                                          <input type="password" class="form-control maskingfield" value="**************" {{$readonly}} {{$etbreadonly}}>
                                        @endif
				                            </div>
				                        </div>
				                    </div>
				                  
				                	 <div class="details-custcol-row col-md-6 {{$entitydisplayClass}} entity_email_id">
				                        <div class="details-custcol-row-top d-flex editColumnDiv" >
				                            <div class="detaisl-left d-flex align-content-center">
				                                <p class="lable-cus">{{$label_entity_email}}</p>
				                                <span class="{{$enable}}">
				                                    @if(isset($reviewDetails['entity_email_id']))
				                                        <i class="fa fa-times"></i>
				                                        {{$reviewDetails['entity_email_id']}}
				                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
				                                    @else
				                                        <i class="fa fa-check"></i>
				                                    @endif
				                                </span>
				                            </div>
				                        </div>
				                        <div class="details-custcol-row-bootm" id="cur_address_proof_number">
				                            <div class="comments-blck">
				                                <input type="email" class="aadhaar_mask form-control EntItyDetailsField enc_input entity_email_id {{$is_review==1 ?  "unmaskingfield": ''}}" {{ $is_review==1 ? 'style=display:none;' : ''}} table="entity_details" name="entity_email_id" id="entity_email_id" value="{{$entity_email_id}}" {{$readonly}} {{$etbreadonly}} >
				                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
																				@if($is_review==1)
                                          <input type="password" class="form-control maskingfield" value="**************" {{$readonly}} {{$etbreadonly}}>
                                        @endif
				                            </div>
				                        </div>
				                    </div>
				                </div>
				            </div>
				        </div>
				        <div class="row">
				            <div class="col-md-12 text-center mt-3 mb-3">
				                <a href="javascript:void(0)" class="btn btn-outline-grey mr-3 mb-3 previoustab" tab="proof-of-permanent-address-{{$i}}">Back</a>
                                <a href="javascript:void(0)" data-id="signature-{{$i}}" class="btn btn-primary nexttaphotographsignature mb-3 check-all-ovd-applicant" id="idProofNext" tab="photographsignature">
                                    Next
                                </a>
				            </div>
				        </div>
				    </div>
			</div>
		</div>
	</div>
</div>