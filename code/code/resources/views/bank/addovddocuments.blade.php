@inject('provider','App\Helpers\labelCode')
@extends('layouts.app')
@section('content')
@php
    $current_state = '';
    $class = 'text-muted-lnavs';
    $display = "";
    $proof_of_address = "";
    $passport_driving_expire = "";
    $passport_driving_issue_date="";
    $passport_driving_issue_date_permanent="";
    $passport_driving_permanent_issue="";
    $passport_driving_issue="";
    $passport_driving_expire_permanent = "";
    $proof_of_current_address = "";
    $mode_of_operation = '';
    $account_holders = array();
    $signature_type = 'Signature';
    $accountHoldersCount = $no_of_account_holders = Session::get('no_of_account_holders');
    $readonly = "";
    $is_review = 0;
    $enable = "display-none";
    $folder = '';
    $callCenterFlow = 0;
    $AccountIds = array();
    //$accountHoldersCount = Session::get('no_of_account_holders');
    //clarify
    $customer_image = '';
    $disabled = "";
    $page = 2;
    $scheme_code = '';
    $scheme_code = $getSchemeDetails['scheme_code'];
    $label_entity_details = $provider::getLabel($scheme_code,'label_entity_details');
@endphp

@if(Session::get('customer_type') == "ETB")
    @php
        if(Session::get('role') == "11"){
           $callCenterFlow = 1;
        }
        else{
           $callCenterFlow = 0;
        }
        
    @endphp
@endif  

@if(isset($userDetails['customerOvdDetails']))
    @php
        $accountHoldersCount = $userDetails['AccountDetails']['no_of_account_holders'];
        $accountDetails = $userDetails['AccountDetails'];
        $customerOvdDetails = $userDetails['customerOvdDetails'];
        $AccountIds = $userDetails['AccountIds'];

        //user customer details to the table 

        if(Session::get('customer_type') != "ETB"){
            //$etbreadonly = "";            
            $account_holders = explode(',',$userDetails['AccountDetails']['account_holders']);
        }else{
             // $etbreadonly = "readonly";
        }
        if(isset($accountDetails['customers_photograph']))
        {            
            $customer_image = $accountDetails['customers_photograph'];
            if(substr($customer_image,0,11) == "_DONOTSIGN_"){
                $customer_image = $customer_image;
            }else{
                $customer_image = '_DONOTSIGN_'.$customer_image;
            }
        }
        $mode_of_operation = $userDetails['AccountDetails']['mode_of_operation'];   
        $signature_type = $userDetails['AccountDetails']['signature_type'];
        $no_of_account_holders = $userDetails['AccountDetails']['no_of_account_holders'];
        //$applicantsDOBs  = $applicantsDOBs;
        $folder = "attachments";

    @endphp
@endif

@if(empty($AccountIds) && isset($ovdIDs) && count($ovdIDs) > 0)
	@php
		$AccountIds = $ovdIDs;			
	@endphp
@endif	

@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $enable = "";
        $readonly = "readonly";
        $disabled = "disabled";
        $folder = "markedattachments";
    @endphp
@endif
@if(isset($applicantsDOBs))
    @php
        $applicantsDOBs  = implode(',',$applicantsDOBs);
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
<div class="dnone-ryt branch-review">
    <div class="pcoded-content1">
        <div class="pcoded-inner-content1">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                 @if($is_review==1)
                @include("bank.mask_unmask_btn")
                @endif
                    <div class="">
                        <div class="process-wrap active-step2">
                             @include('bank.breadcrumb',['page'=>$page]) 
                        </div>
                </div>               

                <!-- Page-body start -->
                <div class="page-body">
                    <div class="tabs">
                        <ul id="tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb ovdapplicant">
                            @for($i = 1; $i <= Session::get('no_of_account_holders');$i++)
                                 @if($i == 1)
                                    <li class="nav-item" id="{{$i}}" onclick="registerTabEvent({{$i}})">
                                        <a href="#tab{{$i}}" class="nav-link">
                                            {{ $accountDetails['constitution'] == 'NON_IND_HUF' ? 'Karta/Manager' : 'Primary Account Holder' }}
                                        </a>                                        
                                    </li>
                                @else
                                    <li class="nav-item" id="{{$i}}" onclick="registerTabEvent({{$i}})">
                                        @if($accountDetails['constitution'] == 'NON_IND_HUF' && $i==2)
                                        <a href="#tab{{$i}}" class="nav-link" data-id="nextapplicant-{{$i-1}}" data-toggle="tab" role="tab">HUF</a>
                                        @else
                                        <a href="#tab{{$i}}" class="nav-link" data-id="nextapplicant-{{$i-1}}" data-toggle="tab" role="tab">Applicant{{$i}}</a>
                                        @endif
                                    </li>
                                @endif                              
                            @endfor
                            @if($accountDetails['account_type'] =='2' && $accountDetails['flow_tag_1'] != 'INDI')
                            <li class="nav-item entitydetailstab">
                                <a href="#tab-entity" class="nav-link" data-id="entitydetailstab" data-bs-toggle="tab" href="#entitydetailstab" role="tab">{{$label_entity_details}}</a>
                            </li>
                            @endif
                            <li class="nav-item photographsignaturetab" style="pointer-events: none;">
                                <a href="#tab-photographsignature" class="nav-link" data-id="photographsignature" data-toggle="tab" href="#photographsignature" role="tab">CUBE AOF</a>
                            </li>
                        </ul> <!-- END tabs-nav -->
                        <form method="post" id="addOvdDocumentForm" action="javascript:void(0)">
                            <div id="tabs-content-cust" class="tabs-content-cust">
                                @if(isset($userDetails['customerOvdDetails']))
                                    @for($i = 1; $i <= $accountHoldersCount;$i++)
                                        @include('bank.addovddocumentsapplicant',['customerOvdDetails' => $userDetails['customerOvdDetails'],'i'=>$i])
                                    @endfor
                                @else
                                    @for($i = 1; $i <= $accountHoldersCount;$i++)
                                            @include('bank.addovddocumentsapplicant',['customerOvdDetails' => array(),'ProfileDetails' => array(),'i'=>$i])
                                    @endfor
                                @endif
                                @if($accountDetails['account_type'] =='2' && $accountDetails['flow_tag_1'] != 'INDI')
                                @include('bank.officeaddress')
                            @endif

                                <!-- photograph signature tab  -->
                                <div id="tab-photographsignature" class="tab-content-cust">
                                    <div class="card">
                                        <div class="col-lg-12">
                                            <input type="hidden" id="formId" name="formId" value="{{$formId}}">
                                            <div class="row px-3">

                                            <div class="col-md-4">
                                                <div class="tab-content">
                                                    <div class="form-group" id="customers_photograph">
                                                        {{-- <label>Upload Customer Photo and Signature</label> --}}
                                                        <div class="detaisl-left align-content-center ">
                                                            <label class="uploadLabel">Upload CUBE AOF</label>
                                                            <span class="{{$enable}}">
                                                                @if(isset($reviewDetails['customer_image']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['customer_image']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                @else
                                                                    <i class="fa fa-check"></i>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="add-document d-flex align-items-center justify-content-around" id="customer_photo">
                                                            @if(isset($customer_image) && ($customer_image != ''))
                                                                <div id="pf_type_div">
                                                                    @if($enable == 'display-none')
                                                                        <div class="upload-delete">
                                                                            <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                                            </button>
                                                                        </div>
                                                                    @else
                                                                        @if(isset($reviewDetails['customer_image']))
                                                                        <div class="upload-delete">
                                                                            <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                                                            </button>
                                                                        </div>
                                                                        @else
                                                                        @endif
                                                                    @endif
                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                    <img class="uploaded_image photographsignature_image" name="customers_photograph" id="document_preview_customer_photo" src="{{URL::to('/images'.$folder.'/'.$formId.'/'.$customer_image)}}" onerror="imgNotFound('CUBE AOF')">
                                                                </div>
                                                                </div>
                                                            @endif
                                                            @if(isset($customer_image) && ($customer_image != ''))
                                                                <div class="add-document-btn adb-btn-inn photographsignature_button display-none">
                                                            @else
                                                                <div class="add-document-btn adb-btn-inn photographsignature_button">
                                                            @endif
                                                                <button type="button" id="upload_customer_image" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                                                data-id="customer_photo"  data-name="customers_photograph"  data-document="Customer Photo" data-target="#upload_proof">
                                                                    <span class="adb-icon">
                                                                        <i class="fa fa-plus-circle"></i>
                                                                    </span>
                                                                    Add CUBE AOF
                                                                </button>
                                                            </div>                                             
                                                        </div>
                                                        <input type="text" style="opacity:0" name="customer_photo">
                                                    </div>
                                                </div>                                            
                                            </div>
                                            <div class="col-md-4">
                                                <div class="details-custcol-row tab-content">
                                                    <div class="details-custcol-row-top d-flex editColumnDiv ">
                                                        <div class="detaisl-left d-flex align-content-center">
                                                            <p class="lable-cus">Mode of operation</p>
                                                            <span class="{{$enable}}">
                                                                @if(isset($reviewDetails['mode_of_operation']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['mode_of_operation']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                @else
                                                                    <i class="fa fa-check"></i>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row-bootm">
                                                        <div class="comments-blck">
                                                            {!! Form::select('mode_of_operation',$modeOfOperations,$mode_of_operation,array('class'=>'form-control mode_of_operation AddAccountDetailsField',
                                                                    'id'=>'mode_of_operation','table'=>'account_details','name'=>'mode_of_operation','placeholder'=>'','readonly')) !!}
                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 signaturetype">
                                                <div class="details-custcol-row tab-content">
                                                    <div class="details-custcol-row-top d-flex editColumnDiv ">
                                                        <div class="detaisl-left d-flex align-content-center">
                                                            <p class="lable-cus">Signature Type</p>
                                                            <span class="{{$enable}}">
                                                                @if(isset($reviewDetails['signature_type']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['signature_type']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                @else
                                                                    <i class="fa fa-check"></i>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="details-custcol-row-bootm">
                                                        <div class="comments-blck">
                                                            {!! Form::select('signature_type',$signatureTypes,$signature_type,array('class'=>'form-control signature_type AddAccountDetailsField',
                                                                    'id'=>'signature_type','table'=>'account_details','name'=>'signature_type','placeholder'=>'','readonly')) !!}
                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Mode of operation for Term Deposit -->
                                            @if(in_array(Session::get('accountType') , [3,4]))
                                               <div class="col-md-4 display-none">
                                            @else
                                                <div class="col-md-4 display-none">
                                            @endif                                            
                                                <div class="details-custcol-row tab-content">
                                                    <div class="details-custcol-row-top d-flex editColumnDiv ">
                                                        <div class="detaisl-left d-flex align-content-center">
                                                            <p class="lable-cus">TD Account Holders</p>
                                                            <span class="{{$enable}}">
                                                                @if(isset($reviewDetails['mode_of_operation_td']))
                                                                    <i class="fa fa-times"></i>
                                                                    {{$reviewDetails['mode_of_operation_td']}}
                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                @else
                                                                    <i class="fa fa-check"></i>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>                                                   
                                                   
                                                    <div class="details-custcol-row-bootm">
                                                      <div class="comments-blck">
                                                        <ul  class="nav  tabs tabs-default nav-tabs-tb ovdapplicant">
                                                       
                                                        <div class="details-custcol-row col-md-12" id="mode_of_operation_td-{{$i}}">
                                                             <div class="details-custcol-row-top d-flex editColumnDiv">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['mode_of_operation_td-'.$i]))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['mode_of_operation_td-'.$i]}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>                                                   
                                                        </div>
                                                        <div class="details-custcol-row-bootm">
                                                            <div class="comments-blck mt-2">                                                              
                                                                @for($i = 1; $i <= Session::get('no_of_account_holders');$i++)
                                                                    @if($i == 1)
                                                                        <input type="checkbox" class="form-control  accountHolder" name="mode_of_operation_td" value="{{$i}}" checked disabled>
                                                                        <span class="lbl padding-8">Primary Account Holder</span> 
                                                                    @else
                                                                        @if(isset($account_holders[$i-1]))
                                                                            <input type="checkbox" class="form-control  accountHolder" name="mode_of_operation_td" value="{{$i}}" {{($account_holders[$i-1] == $i)? "checked" : ""}} {{$disabled}}>
                                                                        @else
                                                                            <input type="checkbox" class="form-control  accountHolder" name="mode_of_operation_td" value="{{$i}}">
                                                                        @endif
                                                                        <span class="lbl mt-2 padding-8">Joint Account Holder{{$i-1}}</span> 
                                                                    @endif 
                                                                @endfor
                                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                            </div>
                                                        </div>
                                                    </div>                
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Mode of operation for Term Deposit -->
                            </div>
                            </div>
                                    <div class="row">
                                        <input type="hidden" name="callCenterFlow" id="callCenterFlow" value="{{$callCenterFlow}}">
                                        <div class="col-md-12 text-center mt-3 mb-3">
                                            <a href="{{route('addaccount')}}" class="btn btn-outline-grey mr-3">Back</a>
                                            <a href="javascript:void(0)" class="btn btn-primary saveOvdDetails" id="{{$formId}}">Save and Continue</a>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- END tabs-content -->
                        </form>
                    </div> <!-- END tabs -->
                </div>
                <!-- Page-body end -->
            </div>
        </div>
    </div>
</div>
<!-- Modal large-->
<div class="modal fade custom-popup" id="upload_proof" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close position-absolute top-0 end-0 px-2" data-bs-dismiss="modal" aria-label="Close">
                <!-- <span aria-hidden="true">&times;</span> -->
            </button>
            <div class="modal-body mt-4">
                <div class="custom-popup-heading document_name">
                    <h1>Upload Aadhar Card</h1>
                </div>
                <div class="upload-blck">
                <input type="file" class="" id="inputImage" name="file" accept="image/*">
                    <div class="upload-blck-inn d-flex justify-content-center align-items-center">
                        <div class="upload-icon">
                            <img src="{{ asset('assets/images/browse-icon.svg') }}">
                        </div>
                        <div class="upload-con">                            
                            <h5>Drag & Drop or <span>Browse</span></h5>  
                        </div>
                    </div>
                </div>                
                <div class="container img-crop-blck image_preview">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-6">
                            <div class="img-container" >
                                <img id="image" class="preview_image" src="" alt="Crop Picture">
                            </div>
                            <div class="row">
                                <div class="col-md-12 docs-buttons button-page d-flex justify-content-center align-items-center">
                                    <div class="btn-group">
                                        <button type="button" class="rotate-icons" data-method="rotate" data-option="-45" title="Rotate Left">
                                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, -45)">
                                                <span class="fa fa-rotate-left"></span>
                                            </span>
                                        </button>
                                        <button type="button" class="rotate-icons" data-method="rotate" data-option="45" title="Rotate Right">
                                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, 45)">
                                                <span class="fa fa-rotate-right"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <button class="image_crop btn btn-green"> crop </button>
                                </div>      
                            </div>
                        </div>
                        <div class="col-md-6 display-none" id="img-preview-div">
                            <div class="docs-preview clearfix">
                                <img class="crop_image_preview" src="">
                                <!-- <div class="img-preview preview-lg"></div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-center mt-3">
                    <button type="button" id="uploadImage" class="btn btn-lblue saveDocument" disabled>Save document</button>
                </div>
            </div>              
        </div>
    </div>
</div>

<input type="hidden" id="pubki" value="{{config('pupvki')['pub']}}">

@endsection
@push('scripts')
<script  src="{{ asset('custom/js/ovd_details.js') }}"></script>
<script  src="{{ asset('components/jsrsa/jsrsasign-all-min.js') }}"></script>
<style type="text/css">
    .select2-container--default .select2-results__option[aria-disabled=true]{display:none};
     a{text-decoration: none!important;}
</style>
<script type="text/javascript">
     is_progress = JSON.parse('<?php echo json_encode($application_status->application_status); ?>');
     _applicantTitles = JSON.parse('<?php echo json_encode($returnTitle); ?>');
     _customerDetails = JSON.parse('<?php echo json_encode($userDetails['customerOvdDetails']); ?>');
     _reviewDetails = JSON.parse('<?php echo json_encode(count($reviewDetails)); ?>');
     _globaluser_dob_ms = JSON.parse('<?php echo json_encode($globaluser_dob_ms); ?>');
     _global_is_review = JSON.parse('<?php echo json_encode($is_review); ?>');
     _accounDetails = JSON.parse('<?php echo json_encode($accountDetails); ?>');
    var _huf_relation = JSON.parse('<?php echo json_encode($huf_relation); ?>');
  
     _encPubStrB = ['-2e', '-2e', '-2e', '-2e', '-2e', '-43', '-46', '-48', '-4a', '-4f', '-21', '-51', '-56', '-43', '-4d', '-4a', '-44', '-21', '-4c', '-46', '-5a', '-2e', '-2e', '-2e', '-2e', '-2e'];
     _encPubStrE = ['-2e', '-2e', '-2e', '-2e', '-2e', '-46', '-4f', '-45', '-21', '-51', '-56', '-43', '-4d', '-4a', '-44', '-21', '-4c', '-46', '-5a', '-2e', '-2e', '-2e', '-2e', '-2e'];

     var reviewFields = [];

     function registerTabEvent(id) {
        if (_is_review == "1" ) {// previous screen validation not required for review page
           
            return true;
        }
     

        if(id=="1"){
            return true;
        } 



        if(typeof _ovd_form_check != "undefined") {
        
            for (var key in _ovd_form_check[id-2]) {
                if (_ovd_form_check[id-2][key] === false) {
                $.growl({message: "Please validate previous screen for Applicant " + (id-1)},{type: "warning",allow_dismiss:false});
                return false;
                break;
                }
         
            }
         }
    }

    $(document).ready(function(){
        disableRefresh();
        disabledMenuItems();
        //check for direct 27
        // if("{{Session::get('customer_type')}}" == "ETB" && "{{Session::get('role')}}" == '2'){
        //     $('.photographsignaturetab').css('pointer-events', 'auto');
        //     $('.nav-item').removeClass('active');
        //     $('.photographsignaturetab').trigger('click');
        // }

        $("[name^=id_proof_osv_check],[name^=add_proof_osv_check],[name^=cur_add_proof_osv_check]").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                            required: "Kindly confirm having sighted and verified original OVD document"
                          }
                    });
            });

         _is_progress = "<?php echo Session::get('in_progress'); ?>";
        if (is_progress == "2") {
         $('.photographsignaturetab').css('pointer-events', 'auto');
        }
        
         _is_review = "<?php echo Session::get('is_review'); ?>";


        $("[name^=father_spouse]").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                            required: "Please Select Father/Spouse Name"
                          }
                    });
            });

        var type = "";
        if('{{$is_review}}' == 1){
            type = true;
        }
        addSelect2('mode_of_operation','Mode of Operation',type);
        addSelect2('signature_type','Signature Type',type);
        $.each(_customerDetails,function(key,value){
            // if("{{Session::get('customer_type')}}" == "ETB")
            if( value.is_new_customer == '0'){
                $('#title-'+key).attr('disabled','disabled');
                $('#gender-'+key).attr('disabled','disabled');
                $('#religion-'+key).attr('disabled','disabled');
                $('#id_proof-'+key).attr('disabled','disabled');
                $('#country-'+key).attr('disabled','disabled');
                $('#current_address_proof-'+key).attr('disabled','disabled');
                $('#proof_of_identity-'+key).attr('disabled','disabled');
                $('#proof_of_address-'+key).attr('disabled','disabled');
                $('#per_country-'+key).attr('disabled','disabled');
                $('#per_address_proof-'+key).attr('disabled','disabled');
                $('#proof_of_current_address-'+key).attr('disabled','disabled');
                $('#current_country-'+key).attr('disabled','disabled');

                addSelect2('title-'+key,'Title',true);
                addSelect2('gender-'+key,'Gender',true);
                addSelect2('religion-'+key,'religion',true);
                addSelect2('id_proof-'+key,'Id Proof',true);
                addSelect2('proof_of_address-'+key,'Permanent Address Proof',true);
                addSelect2('country-'+key,'Country',true);
                addSelect2('per_country-'+key,'Country',true);
                addSelect2('current_address_proof-'+key,'Current Address Proof',true);
            if("{{Session::get('role')}}" == 11){
                    addSelect2('mode_of_operation-'+key,'Mode Of Operation',true);
                    addSelect2('signature_type-'+key,'Signature Type',true);
                $('.photographsignature_button').addClass('display-none');
            }
        }else{

            if(_global_is_review == '1'){

            $('#title-'+key).prop('disabled','disabled');
            $('#gender-'+key).attr('disabled','disabled');
            $('#religion-'+key).attr('disabled','disabled');
            $('#id_proof-'+key).attr('disabled','disabled');
            $('#per_address_proof-'+key).attr('disabled','disabled');
            $('#country-'+key).attr('disabled','disabled');
            $('#current_address_proof-'+key).attr('disabled','disabled');
            $('#proof_of_identity-'+key).attr('disabled','disabled');
            $('#proof_of_address-'+key).attr('disabled','disabled');
            $('#per_country-'+key).attr('disabled','disabled');
            $('#proof_of_current_address-'+key).attr('disabled','disabled');
            $('#current_country-'+key).attr('disabled','disabled');

            // huf disabled coparsner
            $('#plus_button').attr('disabled','disabled');
            $('#minus_button').attr('disabled','disabled');
            
            $('.huf_co_name').attr('disabled', 'disabled');
            $('.coparcener_type').attr('disabled', 'disabled');
            $('.huf_relation').attr('disabled', 'disabled');
            $('.dob').attr('disabled', 'disabled');

            $('.passport_driving_expire ').attr('disabled', 'disabled');
            $('.id_psprt_dri_issue ').attr('disabled', 'disabled');
            $('.passport_driving_expire_permanent ').attr('disabled', 'disabled');
            $('.add_psprt_dri_issue  ').attr('disabled', 'disabled');
            // end
        }

                addSelect2('title-'+key,'Title',type);
                addSelect2('gender-'+key,'Gender',type);
                addSelect2('religion-'+key,'religion',type);
                addSelect2('id_proof-'+key,'Id Proof',type);
                addSelect2('per_address_proof-'+key,'Permanent Address Proof',type);
                addSelect2('country-'+key,'Country',type);
                addSelect2('current_address_proof-'+key,'Current Address Proof',type);
                addSelect2('entity_address_proof-'+key, 'Entity Address Proof', type)
            if("{{Session::get('role')}}" != 11){
                    addSelect2('mode_of_operation-'+key,'Mode Of Operation',type);
                    addSelect2('signature_type-'+key,'Signature Type',type);
            }
        }
        });

        
        var reviewCount = $('.fa-times').length;
        
        if(reviewCount > 0){
            for(var r = 0; r<reviewCount; r++){
               var filedId = $($('.fa-times')[r]).parent().parent().parent().siblings().find(':input:not([type=hidden])').attr('id');
               reviewFields.push(filedId);
                             
            }
        }

        $(".image_crop").cropper({
            aspectRatio: 640 / 320,
            autoCropArea: 0.6,
            autoCrop:false,
            dragCrop: false,
            resizable: false,
            built: function () {
                $(this).cropper("setDragMode", 'move');
                $(this).cropper("clear");
            }
        });

        $("body").on("click","button[id^='collapse_id_proof']",function(){
            $(".id_proof_image_back").cropper({
                aspectRatio: 640 / 320,
                autoCropArea: 0.6,
                autoCrop:false,
                dragCrop: false,
                resizable: false,
                built: function () {
                    $(this).cropper("setDragMode", 'move');
                    $(this).cropper("clear");
                }
            });
        });

        $("body").on("click","button[id^='collapse_add_proof']",function(){
            $(".add_proof_image_back").cropper({
                aspectRatio: 640 / 320,
                autoCropArea: 0.6,
                autoCrop:false,
                dragCrop: false,
                resizable: false,
                built: function () {
                    $(this).cropper("setDragMode", 'move');
                    $(this).cropper("clear");
                }
            });
        });

        $("body").on("click","button[id^='collapse_add_proof_front']",function(){
            $(".add_proof_image_front").cropper({
                aspectRatio: 640 / 320,
                autoCropArea: 0.6,
                autoCrop:false,
                dragCrop: false,
                resizable: false,
                built: function () {
                    $(this).cropper("setDragMode", 'move');
                    $(this).cropper("clear");
                }
            });
        });

        $("body").on("click",".addresstab",function(){
            $(".add_proof_image_front").cropper({
                aspectRatio: 640 / 320,
                autoCropArea: 0.6,
                autoCrop:false,
                dragCrop: false,
                resizable: false,
                built: function () {
                    $(this).cropper("setDragMode", 'move');
                    $(this).cropper("clear");
                }
            });
        });

        $("body").on("click",".caddresstab",function(){
            $(".add_proof_image_front").cropper({
                aspectRatio: 640 / 320,
                autoCropArea: 0.6,
                autoCrop:false,
                dragCrop: false,
                resizable: false,
                built: function () {
                    $(this).cropper("setDragMode", 'move');
                    $(this).cropper("clear");
                }
            });
        });


        $("body").on("click",".caddresstab",function(){
            $(".current_add_proof_image").cropper({
                aspectRatio: 640 / 320,
                autoCropArea: 0.6,
                autoCrop:false,
                dragCrop: false,
                resizable: false,
                built: function () {
                    $(this).cropper("setDragMode", 'move');
                    $(this).cropper("clear");
                }
            });
        });

        $('.photographsignaturetab').click(function(){
                $(".photographsignature_image").cropper({
                aspectRatio: 640 / 320,
                autoCropArea: 0.6,
                autoCrop:false,
                dragCrop: false,
                resizable: false,
                built: function () {
                    $(this).cropper("setDragMode", 'move');
                    $(this).cropper("clear");
                }
            });
        });
         
        $(".passport_driving_expire").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
             startDate: new Date(),

        });
            $(".passport_driving_expire_permanent").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            startDate: new Date(),

        });

        $(".id_psprt_dri_issue").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",           
            endDate: "today",
            maxDate: "today",

        });
            $(".add_psprt_dri_issue").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",           
            endDate: "today",
            maxDate: "today",

        });
         
        $("body").on("keypress",".per_pincode,.current_pincode",function(e){
            var length = $(this).val().length;
            if(length > 6) {
                return false;
            } else if(e.which != 5 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            } else if((length == 0) && (e.which == 48)) {
                return false;
            }
        });


        if($(".comm_flag").prop('checked') == true){
            $('input[id^="address_flag"]').trigger('change');
        }
        

        // if('{{$no_of_account_holders}}' >= 1)
        // {
        //     var valid_mop = getModeofOperations('{{$no_of_account_holders}}','{{$applicantsDOBs}}');
        //     var existingOptions = $('#mode_of_operation')[0].options.length;
        //     for(var i=existingOptions-1; i>0; i--){
        //         var currValue = $('#mode_of_operation')[0].options[i].value;
        //         var found = false;
        //         for(var j=0; j<valid_mop.length; j++) if(valid_mop[j] == currValue) found = true;
        //         if(!found) $('#mode_of_operation')[0].options[i].remove();
        //     }
        //     if('{{$mode_of_operation}}' != '')
        //     {
        //         $("#mode_of_operation").val('{{$mode_of_operation}}').trigger('change');
        //     } 
        // }

        $(document).on('click', '.toggle-password', function() {
        
            $(this).toggleClass("fa-eye-slash fa-eye");
            
            var input = $(".eye-masking");
            input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        });

        $(document).on('click', '.toggle-pass', function() {
        
            $(this).toggleClass("fa-eye-slash fa-eye");
            
            var input = $(".eye-mask");
            input.attr('type') === 'password' ? input.attr('type','text') : input.attr('type','password')
        });        
    });

// Show the first tab and hide the rest
$('#tabs-nav li:first-child').addClass('active');
$('.tab-content-cust').hide();
$('.tab-content-cust:first').show();

// Click function
$('#tabs-nav li').click(function(){
    $('#tabs-nav li').removeClass('active');
    $(this).addClass('active');
    $('.tab-content-cust').hide();
  
    var activeTab = $(this).find('a').attr('href');
    $(activeTab).fadeIn();
    return false;
});


function rsenc(data, publicKey) {
    var publicKey = document.getElementById('pubki');    
    if(publicKey && publicKey != null){
        publicKey = decPubStr(_encPubStrB)+publicKey.value+decPubStr(_encPubStrE);
        const f_publicKey = KEYUTIL.getKey(publicKey);
        // Encrypt the data using public key
        const encryptedData = KJUR.crypto.Cipher.encrypt(data, f_publicKey);
        const encryptedBase64 = hextob64(encryptedData);
        //console.log('Encrypted data:', encryptedBase64);
        return encryptedBase64;
    }else{
        alert('Encryption Failed!');
        return false;
    }
}

function decPubStr(_i='') {
  let _d = [];
  for (let i = 0; i < _i.length; i++) {
    const xv = _i[i]; const flpd = parseInt(xv, 16);
    const cc = ~flpd; const _o = String.fromCharCode(cc);
    _d += _o;
  }
  return _d;
}
$("select").select2();

</script>

@endpush