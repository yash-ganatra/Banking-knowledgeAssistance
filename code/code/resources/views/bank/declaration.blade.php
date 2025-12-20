@extends('layouts.app')
@section('content')
@php
    $redo = false;
    if(Session::get('last_screen') < Session::get('max_screen')){

        $redo = false;
    }
    $minor = '';
    $vernacular = '';
    $name_mismatch = '';
    $pep_approval = '';
    $treasury_approval = '';
    $annexure_approval = '';
    $third_party_approval = '';
    $cash_approval = '';
    $zero_approval = '';
    $scheme_declaration_proof = '';
    $scheme_declaration = '';
    $other = '';
    $gpa_required = '';
    $two_way_sweep = '';
    if(in_array(Session::get('accountType'),[1,2,4,3]))
    {
        $gpa_required = '0';
        $two_way_sweep = '0';
    }
    $card_type = '';
    $gpaplan = '';
    $kit_number = '';
    $auto_renew_gpa = '0';
    $termautorenewal = '';
    $minor_proof = '';
    $vernacular_proof = '';
    $name_mismatch_proof = '';
    $pep_approval_proof = '';
    $treasury_approval_proof = '';
    $annexure_approval_proof = '';
    $third_party_approval_proof = '';
    $cash_approval_proof = '';
    $zero_approval_proof = '';
    $other_proof = array();
    $delightKitPhotographDisplay = '';
    $is_review = '';
    $disabled = '';
    $readonly = '';
    $display = "display-none";
    $enable = "display-none";
    $tddisabled = '';
    $acknowledgement_receipt = '';
    $delight_kit_photograph = '';
    $acknowledgement_receipt_proof = '';
    $account_number = '';
    $customer_id = '';
    
    $page = 6;
@endphp
@if(!isset($nominee_exists))
    @php
        $nominee_exists = '';
    @endphp
@endif
@if(count($userDetails) > 0)
    @php
        //for savings and savingsTD
        if($accountType != 3){ 
            $gpa_required = $userDetails['AccountDetails']['gpa_required'];
            $two_way_sweep = $userDetails['AccountDetails']['two_way_sweep'];
        }
        if((isset($userDetails['DelightDetails'])) && ($delightSavings)){
            //$acknowledgement_receipt = $userDetails['Declarations']['acknowledgement_receipt'];
            //$delight_kit_photograph = $userDetails['Declarations']['delight_kit_photograph'];
            //$acknowledgement_receipt_proof = $userDetails['Declarations']['acknowledgement_receipt_proof'];
            //$delight_kit_photograph_proof = $userDetails['Declarations']['delight_kit_photograph_proof'];
            $account_number = $userDetails['DelightDetails']['account_number'];
            $kit_number = $userDetails['DelightDetails']['id'];
            $customer_id = $userDetails['DelightDetails']['customer_id'];
        }
        $gpaplan = $userDetails['AccountDetails']['gpaplan'];
        $card_type = $userDetails['AccountDetails']['card_type'];
        $auto_renew_gpa = $userDetails['AccountDetails']['auto_renew_gpa'];
        $termautorenewal = $userDetails['AccountDetails']['termautorenewal'];

        $display = "";
        $folder = "attachments";
    @endphp
@endif
@if(Session::get('is_review') == 1)
    @php
        $is_review = 1;
        $disabled = 'disabled';
        $enable = '';
        $folder = "markedattachments";
    @endphp
@endif
@if($accountType == 3)
    @php
        $tddisabled = 'disabled';
    @endphp
@endif
@php
$image_mask_blur = "";
$def_blur_image = "";
if($is_review==1){
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
}


if($accountDetails['constitution'] == 'NON_IND_HUF'){
    $is_huf_display = true;
 }else{
    $is_huf_display = false;
 }

@endphp
<style type="text/css">
    .switch {
  position: relative;
  display: inline-block;
  width: 90px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #2ab934;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #e28500b0;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2ab934;
}

input:checked + .slider:before {
  -webkit-transform: translateX(55px);
  -ms-transform: translateX(55px);
  transform: translateX(55px);
}

/*------ ADDED CSS ---------*/
.on
{
  display: none;
}

.on, .off
{
  color: white;
  position: absolute;
  transform: translate(-50%,-50%);
  top: 50%;
  left: 50%;
  font-size: 10px;
  font-family: Verdana, sans-serif;
}

input:checked+ .slider .on
{display: block;}

input:checked + .slider .off
{display: none;}

/*--------- END --------*/

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
     border-radius: 34px;
            height: 31px;
    width: 81px;
}

.slider.round:before {
  border-radius: 50%;}

    .slider.round:before {
    border-radius: 50%;
}
 a{text-decoration: none !important;}    
</style>
<input type="hidden" name="declaration" class="declarationform">
<div class="pcoded-content1 branch-review">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
            @if($is_review==1)
                @include("bank.mask_unmask_btn")
                @endif
                <div class="">
                    <div class="process-wrap active-step6">
                        @include('bank.breadcrumb',['page'=>$page])
                    </div>
                </div>
                
                <!-- Page-body start -->
                <div class="tab-pane documentstab" id="termdeposit" role="tabpanel">
                    <form method="post" id="declarationsForm" action="javascript:void(0)">
                        <div class="page-body">
                            <div class="tabs">
                                <ul id="tabs-nav" class="nav nav-tabs tabs tabs-default nav-tabs-tb ovdapplicant">
                                    <li class="nav-item firsttab">
                                        <a href="#tab" class="nav-link">Declaration</a>
                                    </li>
                                    @if(in_array($accountType,[1,2,4]))
                                        <li class="nav-item secondTab">
                                            <a href="#tab2" class="nav-link" data-id="termdeposit" data-bs-toggle="tab" href="#termdeposit" role="tab">Services</a>
                                        </li>
                                    @endif
                                    @if($delightSavings)
                                        <li class="nav-item secondTab">
                                            <a href="#tab3" class="nav-link" data-id="delight" data-bs-toggle="tab" href="#delight" role="tab">Delight</a>
                                        </li>
                                    @endif
                                </ul>

                                <div class="card">
                                    <div id="tab" class="tab-content-cust">
                                        <span class="visibility_check" id="visibility_check"></span>
                                        <div class="card-block card-block-sign">
                                            <h4 class="sub-title">Declaration</h4>
                                        
                                        @if(isset($accountDetailsforScheme['source']) && $accountDetailsforScheme['source'] == 'CC')

                                            @if(isset($schemeDetails->ri_nri) && $schemeDetails->ri_nri == 'NRI')
                                            @include('bank.callcenterdeclaration')
                                            <div id="nri_email_date" style="display: block;">
                                                <p class="lable-cus mb-1 mt-3">
                                                    <b>NRI EMAIL DATE</b>
                                                </p>
                                                <input type="text" id="date_picker" class="form-control" style="width:27%;" placeholder="Please Enter Date" pattern="\d{4}-\d{2}-\d{2}" >
                                                <p id="date_picker_err" class="mb-3" style="color: red;"></p>
                                            </div>
                                        @endif
                                        @endif
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <!--Scheme Specific Decaration -->
                                                    <div class="declarationblade mt-2 ccemail">
                                                        @if((count($checkdeclaration) !== 0) && (!empty($checkdeclaration)))
                                                            @foreach($checkdeclaration as $declaration)
                                                                @if((count($declaration) !== 0) && (!empty($declaration)))
                                                                    @if($declaration[0]->blade_id == 'acknowledgement_receipt' || $declaration[0]->blade_id == 'delight_kit_photograph')
                                                                    @else
                                                                        @include('bank.schemedeclaration')
                                                                    @endif
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </div>

                                                    @if($addotherdeclarations)
                                                        <div class="col-md-5" id="other_declarations"></div>
                                                        
                                                        <a href="javascript:void(0)" class="btn btn-primary addOtherDeclarataion">Add Other Declarations</a><br><br><br>
                                                        @if($is_review == 1 && isset($reviewDetails['other']))
                                                        <span>*Message from NPC: <span style="color: red">{{$reviewDetails['other']}}</span></span>
                                                    @endif
                                                    @endif
                                                    <br>
                                                </div>
                                            </div>

                                            <!--Next Tab -->                                            
											@if((in_array($accountType,[1,2,4])) && (!$delightSavings))
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <a href="javascript:void(0)" class="btn btn-primary nexttaservices" id="idProofNext" tab="termdeposit">
                                                            Next
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($delightSavings)
                                                <div class="row">
                                                    <div class="col-md-12 text-center">
                                                        <a href="javascript:void(0)" class="btn btn-primary nexttaservices" tab="termdeposit">
                                                            Next
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="row">
                                                @if(in_array($accountType,[3]))
                                                    <div class="col-md-12 text-center">
                                                        <a href="{{route('addnomineedetails')}}" class="btn btn-outline-grey mr-3">Back</a>
                                                        <a href="javascript:void(0)" class="btn btn-primary applyDigiSign" id="{{$formId}}">Save and Continue</a>
                                                    </div>
                                                @endif
                                            </div>
                                            <!--Next Tab End-->
                                        </div>
                                    </div>
                                </div>
                                <!--Services-->
                                @if($accountType != 3)
                                <div class="card services">
                                    <div id="tab2" class="tab-content-cust">
                                         <span class="visibility_check" id="visibility_check"></span>
                                        <div class="card-block card-block-sign">
                                            <h4 class="sub-title">Services</h4>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        @if(!$is_huf_display)
                                                        <div class="col-md-4 sub-title">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">GPA Required</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['gpa_required']))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['gpa_required']}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <label class="radio">
                                                                        <input class="servicesdata" type="radio" id="gpa_required"  name="gpa_required" value="1" {{ ($gpa_required=="1")?  "checked" : "" }}  disabled>
                                                                        <span class="lbl padding-8">Yes</span>
                                                                    </label>
                                                                    <label class="radio">
                                                                        <input classs="servicesdata" id="gpa_requiredD"  type="radio" name="gpa_required" value="0" {{ ($gpa_required=="0")? "checked" : "" }} disabled>
                                                                        <span class="lbl padding-8">No</span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if($sweeps_availability)
                                                        <div class="col-md-4 sub-title">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Two way sweep</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['two_way_sweep']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['two_way_sweep']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <label class="radio">
                                                                            <input class="servicesdata" type="radio" id="two_way_sweep_yes"  name="two_way_sweep" value="1" {{ ($two_way_sweep=="1")?  "checked" : "" }}  disabled>
                                                                            <span class="lbl padding-8">Yes</span>
                                                                        </label>
                                                                        <label class="radio">
                                                                            <input classs="servicesdata" id="two_way_sweep_no"  type="radio" name="two_way_sweep" value="0" {{ ($two_way_sweep=="0")? "checked" : "" }} disabled>
                                                                            <span class="lbl padding-8">No</span>

                                                                        </label>
                                                                    </div>
                                                                </div>
                                                        </div>
                                                        @endif

                                                        @endif  

                                                        <div class="col-md-3 sub-title">
                                                            <div class="detaisl-left d-flex align-content-center">
                                                                <p class="lable-cus">Card Type</p>
                                                                <span class="{{$enable}}">
                                                                    @if(isset($reviewDetails['card_type']))
                                                                        <i class="fa fa-times"></i>
                                                                        {{$reviewDetails['card_type']}}
                                                                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                    @else
                                                                        <i class="fa fa-check"></i>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    {!!Form::select('card_type',$cardType,$card_type,array('class'=>'form-control card_type servicesdata','table'=>'account_details','id'=>'card_type','name'=>'card_type'))!!}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if($gpa_required != 0)
                                                            <div class="details-custcol-row col-md-12" id="gpa_details">
                                                        @else
                                                            <div class="details-custcol-row col-md-12 display-none" id="gpa_details">
                                                        @endif
                                                            <!--dROP DOWN-->
                                                            <div class="row">
                                                                <div class="details-custcol-row col-md-4 mt-3">
                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                            <p class="lable-cus">
                                                                                Plan Name
                                                                                <span id="gpa_plan_description" role="tooltip" aria-label="" data-microtip-position="top" data-microtip-size="medium">
                                                                                    <i class="fa fa-info-circle" class="tooltip"></i>
                                                                                </span>
                                                                            </p>
                                                                            <span class="{{$enable}}">
                                                                                @if(isset($reviewDetails['gpaplan']))
                                                                                    <i class="fa fa-times"></i>
                                                                                    {{$reviewDetails['gpaplan']}}
                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm">
                                                                        <div class="comments-blck">
                                                                            {!! Form::select('gpaplan',$gpaplans,$gpaplan,array('class'=>'form-control gpaplan servicesdata',
                                                                                                            'table'=>'account_details','id'=>'gpaplan','name'=>'gpaplan'))!!}
                                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="details-custcol-row col-md-3 mt-3">
                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                            <p class="lable-cus">Auto Renewal</p>
                                                                            <span class="{{$enable}}">
                                                                                @if(isset($reviewDetails['auto_renew_gpa']))
                                                                                    <i class="fa fa-times"></i>
                                                                                    {{$reviewDetails['auto_renew_gpa']}}
                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm">
                                                                        <div class="comments-blck">
                                                                            <label class="radio">
                                                                                <input class="servicesdata" type="radio" id="auto_renew_gpa"  name="auto_renew_gpa" value="1" {{ ($auto_renew_gpa=="1")?  "checked" : "" }} {{$tddisabled}} {{$disabled}}>
                                                                                <span class="lbl padding-8">Yes</span>
                                                                            </label>
                                                                            <label class="radio">
                                                                                <input classs="servicesdata" id="auto_renew_gpa" type="radio" name="auto_renew_gpa" value="0" {{ ($auto_renew_gpa=="0")? "checked" : "" }} {{$tddisabled}} {{$disabled}}>
                                                                                <span class="lbl padding-8">No</span>
                                                                            </label>
                                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="details-custcol-row col-md-4 mt-2">
                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                            <p class="lable-cus">Term for Auto Renewal</p>
                                                                            <span class="{{$enable}}">
                                                                                @if(isset($reviewDetails['termautorenewal']))
                                                                                    <i class="fa fa-times"></i>
                                                                                    {{$reviewDetails['termautorenewal']}}
                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                @else
                                                                                    <i class="fa fa-check"></i>
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="details-custcol-row-bootm">
                                                                        <div class="comments-blck">
                                                                            {!! Form::select('termautorenewal',$termautorenewals,$termautorenewal,array('class'=>'form-control termautorenewal servicesdata',
                                                                                'table'=>'account_details','id'=>'termautorenewal','name'=>'termautorenewal','placeholder'=>'')) !!}
                                                                            <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!--dROP DOWN END-->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    @if($delightSavings)
                                                        <div class="col-md-12 text-center">
                                                            <a href="javascript:void(0)" class="btn btn-primary nexttaservices" id="nextdelight" tab="delight">
                                                                Next
                                                            </a>
                                                        </div>
                                                    @else
                                                        @if(in_array($accountType,[1,2,4]))
                                                            <div class="col-md-12 text-center" id="saveandcontinue">
                                                                <a href="{{route('addnomineedetails')}}" class="btn btn-outline-grey mr-3">Back</a>
                                                                <a href="javascript:void(0)" class="btn btn-primary applyDigiSign" id="{{$formId}}">Save and Continue</a>
                                                            </div>
                                                        @else
                                                            <div class="col-md-12 text-center">
                                                                <a href="{{route('addnomineedetails')}}" class="btn btn-outline-grey mr-3">Back</a>
                                                                <a href="javascript:void(0)" class="btn btn-primary applyDigiSign" id="{{$formId}}">Save and Continue</a>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--Services End-->
                                @endif
                                <!--Delight Start-->
                                    @if($delightSavings)
                                        @include('bank.delight')
                                    @endif

                                <!--Delight End-->

                    </form>
                </div>
            </div>
            <!-- Page-body end -->
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
                    <h1>Upload Document</h1>
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
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/declaration.js') }}"></script>
<script type="text/javascript">
    _globalSchemeDetails = JSON.parse('<?php echo json_encode($schemeDetails); ?>');
    _dynaText = JSON.parse('<?php echo json_encode($declarationExtraInfo); ?>');
    _max_screen = "<?php echo Session::get('max_screen'); ?>";
    _last_screen = "<?php echo Session::get('last_screen'); ?>";
                                                                                
    var currDate = new Date() -10;
    $('#declaration_142').css('display','');
    $(document).ready(function(){

        $(function() {
			$("#date_picker").datepicker({
                clearBtn: true,
                format: "dd-mm-yyyy",
                endDate: "today",
                maxDate:0, 
                startDate: '-10d'       
            }).on('change', function () {
                $(this).datepicker('hide');
                var checkDate = moment($('#date_picker').val(), "DD-MM-YYYY").format("YYYY-MM-DD");    
                var currDate = new Date();
                var currDateFomate = moment(currDate, "DD-MM-YYYY").format("YYYY-MM-DD"); 
                var dayS = moment(checkDate).diff(currDateFomate, 'days');
                if(dayS < 0){
                    $('#declaration_142').removeAttr('style');
                }else{
                    $('#declaration_142').css('display','none');
                }
            });
		});

        disableRefresh();
        disabledMenuItems();
        addSelect2('card_type','Card Type',true);
        
        _availbleDeclarationUi = $('.visibility_check').length;
        _declaration_form_check = [];
        form_check_declaration_var();

        _accountType = '<?php echo Session::get('accountType'); ?>';
        if('{{$delightSavings}}')
        {
            addSelect2('kit_number','Delight Kit');
        }
        if('{{$is_review}}' == '1'){
            $('.other-declaration-div').removeClass('display-none');
            addSelect2('card_type','Card Type',true);
        }

        if(('{{$accountType}}' == '3') || ('{{$is_review}}' == '1')){
            addSelect2('gpaplan','GPA Plan',true);
            addSelect2('termautorenewal','Term for Auto Renewal',true);
            addSelect2('kit_number','Delight Kit',true);
        }else{
            addSelect2('gpaplan','GPA Plan');
            if('{{$auto_renew_gpa}}' == '0')
            {
                addSelect2('termautorenewal','Term for Auto Renewal',true);
            }else{
                addSelect2('termautorenewal','Term for Auto Renewal',false);
            }
        }
        imageCropper("document_preview_minor_proof");
        imageCropper("document_preview_vernacular_proof");
        imageCropper("document_preview_name_mismatch");
        imageCropper("document_preview_other_proof");

        $("#term_auto_renewal").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",
        });

        // if((_max_screen > _last_screen) || ('{{$is_review}}' != 1)){
        //     if(_globalSchemeDetails.sweeps_availability == "Y"){
        //         $('#two_way_sweep_yes').prop('disabled',false);
        //         $('#two_way_sweep_yes').prop("checked", true).trigger("click");
        //     }
        //     if(_globalSchemeDetails.sweeps_availability == "N"){
        //         $('#two_way_sweep_yes').prop('disabled',true);
        //         $('#two_way_sweep_no').prop("checked", true).trigger("click");
        //     }

        // }
        nominee_exists = '{{$nominee_exists}}';
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

      $("#togBtn").on('change', function() {
          if ($(this).is(':checked')){
            switchStatus = $(this).is(':checked');
            $('.ccemail').addClass('display-none');
            $('#nri_email_date').css("display", "none");
              if($('#togBtn:checked').val() != 'on'){
            
            if($('.uploaded_image-1').attr('src') != ''){
                $('#nri_email-1_image_proof_div').addClass('display-none');
                    $('#nri_email-1').removeAttr('checked');

                var image = $('.uploaded_image').attr('src').split('/');
                var split_id = $('.uploaded_image').attr('id');
                var deleteImageObject = [];
                deleteImageObject.data = {};
                deleteImageObject.url =  '/bank/deleteimage';
                deleteImageObject.data['image_div'] = split_id;
                deleteImageObject.data['form_id'] =   $('#formId').val();
                deleteImageObject.data['imageName'] = image[image.length-1];
                deleteImageObject.data['functionName'] = 'DeleteImageCallBack';
                crudAjaxCall(deleteImageObject);
                return false;
            }
        }
            }
        else {
           switchStatus = $(this).is(':checked');
            $('#nri_email_date').css("display", "");
            $('.ccemail').removeClass('display-none');
            
        }
    });

    $("body").on("click",".delightDeclaration",function(){
        var name = $(this).attr("id");
        if ($("input[name="+name+"]").prop("checked") == true){
            $("#"+name+"_image_proof_div").removeClass('display-none');
            $("#dummy_image_upload").addClass('display-none');
        }else{
            $("#"+name+"_image_proof_div").addClass('display-none');
            $("#dummy_image_upload").removeClass('display-none');
        }
    });

    $("body").on("change","input[id^='gpa_required']",function(){
        if($(this).val() == 1){
            $("#gpa_details").show();
            $("#deposit").hide();
            $('#deposit_type_recurring').prop('checked', false);

            if('{{$nominee_exists}}' == 'no'){
                gpaValid = 1;
                $.growl({message: "Please Fill the Nominee Details"},{type: "warning"});
            }else{
                gpaValid = 0;
            }
        }else{
            gpaValid = 0;
            $("#gpa_details").hide();
        }
        return false;
    });
</script>
@endpush
