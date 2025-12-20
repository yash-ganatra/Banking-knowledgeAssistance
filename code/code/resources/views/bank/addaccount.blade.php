@extends('layouts.app')
@section('content')
@php
    $accountType = '';
    $accountlevelType = '';
    $no_of_account_holders = '1';
    // $mode_of_operation = '';
    $pf_type = 'pancard';
    $pancard_no = '';
    $education = '';
    $dob = '';
    $gross_income = '';
    $marital_status = '';
    $residential_status = '';
    $customer_account_type = '';
    $mobile_number = '';
    $email = '';
    $scheme_code = '';
    $td_scheme_code = '';
    $pan_osv_check = '';
    $class = "inactive";
    $display = "";
    $readonly = "";
    $enable = "display-none";
    $is_review = 0;
    $folder = "";
    $accountHoldersCount = 1;
    $customerOvdDetails = array();
    $AccountIds = array();
    $disabled = '';
    $page = 1;
    $flow_tag_1 = '';
    $disablecategory = '';
    $huf_all ='';
    $hidden ='';
    $schemeDetails = Session::get('td_schemeData');
    if(!isset($schemeDetails['id']))
    {
        $schemeDetails = Session::get('schemeData');
    }
    $fieldsRestricted = 'false';
@endphp
@if(count($userDetails) > 0)
    @php
        if(isset($userDetails['AccountDetails']['account_type']))
        {
            $accountType = $userDetails['AccountDetails']['account_type'];
            $accountlevelType = $userDetails['AccountDetails']['account_level_type'];
            $no_of_account_holders = $userDetails['AccountDetails']['no_of_account_holders'];
            $scheme_code = $userDetails['AccountDetails']['scheme_code'];
            if($scheme_code == 14 && $accountType == 2){
                $scheme_code = 1;
            }
            $fieldsRestricted = 'true';
        }
        if($accountType == 4)
        {
            $td_scheme_code = $userDetails['AccountDetails']['td_scheme_code'];
        }

        if($accountType == 2)
        {
            $flow_tag_1 = $userDetails['AccountDetails']['flow_tag_1'];
        }

        $disablecategory = $userDetails['AccountDetails']['constitution'];
            if($disablecategory== 'NON_IND_HUF'){
                $huf_all = true;
                $hidden = 'display-none';
            }

        $pan_osv_check = 1;
        $class = "active";
        $display = "display-none";
        $folder = "attachments";
        
    @endphp
@endif
@if(isset($userDetails['customerOvdDetails']))
    @php
        $accountHoldersCount = count($userDetails['customerOvdDetails']);
        //$accountHoldersCount = Session::get('no_of_account_holders');
        $customerOvdDetails = $userDetails['customerOvdDetails'];
        // $ProfileDetails = $userDetails['ProfileDetails'];
        $AccountIds = $userDetails['AccountIds'];
    @endphp
@endif
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $enable = "";
        $readonly = "readonly";
        $disabled = 'disabled';
        $folder = "markedattachments";
        $accountHoldersCount = $no_of_account_holders;
    @endphp
@endif
@if(Session::get('in_progress') == 1)
    @php
        $accountHoldersCount = $no_of_account_holders;
    @endphp
@endif
<style type="text/css">
.pandiv{
    position: relative;
    text-transform: uppercase;
    border: 0px;
    opacity: 0.5;
    z-index: 0;
    
}
.indivBox{
    font-family: Arial;
    font-size: 1.3em;
    background-color:#80808069;
    border: 2px solid white;

}
.pan{
    /*position: absolute; */
    /* font-family: Arial; */
    /* font-size: 1.1em; */
    /* margin-left: 5px; */
    text-transform: uppercase;
    letter-spacing: 2px;
    /* border: 0px; */
    /* opacity: 0.5; */
    z-index: 2;
    width: 100%;
}
.pan:focus{
    outline:none;
}
  .display-none-existing-customer-loader{
        display: none;
    }
 a{text-decoration: none!important;}
</style>
@if(Session::get("last_screen") == '0.5')
  <script type="text/javascript">
      function resetBrowserLastScreen(){
        $("#refreshContinue").text('Please wait...');
        $("#refreshContinue").attr('disabled','disabled');
        registerScreenFlow(0, 0);
        setTimeout(function(){
            window.location = "{{route('bankdashboard')}}";
        },3000);
      }
  </script>

  <div class="container RefreshRestrictMsg">

    <h4>Refresh not permitted in account opening flow.</h4>
      <button type="button" class="btn btn-sm" id="refreshContinue" onclick="resetBrowserLastScreen()">Continue</button>

  </div>
  <div id="forBlockMsg" style="display: none;">
      

@else
  <script type="text/javascript">
  </script>
    <div id="forBlockMsg">
@endif
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
                        <div class="process-wrap active-step1">
                        @include('bank.breadcrumb',['page'=>$page])
                    </div>
                    </div>
                    <!-- Page-body start -->
                    <div class="page-body"> 
                    <form method="post" id="addAccountForm" action="javascript:void(0)">
                        <input id="constitution" name="constitution" type="hidden" value="{{$nonindividual}}" class="AddAccountDetailsField" />
                        <div class="card">
                            <div id="casatd-key-block" class="card-block pb-0">
                                <div class="row">
                                    <input type="hidden" id="formId" name="formId" value="{{$formId}}">
                                    <div class="details-custcol-row col-lg-2 col-md-6 col-sm-6">
                                        <div class="details-custcol-row-top d-flex editColumnDiv ">
                                            <div class="detaisl-left d-flex align-content-center">
                                                <p class="lable-cus">Type of account</p>
                                                <span class="{{$enable}}">
                                                    @if(isset($reviewDetails['account_type']))
                                                        <i class="fa fa-times"></i>
                                                        {{$reviewDetails['account_type']}}
                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                    @else
                                                        <i class="fa fa-check"></i>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="details-custcol-row-bootm">
                                            <div class="comments-blck">
                                                {!! Form::select('account_type',$accountTypes,$accountType,array('class'=>'form-control account_type AddAccountDetailsField',
                                                    'table'=>'account_details','id'=>'account_type','name'=>'account_type','placeholder'=>'')) !!}
                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            </div>
                                        </div>
                                    </div>
                                   
                                    <div class="details-custcol-row col-lg-3 col-md-6 col-sm-6">
                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                            <div class="detaisl-left d-flex align-content-center ">
                                                <p class="lable-cus">
                                                    Scheme Code 
                                                    <span id="scheme_code_description" role="tooltip" aria-label="" data-microtip-position="top" data-microtip-size="medium">
                                                        <i class="fa fa-info-circle" class="tooltip" ></i>
                                                    </span>
                                                </p> 
                                                <span class="{{$enable}}">
                                                    @if(isset($reviewDetails['scheme_code']))
                                                        <i class="fa fa-times"></i>
                                                        {{$reviewDetails['scheme_code']}}
                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                    @else
                                                        <i class="fa fa-check"></i>
                                                    @endif
                                                </span>
                                            </div>                                                   
                                        </div>
                                        <div class="details-custcol-row-bootm">
                                            <div class="comments-blck">
                                                {!! Form::select('scheme_code',$savingsSchemeCodes,$scheme_code,array('class'=>'form-control scheme_code AddAccountDetailsField',
                                                    'table'=>'account_details','id'=>'scheme_code','name'=>'scheme_code','placeholder'=>'')) !!}
                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            </div>
                                        </div>
                                    </div>
                                    @if($accountType == 4)
                                        <div class="details-custcol-row col-md-6 col-lg-3 col-sm-6" id="td_scheme_code_div">
                                    @else
                                        <div class="details-custcol-row col-md-6 col-lg-3 col-sm-6 display-none" id="td_scheme_code_div">
                                    @endif
                                        <div class="details-custcol-row-top d-flex editColumnDiv">
                                            <div class="detaisl-left d-flex align-content-center ">
                                                <p class="lable-cus">
                                                    TD Scheme Code 
                                                    <span id="td_scheme_code_description" role="tooltip" aria-label="" data-microtip-position="top" data-microtip-size="medium">
                                                        <i class="fa fa-info-circle" class="tooltip" ></i>
                                                    </span>
                                                </p> 
                                                <span class="{{$enable}}">
                                                    @if(isset($reviewDetails['td_scheme_code']))
                                                        <i class="fa fa-times"></i>
                                                        {{$reviewDetails['td_scheme_code']}}
                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                    @else
                                                        <i class="fa fa-check"></i>
                                                    @endif
                                                </span>
                                            </div>                                                   
                                        </div>
                                        <div class="details-custcol-row-bootm">
                                            <div class="comments-blck">
                                                {!! Form::select('td_scheme_code',$tdSchemeCodes,$td_scheme_code,array('class'=>'form-control td_scheme_code AddAccountDetailsField',
                                                    'table'=>'account_details','id'=>'td_scheme_code','name'=>'td_scheme_code','placeholder'=>'')) !!}
                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            </div>
                                        </div>
                                    </div>
                                     <div class="details-custcol-row col-lg-2 col-md-6 col-sm-6 npof-ach {{$hidden}}">
                                        <div class="details-custcol-row-top d-flex editColumnDiv ">
                                            <div class="detaisl-left d-flex align-content-center">
                                                <p class="lable-cus">
                                                    No of Account Holders
                                                </p>
                                                <span class="{{$enable}}">
                                                    @if(isset($reviewDetails['no_of_account_holders']))
                                                        <i class="fa fa-times"></i>
                                                        {{$reviewDetails['no_of_account_holders']}}
                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                    @else
                                                        <i class="fa fa-check"></i>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        @if(Session::get('role') == 11)
                                            <div class="details-custcol-row-bootm delight_applicant_counter">
                                        @else
                                            <div class="details-custcol-row-bootm">
                                        @endif
                                            <div class="comments-blck">
                                                <div class="input-group mb-3 plus-minus">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="btn btn-lblue btn-sm" id="minus-btn" {{$disabled}}>
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" table="account_details" id="qty_input" name="no_of_account_holders" class="form-control form-control-sm AddAccountDetailsField" value="{{$no_of_account_holders}}" min="1" disabled="">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="btn btn-lblue btn-sm" id="plus-btn" {{$disabled}}>
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$huf_all)
                                    <div class="details-custcol-row col-lg-2 col-md-6 col-sm-6">
                                        <div class="details-custcol-row-top d-flex editColumnDiv ">
                                            <div class="detaisl-left d-flex align-content-center">
                                                <p class="lable-cus">Category</p>
                                                <span class="{{$enable}}">
                                                    @if(isset($reviewDetails['account_level_type']))
                                                        <i class="fa fa-times"></i>
                                                        {{$reviewDetails['account_level_type']}}
                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                    @else
                                                        <i class="fa fa-check"></i>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="details-custcol-row-bootm">
                                            <div class="comments-blck">
                                                {!! Form::select('account_level_type',$accountlevelTypes,$accountlevelType,array('class'=>'form-control account_level_type AddAccountDetailsField',
                                                    'table'=>'account_details','id'=>'account_level_type','name'=>'account_level_type')) !!}
                                                <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    <!-- current account only -->
                                    
                                    <div class="details-custcol-row col-lg-2 col-md-6 col-sm-6 display-none" id="currentAccountProInd">
                                    <div class="details-custcol-row-top d-flex">
                                            <div class="detaisl-left d-flex align-content-center">
                                                <p class="lable-cus pop_in">
                                                   {{ $nonindividual == 'NON_IND_HUF' ? 'HUF / PROP' : 'Proprietorship/Individual' }}
                                                </p>   
                                </div>
                            </div>
                                        <div class="details-custcol-row-bootm">
                                            <div class="comments-blck">
                                                <select class="form-control currentPropInd display-none" name="flow_tag_1" id="current_prop_indi">
                                                    @if($nonindividual == 'NON_IND_HUF')
                                                    <option value="INDI" selected>HUF</option>
                                                    <option value="PROP">HUF / PROP</option>
                                                    @else
                                                    <option value="INDI" selected>Individual</option>
                                                    <option value="PROP">Proprietorship</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end current account -->
                                </div>
                            </div>
                        </div>                        
                        <div class="tabs" id="PAN_F60_Tabs">
                            <ul id="tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb tabList">
                                @for($i = 1; $i <= $accountHoldersCount;$i++)
                                  @if($i == 1)
                                    <li class="nav-item" id="Primary Account Holder" onclick="registerTabEvent({{$i}})">
                                        <a href="#tab{{$i}}" class="nav-link" id='primary_karta_mag_text'>
                                            {{ $nonindividual == 'NON_IND_HUF' ? 'Karta/Manager' : ' Primary Account Holder' }}

                                        </a>
                                    </li>
                                  @else
                                    <li class="nav-item" id="Joint Account Holder{{$i-1}}" onclick="registerTabEvent({{$i}})">
                                        <a href="#tab{{$i}}" class="nav-link" data-id="nextapplicant-{{$i-1}}" data-toggle="tab" role="tab">
                                            @if($nonindividual == 'NON_IND_HUF') HUF @else Applicant{{$i}} @endif
                                        </a>
                                    </li>
                                  @endif
                                @endfor
                            </ul> 

                            <!-- END tabs-nav -->
                            <div id="tabs-content-cust" class="tabs-content-cust">
                                <div id="pan60active_blur"></div>
                                @for($i = 1; $i <= $accountHoldersCount;$i++)
                                    @include('bank.addaccountapplicant',['customerOvdDetails' => $customerOvdDetails, 'AccountIds' => $AccountIds,'i'=>$i])
                                @endfor    
                            </div> <!-- END tabs-content -->
                        </div> <!-- END tabs -->
                        </form> 
                    </div>

                    <div class="col-md-12 text-center mt-3 mb-3">
                        <!-- <a href="#" class="btn btn-primary">Save and Continue</a> -->
                        <!-- <button type="submit" class="btn btn-primary" id="saveAccountDetails">Save and Continue</button> -->
                    </div>
                </div>
                <!-- Page-body end -->
            </div>
        </div>
    </div>
</div>
<!-- Modal large-->
<div class="modal fade custom-popup" id="upload_pan" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close position-absolute top-0 end-0 px-2" data-bs-dismiss="modal" aria-label="Close">
                <!-- <span aria-hidden="true">&times;</span> -->
            </button>
            <div class="modal-body mt-4">
                <div class="custom-popup-heading document_name">
                    <h1>Upload Image</h1>
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
<div class="modal fade" id="customer_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-etb">
            <div class="modal-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h4 class="modal-title">Existing Customer<br></h4>
                    </div>
                    <div class="col-sm-12">
                        <span class="existing-title d-block">Please provide any one of the below details</span>
                    </div>
                </div>
                <!--  -->
                <!-- <h6 class="">Please provide one of the below details</h6> -->
                
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>
            <div class="br_submit_loader display-none-existing-customer-loader">
                  <div class="br_submit_loader__element"></div>
            </div>

            <div class="row mt-4 ml-2 py-4">
               <div class="mr-1 ml-2">
                   <p class="lable-cus mt-2 px-3">Customer ID</p> 
               </div>
            <!-- 22May23 - For BS5 - commented below line -->
               <!-- <div class="col-6 ml-5"> -->
                <div class="col-6 ml-5 position-absolute top-50 start-50 translate-middle-x">
                   <input type="text" class="form-control" id="customer_id" name="customer_id" placeholder="Customer Id" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" maxlength="9">
               </div>
            </div>

            <!-- <div class="row mt-3 ml-2 mb-4">
               <div class="mr-1 ml-2" style="display: inherit;">
                   <p class="lable-cus mt-2">PAN Number</p> 
                   <a href="javascript:void(0)" style="margin-left: 9px;" class="panIsValid mt-2" id="panIsValid_{{$i}}">
                        <i class="fa fa-refresh" aria-hidden="true"></i>
                   </a>
               </div>
               <div class="col-6 ml-4">
                   <input tyle="text" class="form-control pan" id="etb_pancard_no" name="pancard_no" onkeyup="this.value = this.value.toUpperCase();">
               </div>
            </div>
 -->
            <div class="row mt-3 mx-md-n5 display-none">
                <div class="col-sm-12 d-flex">
                    <div class="d-flex ml-3">
                        <div class="d-flex">
                            <p class="lable-cus mt-2">PAN Number</p>
                            <a href="javascript:void(0)" style="margin-left: 5px;" class="panIsValid mt-2" id="panIsValid_{{$i}}">
                                <i class="fa fa-refresh" aria-hidden="true"></i>
                            </a> 
                        </div>
                        <div class="col-8 ml-2">
                            <input tyle="text" class="form-control pan" id="etb_pancard_no" name="pancard_no" onkeyup="this.value = this.value.toUpperCase();">
                        </div>
                    </div>
                    <div class="col-sm-6 d-flex">
                        <div class="ml-3 d-flex">
                            <p class="lable-cus mt-2">DOB</p> 
                        </div>
                        <div class="col-8">
                            <input type="text" class="form-control dob" id="dob" name="dob" value="{{$dob}}" {{$readonly}}>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3 ml-2 display-none">
               <div class="mr-1 ml-2">
                   <p class="lable-cus mt-2">Mobile Number</p> 
               </div>
               <div class="col-6 ml-2">
                   <input type="text" class="form-control AddPanDetailsField mobile_number mobile" table="customer_ovd_details" id="mobile_number-{{$i}}" name="mobile_number" value="{{$mobile_number}}" {{$readonly}}>
               </div>
            </div>
              
            <div class="row mt-3 ml-2 mb-3 display-none">
               <div class="mr-1 ml-2">
                   <p class="lable-cus">Account Number</p> 
               </div>
               <div class="col-6 mr-2">
                   <input type="text" class="form-control" id="account_no" name="account_no" placeholder="Account Number">
               </div>
            </div>

            <div class="modal-footer modal-display">
                <button type="button" class="btn btn-primary pull-right waves-effect waves-light" id="checkCustomer">Search</button>
                <button type="button" class="btn btn-default pull-right waves-effect mr-2" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>
<input type="hidden" id="pubki" value="{{config('pupvki')['pub']}}">


 @if(Session::get('role') == 11)
  <input type="hidden" name="ccrole" value="Y" id="ccrole">
   @include('bank.ccgrid');
 @endif
 

@endsection
@push('scripts')
<script  src="{{ asset('custom/js/basic_details.js') }}"></script>
<script  src="{{ asset('components/jsrsa/jsrsasign-all-min.js') }}"></script>
<script type="text/javascript">
    var _nonInd = $("#constitution").val();
    _globalSchemeDetails = JSON.parse('<?php echo json_encode($schemeDetails); ?>');
    _globalTDSchemeDetails = JSON.parse('<?php echo json_encode($schemeDetails); ?>');
    _is_progress = "<?php echo Session::get('in_progress'); ?>";
    _is_review = "<?php echo Session::get('is_review'); ?>";
    _globalPanOkToContinue = false;
    _flow_tag_1 = JSON.parse('<?php echo json_encode($flow_tag_1); ?>');
    _encPubStrB = ['-2e', '-2e', '-2e', '-2e', '-2e', '-43', '-46', '-48', '-4a', '-4f', '-21', '-51', '-56', '-43', '-4d', '-4a', '-44', '-21', '-4c', '-46', '-5a', '-2e', '-2e', '-2e', '-2e', '-2e'];
    _encPubStrE = ['-2e', '-2e', '-2e', '-2e', '-2e', '-46', '-4f', '-45', '-21', '-51', '-56', '-43', '-4d', '-4a', '-44', '-21', '-4c', '-46', '-5a', '-2e', '-2e', '-2e', '-2e', '-2e'];

    var registerDOBevents = function(){
        $(".dob").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",            
            autoclose: true,
        }).on('change', function () {
            var curr = $(this);
            var idSequence = 1;
           
            if(curr[0].id != null){
                idSequence = curr[0].id.split('-')[1];
            }                        

            var dob_date_string = moment(this.value, "DD-MM-YYYY").format("YYYY-MM-DD");
                
            if(!checkschemeForAgeok(idSequence,dob_date_string)){
                return false;
            }
            
        });     
    }

    function registerTabEvent(id) {
        if (_is_review == "1" ) {// previous screen validation not required for review page
            return true;
        }
        //console.log('registerEvent'+id);
        if(id=="1"){
            return true;
        } 
        disableAddingApplicant();
        disableSchemCodeChange();
        
        if(typeof _basic_form_check != "undefined") {
           if(_basic_form_check[id-2]['basic_account-'+(id-1)]!= true){
                //console.log(_basic_form_check[id-2]['basic_account-'+(id-1)]);
              $.growl({message: "Please validate previous screen for Applicant " + (id-1)},{type: "warning",allow_dismiss:false});
              return false;
           }else{
             _globalPanOkToContinue = false;
           }           
         
        }
    }
  

    $(document).ready(function(){
        disableRefresh();
        disabledMenuItems();

        if(_flow_tag_1 == ''){
            $('#current_prop_indi').val('INDI');
        }else{
        $('#current_prop_indi').val(_flow_tag_1);
        }
        // if(($("#scheme_code").val() == '' ) || ($("#scheme_code").val() == null) ){

        //     disableAddingApplicant();
        // }else{

        //     enableAddingApplicant();
        // }

        // if(($("#td_scheme_code").val() == '') || ($("#td_scheme_code").val() == null)){
            
        //     disableAddingApplicant();
        // }else{

        //     enableAddingApplicant();
        // }

        _max_screen = "<?php echo Session::get('max_screen'); ?>";
        _last_screen = "<?php echo Session::get('last_screen'); ?>";
        _accountType = JSON.parse('<?php echo json_encode($accountType); ?>');
      
        if (_accountType == 5) {
            // $('.existing_cust').html('&nbsp');
            // $('#etb_button-1').css('display','none');
            $('#etb_button-1').css('visibility','hidden');

        }

        if('{{$formId}}' != ''){
            panIsvalid = 1;
        }
        pan_check = "{{env('PAN_CHECK')}}";
        if('{{$is_review}}' != 1){
            if(_max_screen == 0 ){
            setTimeout(function(){
                //Check if scheme code value exist (it could be a refresh) then get the scheme details
                if($("#scheme_code").val() != '') $("#scheme_code").val($("#scheme_code").val()).change();
                if($("#td_scheme_code").val() != '') $("#td_scheme_code").val($("#td_scheme_code").val()).change();
            }, 1500);   
        }
        }else{
            getSchemeDetails('{{$accountType}}');
        }
        
/*====================================Get Scheme Details During Forward and Backward==================*/
        if((_max_screen > 0) || ('{{$is_review}}' == 1) ){
            $('#account_type').prop('disabled',true);
            setUIForBasicDetailsAlreadyFilled();
        }
/*====================================Disable process steps in review==================*/

        if((_max_screen == 0) && ('{{$is_review}}' == 1) ){
            $('.process-step').css('pointer-events', 'none');
        }else{
            $('.process-step').css('pointer-events', '');
        }

        		
        if('{{$accountType}}' == 4)
        {
            $(".customer_account_type option[value='3']").prop('disabled',true).trigger('change');
        }
        
        $("#account_type option[value='2']").prop('enable',true);
        // To greyout Simplified and 'Small Account Level Type'
        $("#account_level_type").val('01').trigger('change');
        $("#account_level_type option[value='02']").prop('disabled',true).trigger('change');
        $("#account_level_type option[value='03']").prop('disabled',true).trigger('change');
        $("#account_level_type option[value='04']").prop('disabled',true).trigger('change');
        $("#account_level_type option[value='05']").prop('disabled',true).trigger('change');
        //To greyout residential status
        $(".residential_status option[value='2']").prop('disabled',true).trigger('change');
        $(".residential_status option[value='3']").prop('disabled',true).trigger('change');
        $(".residential_status option[value='4']").prop('disabled',true).trigger('change');
        $(".residential_status option[value='5']").prop('disabled',true).trigger('change');
        $(".residential_status option[value='6']").prop('disabled',true).trigger('change');
        $(".residential_status option[value='7']").prop('disabled',true).trigger('change');
        
        var type = '';       
        if('{{$is_review}}' == 1){
            type = true;
        }
        addSelect2('account_type','Account Type',type);
        addSelect2('account_level_type','Account Type',type);
        addSelect2('scheme_code','Scheme Code',type);
        addSelect2('td_scheme_code','TD Scheme Code',type);
        addSelect2('education','Education',type);
        addSelect2('gross_income','Gross Income',type);
        addSelect2('country_of_birth','Country of Birth',type);
        addSelect2('citizenship','Citizenship',type);
        addSelect2('marital_status','Marital Status',type);
        addSelect2('residential_status','Residential Status',type);
        addSelect2('customer_account_type','Customer Account Type',type);
        addSelect2('huf_signatory_relation','Relationship Between HUF & Signatory',type);
        addSelect2('currentPropInd','Current Proprietorship or Individual',type);

        

        

        imageCropper("document_preview_pf_type");

        $('#qty_input').prop('disabled', true);
        $('#plus-btn').click(function(){

        
         //-------------No of Applicant Validations For Savings-----------------------// 
        if($('#account_type').val() == 1){
            if(_nonInd != "NON_IND_HUF"){
            $('#plus-btn').addClass('minusplus_button_disable');
            }
            var currentCount  = parseInt($('#qty_input').val());
            if(typeof(_globalSchemeDetails) == 'undefiend'){
                var maxAllowed = 4;
            }else{
               var maxAllowed = _globalSchemeDetails.no_of_applicant;
            }

            if(($("#scheme_code").val() == '' ) || ($("#scheme_code").val() == null) ){

              disableAddingApplicant();
               $.growl({message: "Please Select Savings Scheme Code"},{type: "warning",delay:2000,allow_dismiss:false});
                 return false;
            }else{

                enableAddingApplicant();
            }


            // setTimeout(function(){
            //     $('#plus-btn').removeClass('minusplus_button_disable');
            // },2000);

            if((currentCount+1) <= maxAllowed){
               $.growl({message: "Adding applicant, Please wait..."},{type: "warning",delay:2000,allow_dismiss:false});
               if(_nonInd != "NON_IND_HUF"){
               $('#qty_input').val(currentCount+1);
               }
            }else{
             if(_nonInd != "NON_IND_HUF"){
             $.growl({message: "Number of Applicant(s) has reached permissible limit"},{type: "warning",delay:2000,allow_dismiss:false});
             return false;
            }
            }

        }else if(($('#account_type').val() == 3) || ($('#account_type').val() == 4)){


            $('#plus-btn').addClass('minusplus_button_disable');
            var currentCount  = parseInt($('#qty_input').val());
            if(typeof(_globalTDSchemeDetails) == 'undefiend'){
                var maxAllowed = 4;
            }else{
               var maxAllowed = _globalTDSchemeDetails.joint_applicant_related;
            }
            
            if($('#account_type').val() == 4){


            if(($("#td_scheme_code").val() == '') || ($("#td_scheme_code").val() == null)){
            
              disableAddingApplicant();
                $.growl({message: "Please Select TD Scheme Code"},{type: "warning",delay:2000,allow_dismiss:false});
                 return false;
        
                }else{

                    enableAddingApplicant();
                }
            }else{

                if(($("#scheme_code").val() == '' ) || ($("#scheme_code").val() == null) ){

              disableAddingApplicant();
               $.growl({message: "Please Select Scheme Code"},{type: "warning",delay:2000,allow_dismiss:false});
                 return false;
                }else{

                    enableAddingApplicant();
                }

            }
            // setTimeout(function(){
            //     $('#plus-btn').removeClass('minusplus_button_disable');
            // },2000);

            if((currentCount+1) <= maxAllowed){
               $.growl({message: "Adding applicant, Please wait..."},{type: "warning",delay:2000,allow_dismiss:false});
               if(_nonInd != "NON_IND_HUF"){
               $('#qty_input').val(currentCount+1);
               }
            }else{
             
             $.growl({message: "Number of Applicant(s) has reached permissible limit"},{type: "warning",delay:2000,allow_dismiss:false});
             return false;
            }

        }else{
            if(_nonInd!="NON_IND_HUF" && $("#account_type").val()=="2") return false;
            $('#plus-btn').addClass('minusplus_button_disable');
            if(_nonInd != "NON_IND_HUF"){
            $('#qty_input').val(parseInt($('#qty_input').val()) + 1 );
            }
            if ($('#qty_input').val() > 4) {
                $('#qty_input').val(4);
                return false;
            }

            if(($("#scheme_code").val() == '' ) || ($("#scheme_code").val() == null) ){

              disableAddingApplicant();
               $.growl({message: "Please Select Scheme Code"},{type: "warning",delay:2000,allow_dismiss:false});
                 return false;
            }else{

                enableAddingApplicant();
            }


        


            // setTimeout(function(){
            //     $('#plus-btn').removeClass('minusplus_button_disable');
            // },2000);

            if ($('#qty_input').val() <= 4) {
                $.growl({message: "Adding applicant, Please wait..."},{type: "warning",delay:2000,allow_dismiss:false});

            }else{
                $.growl({message: "Number of Applicant(s) has reached permissible limit"},{type: "warning",delay:2000,allow_dismiss:false});
                return false;

            }

        }
            
        });

        $('#minus-btn').click(function(){
            $('#qty_input').val(parseInt($('#qty_input').val()) - 1 );
            if ($('#qty_input').val() == 0) {
                $('#qty_input').val(1);
                return false;
            }
        });

        $('.pan').inputmask("aaaa-a-9999-a", { 
            "placeholder": "xxxx-x-0000-x",
            autoUnmask: true,
        });

        $('.mobile').inputmask('9999-999-999', {
            // clearMaskOnLostFocus: false, 
            autoUnmask: true,
        });

        //static mask  
        var spanStr='';
        for(var i=0; i<10; i++){
            spanStr += '<span class="indivBox">&#8195;</span>';
        }
        registerDOBevents();
        if($('.dob').val() != ''){
            setTimeout(function(){
                var idSequence = 1;
                if($('.dob')[0].id != null){
                    idSequence = $('.dob')[0].id.split('-')[1];
                }  
            $('#dob-'+idSequence).trigger('change');
           
            },3000);
        }

        $('.select2-selection--single[aria-labelledby="select2-scheme_code-container"]').on('click',function(e){
        
            if($('#account_type').val() == ""){
               $.growl({message: "Please select Account type"},{type: "warning",delay:1500,allow_dismiss:false});
               $('#scheme_code').val(null).trigger('change');
               $('.select2-dropdown').hide()
               e.preventDefault();
            }else{
                $('.select2-dropdown').show()
            }
        });

        $("#PAN_F60_Tabs").addClass('pan60active');
        $("#pan60active_blur").addClass('pan60active_blur');

        if(($("#scheme_code").val() != null) && ($("#scheme_code").val() != '')){
            setTimeout(function(){
                $("#PAN_F60_Tabs").removeClass('pan60active');
                $("#pan60active_blur").removeClass('pan60active_blur');
                $('#minus-btn').removeClass('minusplus_button_disable');
                $('#plus-btn').removeClass('minusplus_button_disable');    
            },2000)
        }
       
        if('{{Session::get('role')}}' == "11"){
            if(('{{Session::get('in_progress')}}' == 0) && ('{{Session::get('last_screen')}}' < 1)){

                $('#account_type').val(3).trigger('change');
                setTimeout(function(){
                $('#account_type').prop('disabled',true);
                }, 2000);
            }else{
                $('#account_type').prop('disabled',true); 
            }
            
        }

    });
</script>

<script>
// Show the first tab and hide the rest
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

</script>

<script type="text/javascript">
    jQuery(document).ready(function () {

        registerScreenFlow(0.5, 0);

        jQuery(".mobile_number").keypress(function (e) {
            var length = jQuery(this).val().length;
            if(length > 9) {
                return false;
            } else if(e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
                return false;
            } else if((length == 0) && (e.which == 48)) {
                return false;
            }
        });

        if ({{$fieldsRestricted}}) {
            $('#account_type').prop('disabled',true);
            $('#scheme_code').prop('disabled',true);
            $('#minus-btn').prop('disabled',true);
            $('#plus-btn').prop('disabled',true);
            $('#account_level_type').prop('disabled',true);
            $('#current_prop_indi').prop('disabled',true);
            $('[id^=etb_button]').prop('disabled',true);
        }
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

</script>
@if($flow_tag_1=="" && $is_review==0)
    <script>
        $("body").on("click","#nextapplicant-1",function(){
            let input = $("#constitution");
            if(input.val()=="NON_IND_HUF"){
            $("#email-2").val($("#email-1").val());
            $("#mobile_number-2").val($("#mobile_number-1").val());
            }
        })
    </script>
@endif
@endpush