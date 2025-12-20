@extends('layouts.app')
@php
use App\Helpers\CommonFunctions;
$page = 3;

if($html == 'hide'){
    $mx_auto = 'mx-auto';
}else{
    $mx_auto = '';
}
$masking_fields = ["Registered Email Id","Registered Mobile Number (RMN)","Aadhaar Number","PAN Number Updation","voter_id_number","Alternate Contact Number","passport_number","aadhaar_photocopy_number","driving_licence_number","pan_number","Passport","Voter ID","Driving Licence"];
$tokenParams = Cookie::get('token');
$encrypt_key = substr($tokenParams, -5);
$prevdescription = '';
$crf_Number = Session::get('crfNumber');
$preaccnumber = '';
$prevClnId = '';
$showOvdDrop = '';
$showOvdDrop1 = '';
$showUploadDoc = 'N';
$hideFromUser = '';

@endphp
<style>  
.step-4::before{
    display: none;
}
.step-4::after{
    content: '4';
}
.row-line-height{
    line-height: 40px;
}
.rowAlign{
    font-size: 12px;
    margin-left:1px;
    margin-top:28px;
}
.rowacmAlign{
    height:50px;
    margin-top:10px;
}
.addressField{
    margin-bottom: -10px;
}
.hideElem{
    visibility: hidden;
}
a{text-decoration: none!important;}
</style>
@section('content')
<div class="dnone-ryt">
    <div class="pcoded-content1">
        <div class="pcoded-inner-content1">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    @include("bank.mask_unmask_btn")
                    <div class="">
                        <div class="process-wrap active-step1" style="margin-left:340px">
                        @include('amend.amendbreadcrum',['page'=>$page])
                        </div>
                    </div>
                    <!-- Page-body start -->
                    <div class="page-body" style="padding-top:50px;width:100%;"> 
                        <div class="card" hidden>
                            <div class="card-block">
                                <div class="row">
                                  
                                </div>
                            </div>
                        </div>
                        <div class="card" style="background-color:#F2F2F2;font-family:Franklin Gothic Book,arial, sans-serif; font-size:14px;">
                            <div class="card-block pb-0">
                                <div class="row">
                                    <div class="col-md-2 {{$mx_auto}}">
                                        <b>Selected Change</b>
                                    </div>
                           
                                    <div class="col-md-2 fieldName">
                                        <b>Field Name</b>
                                    </div>
                                    <div class="col-md-4 {{$mx_auto}}">
                                        <b>Existing Details</b>
                                    </div>
                                     <div class="col-md-3 {{$mx_auto}}">
                                       <b><center>New Details</center></b>
                                    </div>
                                    <div class="col-md-1 {{$mx_auto}}">
                                        <b>Action</b>
                                    </div>
                                </div><hr/>
                                @for($list = 0;$list<count($cleanArray);$list++)
                                 
                                    @php
                                   
                                    $divHidden = $cleanArray[$list]['fieldDiv'];
                                    $accNoCheck = '';
                                     if(isset($cleanArray[$list]['accNoChecked']) && $cleanArray[$list]['accNoChecked'] == 'Y'){

                                        $accNoCheck = 'checked';
                                     }
                                      $currentdescription = $cleanArray[$list]['description'];

                                      if($currentdescription != $prevdescription){

                                        $prevdescription = $currentdescription;

                                        $descToshow = $currentdescription;
                                        
                                      }else{

                                        $descToshow = '';
                                      }

                                    $clnId = $cleanArray[$list]['id'];
                                    $currentaccnumber = $cleanArray[$list]['accNo'];

                                        if($clnId != $prevClnId){
                                            $prevClnId = $clnId;
                                            $descToId = 'visibility';
                                            $descAccNo = $cleanArray[$list]['accNo'];

                                        }else{
                                            $descAccNo = '';
                                            $descToId = 'hidden';
                                        }

                                        $addrField = $descToshow == '' ? 'addressField' : '';

                                        if($scenario == 'ekyc'){
                                            $valueToShow = $cleanArray[$list]['ekycdata']; 
                                            
                                            if($cleanArray[$list]['ekycdata'] == ''){
                                                $valueToShow = isset($cleanArray[$list]['newValue']) && $cleanArray[$list]['newValue'] != ''? $cleanArray[$list]['newValue']:'';
                                            }
                                            
                                            if(in_array($cleanArray[$list]['id'],[18,4,15,6,5,10])){
                                                $cleanArray[$list]['btnShow'] = 'none';
                                            }else{
                                                $cleanArray[$list]['btnShow'] = '';
                                            }
                                                
                                        }else{
                                            $valueToShow = (isset($cleanArray[$list]['newValue']) && $cleanArray[$list]['newValue'] != '') ? $cleanArray[$list]['newValue']: '';      
                                            $cleanArray[$list]['btnShow'] = '';
                                        }
                                         
                                      $ekyc = false;
                                      $ekycFlag = '';
                                        if(in_array($cleanArray[$list]['id'],[18,20])){
                                            $ekyc = true;
                                            $valueToShow = 'Y';
                                        }
                                        if(in_array($cleanArray[$list]['id'],[54])){
                                            $ekyc = true;
                                            $valueToShow = 'INITIATED';
                                        }
                                    @endphp

                                    @if($addrField == '' && $list != 0)
                                        <hr>
                                    @endif
                                    <div class="row amendRow {{$addrField}} {{$divHidden}}" style="line-height: 40px;" id="amendRow_{{$cleanArray[$list]['id']}}" >
                                        <div class="col-md-2 {{$mx_auto}}">
                                            <span class="descField" id="desc_field_{{$cleanArray[$list]['id']}}">{{$descToshow}}</span>
                                                <p>
                                            @if($cleanArray[$list]['accNo'] != '')

                                                    <span><input type="checkbox" class="custom-control-input customChecks checkMultiple_{{$cleanArray[$list]['id']}} " id="customChecks_{{$cleanArray[$list]['id']}}_{{$list}}" {{$accNoCheck}} style="visibility:{{$descToId}};" disabled checked></span>
                                            @endif
                                                    <span style="margin-left:20px" class="acctSeqNum" id="act_seq_{{$cleanArray[$list]['id']}}_{{$list}}">{{$descAccNo}}</span>
                                                </p>
                                        </div>
                                        <div class="col-md-2 fieldName">
                                            <span class="fieldname" id="field_name_{{$cleanArray[$list]['id']}}_{{$list}}">{{$cleanArray[$list]['fieldName']}}</span>
                                        </div>
                                            @php
                                                $disabled ='';
                                                $background = 'white';
                                            @endphp
                                                @php
                                                    if($scenario != 'manual'){
                                                        if($cleanArray[$list]['ekycdata'] != '' || $cleanArray[$list]['required'] == 'N'){
                                                            $disabled = 'disabled';
                                                        }
                                                        if($cleanArray[$list]['ekycdata'] == '' && in_array($cleanArray[$list]['fieldName'],['CUST_COMU_ADDR3','CUST_COMU_ADDR2'])){
                                                            $disabled = '';
                                                        }
                                                    }

                                                    if(isset($cleanArray[$list]['id']) && $cleanArray[$list]['id'] == 30){
                                                        $cleanArray[$list]['btnShow'] = 'none';
                                                        if($cleanArray[$list]['fieldName'] == 'recDelFlg'){
                                                            $valueToShow = 'Y';
                                                    $cleanArray[$list]['display'] = "hidden";
                                                        }
                                                        $disabled = 'disabled';
                                                        $cleanArray[$list]['btnShow'] = 'none';
                                                        $cleanArray[$list]['saveBtn'] = 'N';
                                                        $scenario = '';
                                                if($cleanArray[$list]['amendField'] == 'regNum'){
                                            
                                                    $cleanArray[$list]['display'] = "hidden";
                                                    }
                                            }

                                            if(isset($cleanArray[$list]['id']) && $cleanArray[$list]['id'] == 29){
                                                if($cleanArray[$list]['amendField'] == 'regNum'){
                                                    $disabled = 'disabled';
                                                    $cleanArray[$list]['display'] = 'hidden';
                                                }
                                            }
                                                    if($cleanArray[$list]['id'] == 28){
                                                        if($cleanArray[$list]['amendField'] == 'regNum'){
                                                            $valueToShow = '001';
                                                            $disabled = 'disabled';
                                                    $cleanArray[$list]['display'] = 'hidden';
                                                        }
                                                    }

                                
                                                     if($cleanArray[$list]['id'] == 54){
                                                    	if($cleanArray[$list]['fieldName'] == '_TRV_FLAG'){
                                                   		$cleanArray[$list]['display'] = 'hidden';
                                                   	}
                                                   }

                                                   
                                                    if($cleanArray[$list]['id'] == 21){
                                                        if($cleanArray[$list]['fieldName'] == 'DateOfNotification'){
                                                            $valueToShow =  $currentDate;
                                                            $disabled = 'disabled';
                                            $cleanArray[$list]['display'] = 'hidden';
                                                            $cleanArray[$list]['btnShow'] = 'none';
                                                        }
                                                if($cleanArray[$list]['fieldName'] == 'CUST_NAME'){
                                                   
                                                    $valueToShow = $cleanArray[$list]['oldValue'];
                                                    $disabled = 'disabled';
                                                    $cleanArray[$list]['display'] = 'hidden';
                                                    $cleanArray[$list]['btnShow'] = 'none';
                                                    }
                                    }

                                    if($cleanArray[$list]['id'] == 31){
                                        if($cleanArray[$list]['fieldName'] == 'NAT_ID_CARD_NUM'){
                                            $valueToShow =  $cleanArray[$list]['oldValue'];
                                            if($valueToShow != ''){
                                                $disabled = 'disabled';
                                                $cleanArray[$list]['btnShow'] = 'none';
                                            }
                                        }
                                    }

                                    if($cleanArray[$list]['id'] == 26){
                                      
                                        if($cleanArray[$list]['fieldName'] == 'SCHM_CODE'){
                                            $valueToShow = $cleanArray[$list]['oldValue'];
                                            $disabled = 'disabled';
                                            
                                            $cleanArray[$list]['btnShow'] = 'none';
                                        }
                                        if($cleanArray[$list]['fieldName'] == 'GL_SUB_HEAD_CODE'){
                                            $valueToShow = $cleanArray[$list]['oldValue'];
                                            $disabled = 'disabled';
                                            $cleanArray[$list]['display'] = 'hidden';
                                            $cleanArray[$list]['btnShow'] = 'none';
                                        }
                                    }
                                    if($cleanArray[$list]['id'] == 50){
                                      
                                      if($cleanArray[$list]['fieldName'] == 'JOINTHOLDERS_CUSTID'){
                                        $valueToShow = '';
                                        $cleanArray[$list]['oldValue'] = '';
                                      }
                                    }
                                    
                                
                                @endphp
                                        @if($cleanArray[$list]['type1'] == 'Amendment')
                                        <div class="col-md-4 {{$mx_auto}}">
                                            @if($cleanArray[$list]['id'] == 6)
                                                <span class="oldValue" id="old_value_{{$cleanArray[$list]['id']}}_{{$list}}">
                                                {{date('d-m-Y',strtotime(substr($cleanArray[$list]['oldValue'],0,10)))}}</span>
                                            @else

                                            @if(in_array($cleanArray[$list]['description'],$masking_fields) && $cleanArray[$list]['oldValue'] != "")
                                            <span class="oldValue enc_label unmaskingfield" id="old_value_{{$cleanArray[$list]['id']}}_{{$list}}" style="visibility:{{$cleanArray[$list]['display']}}; display:none;">{{ CommonFunctions::encrypt256($cleanArray[$list]['oldValue'],$encrypt_key)}}</span>
                                            <span class="maskingfield" style="visibility:{{$cleanArray[$list]['display']}}; ">***********</span>
                                            @else
                                                <span class="oldValue" id="old_value_{{$cleanArray[$list]['id']}}_{{$list}}" style="visibility:{{$cleanArray[$list]['display']}}">{{$cleanArray[$list]['oldValue']}}</span>
                                            @endif                                            
                                                
                                            @endif
                                        </div>
                                                        
                                        <div class="col-md-3 {{$mx_auto}}">
                                                @php
                                                if($cleanArray[$list]['display']=='hidden'){
                                                    $hideFromUser = 'hideElem';
                                                }else{
                                                    $hideFromUser = '';
                                        }

                                                @endphp

                                                @if($cleanArray[$list]['input_type'] == 'dropdown')
                                                    @foreach($dropdownData as $dropkey => $field)
                                                        @if($dropkey == $cleanArray[$list]['fieldName'])
                                                            <div class="newValue">
                                                                {!! Form::select('account_type',$dropdownData[$cleanArray[$list]['fieldName']],$valueToShow
                                                                ,array('class'=>'form-control '.$hideFromUser.' account_type input_field '.$cleanArray[$list]['fieldFunction'], 'id'=>'input_'.$cleanArray[$list]['id'].'_'.$list,' data-func'=>$cleanArray[$list]['fieldFunction'],  'placeholder'=>$cleanArray[$list]['placeholder'] ,$disabled)) !!}
                                                            </div>
                                                            
                                                            @if($dropkey == 'CUST_TITLE_CODE' && isset($dropdownData['CUST_TITLE_CODE_EXTRA']))
                                                                <script> var _title = JSON.parse('<?php echo json_encode($dropdownData['CUST_TITLE_CODE_EXTRA']); ?>'); </script>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                 
                                                @else
                                               
                                                    <input type="text" class="{{$hideFromUser}} validation_{{$cleanArray[$list]['id']}} input_field form-control {{$cleanArray[$list]['fieldClass']}}" value="{{$valueToShow}}" id="input_{{$cleanArray[$list]['id']}}_{{$list}}"  data-func="{{$cleanArray[$list]['fieldFunction']}}"   autocomplete="off" 
                                                     {{$disabled}}  placeholder="{{$cleanArray[$list]['placeholder']}}"  name="get_data" style="height:32px;background-color:{{$background}};visibility:{{$cleanArray[$list]['display']}}">
                                                @endif
                                        </div>
                                            @else
                                        <div class="col-md-4 {{$mx_auto}}">
                                        </div>
                                        <div class="col-md-3 {{$mx_auto}}">
                                            <center>
                                                <div class="switch-blck" id="amend_switch_{{$cleanArray[$list]['id']}}_{{$list}}">
                                                    <div class="toggleWrapper">

                                                    <input type="checkbox" name="amend_toggle_{{$cleanArray[$list]['id']}}_{{$list}}" class="mobileToggle toggle_field" id="amend_toggle_{{$cleanArray[$list]['id']}}_{{$list}}" data-func="{{$cleanArray[$list]['fieldFunction']}}" style="visibility:{{$cleanArray[$list]['display']}}">
                                                            <label for="amend_toggle_{{$cleanArray[$list]['id']}}_{{$list}}" style="visibility:hidden;"></label>
                                                    </div>
                                                </div>

                                            </center>                                                
                                        </div>
                                            @endif


                                        <div class="col-md-1 {{$mx_auto}}">
                                        @if($cleanArray[$list]['saveBtn'] == 'N')
                                       
                                            <a class="btn btn-sm btn-outline-primary mb-3" style="visibility:hidden;">Save</a>
                                        @else
                                            <a href="javascript:void(0)" class="save_field btn btn-sm btn-outline-primary mb-3 save_per-{{$cleanArray[$list]['id']}} {{$disabled}}" 
                                            id="save_{{$cleanArray[$list]['id']}}_{{$list}}" mandatory="{{$cleanArray[$list]['required']}}" 
                                            style="display:{{$cleanArray[$list]['btnShow']}};">Save</a>
                                            
                                            <a href="javascript:void(0)" class="edit_field btn btn-sm btn-outline-primary mb-3 edit_per-{{$cleanArray[$list]['id']}}"
                                             id="edit_{{$cleanArray[$list]['id']}}_{{$list}}" style="display:none">Edit</a>
                                        @endif

                                        </div>
                                    </div>
                                    @php
                                        $getEvdList  = explode(',',$cleanArray[$list]['evidence_id']);
                                        if($scenario != 'ekyc' && ($descToshow == 'Kyc Refresh' || in_array(2,$getEvdList))){
                                          $showOvdDrop = 'Y';
                                        }
                                        
                                        if($scenario != 'ekyc'){
                                            $showUploadDoc = 'Y';
                                        }else{
                                            if(!in_array($cleanArray[$list]['id'],[18,4,15,6,5,10])){
                                                $showUploadDoc = 'Y';
                                            }else{
                                               
                                                unset($evidenceUniqueList['2']);
                                                unset($evidenceUniqueList['3']);
                                            }
                                        }
                                        
                                        if($cleanArray[$list]['id'] == 11){
                                            $showOvdDrop1 = 'Y';
                                        }
                                        
                                    @endphp
                                @endfor
                                @if($showOvdDrop == 'Y')
                                        @include('amend.amendekyc')
                                    @endif

                                @if($showOvdDrop1 == 'Y')
                                        @include('amend.amendcomuaddproof')
                                @endif
                            </div>
                        </div>
                    @if($showUploadDoc == 'Y')
                        <div class="card amendImageCard" style="background-color:#F2F2F2;font-family:Franklin Gothic Book,arial, sans-serif; font-size:14px;">
                            <div class="card-block pb-0">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 style="text-align: center;">Please upload below required documents.</h4>
                                    </div>
                                </div>
                                <!-- Upload image-->
                                <label id="evidence_crf_number" hidden>{{$crf_Number}}</label>
                                   
                                    <div class="row">
                                    @php
                                        $setPdftype = "";
                                        $displayImage = "";
                                        $pdfName = "";
                                    @endphp
                                       @foreach($evidenceUniqueList as $key => $value)
                                      
                                            @if($value['other'] == 'N')
                                                @include('amend.amendimageupload')
                                            @endif
                                        @endforeach
                                    
                                    </div>

                                    <div class="row"> 
                                        <div class="col-md-12">   
                                            <button type="button" id="otherDocuments" class="btn btn-primary otherDocDisplay">Other Documents</button>
                                        </div>
                                    </div>
                                    <div class="row otherDocumentsDiv" style="display:none">
                                        <div class="col-md-12">
                                            <center><h4>Please upload below required other documents.</h4></center>
                                        </div>
                                    </div>
                                    <div class="row otherDocumentsDiv" style="display:none">
                                   
                                        @foreach($evidenceUniqueList as $key => $value)
                                            @if($value['other'] == 'Y')
                                                @include('amend.amendimageupload')
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <!-- end Image -->
                                <!-- Modal large-->
                                <div class="modal fade custom-popup" id="upload_amend" tabindex="-1" role="dialog">
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
                                                <input type="file" class="" id="inputImage" name="file" accept="image/* ,application/pdf">
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
                                                                    <button class="amend_image_crop btn btn-green"> crop </button>
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
                                                <div class="col-md-12 text-center mt-3" id="savePdfDoc">
                                                   <center> <button type="button" id="uploadImageAmend" class="btn btn-lblue saveDocument" disabled>Save document</button></center>
                                                </div>
                                            </div>              
                                        </div>
                                    </div>
                                </div> 
                             @endif
                            <!-- end modal -->
                                <!-- final submit button -->
                                <div class="row pb-4">
                                    <div class="col-md-12">
                                        @if($scenario == 'manual')
                                            <center> <a href="javascript:void(0)" class="btn btn-primary disabled" id="saveAmendData">Save and Generate CRF</a></center>
                                        @else
                                            <center> <a href="javascript:void(0)" class="btn btn-primary" id="saveAmendData">Save and Generate CRF</a></center>

                                        @endif

                                    </div>
                                </div>
                                <!-- end button -->
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="pubki" value="{{config('pupvki')['pub']}}">
</div>
@endsection
@push('scripts')
<script>

    _html = JSON.parse('<?php echo json_encode($html); ?>');

    _validation = JSON.parse('<?php echo json_encode($validation); ?>');

    _encPubStrB = ['-2e', '-2e', '-2e', '-2e', '-2e', '-43', '-46', '-48', '-4a', '-4f', '-21', '-51', '-56', '-43', '-4d', '-4a', '-44', '-21', '-4c', '-46', '-5a', '-2e', '-2e', '-2e', '-2e', '-2e'];
    _encPubStrE = ['-2e', '-2e', '-2e', '-2e', '-2e', '-46', '-4f', '-45', '-21', '-51', '-56', '-43', '-4d', '-4a', '-44', '-21', '-4c', '-46', '-5a', '-2e', '-2e', '-2e', '-2e', '-2e'];

 
  for(var value in _validation){

        switch(_validation[value].class){
            case 'noFutureDate':
                
                $('.'+_validation[value].class).attr({onkeypress:'return /[0-9]/i.test(event.key)',
                                                                maxlength:_validation[value].maxlength});
            break;
            case 'noPastDate':
                
                $('.'+_validation[value].class).attr({onkeypress:'return /[0-9]/i.test(event.key)',
                                                                maxlength:_validation[value].maxlength});
            break;
            case 'string':

                $('.'+_validation[value].class).attr({onkeypress:'return /[a-zA-Z]/i.test(event.key)',
                                                                maxlength:_validation[value].maxlength});
            break;

            case 'spacestring':

                $('.'+_validation[value].class).attr({onkeypress:'return /[ a-zA-Z]/i.test(event.key)',
                                                                maxlength:_validation[value].maxlength});
            break;

            case 'specialcase':
                $('.'+_validation[value].class).attr({onkeypress:'return /[a-zA-Z0-9.a-zA-z0-9+@+a-zA-Z0-9+a-zA-Z]/i.test(event.key)',maxlength:_validation[value].maxlength});
            break;

            case 'numeric':

                $('.'+_validation[value].class).attr({onkeypress:'return /[0-9]/i.test(event.key)',maxlength:_validation[value].maxlength});

            break;

            case 'anumeric':

                $('.'+_validation[value].class).attr({onkeypress:'return /[0-9]/i.test(event.key)',maxlength:_validation[value].maxlength});

            break;

            case 'alphanumeric':

                $('.'+_validation[value].class).attr({onkeypress:'return /[a-z0-9 (/),@.#&-\\]/gi.test(event.key)',maxlength:_validation[value].maxlength});

            break;

             case 'aplhaword':

                $('.'+_validation[value].class).attr({onkeypress:'return /[a-zA-Z]/i.test(event.key)',maxlength:_validation[value].maxlength});

            break;
            case 'pincode':

                $('.'+_validation[value].class).attr({onkeypress:'return /[0-9]/i.test(event.key)',maxlength:_validation[value].maxlength});

            break;

            case 'strnumbercombo':
                $('.'+_validation[value].class).attr({onkeypress:'return /[a-zA-Z0-9]/i.test(event.key)',maxlength:_validation[value].maxlength});
            break;

            case 'pan':
                $('.'+_validation[value].class).attr({onkeyup:'return this.value = this.value.toUpperCase();',maxlength:_validation[value].maxlength});
            break;

        }
  }    

    if(_html == 'hide'){
        $('.fieldName').hide();
    }

    function showPreviewModal(elementSrc){
         if (window.event.ctrlKey) {
                document.getElementById("imagePreviewModal").style.display = 'block';
                $('#imagePreviewSrc').attr('src', $(elementSrc.srcElement).attr('src'));
                $('#imagePreviewSrc').attr('width','100%');
            }
    }
    
    $('.pan').inputmask("aaaa-a-9999-a",{ 
            "placeholder": "xxxx-x-0000-x",
            autoUnmask: true,
    });

    $('.noFutureDate').datepicker({
        clearBtn: true,
        format: "dd-mm-yyyy",
        endDate: "today",
        maxDate: "today",            
    }).on('change', function () {
        var curr = $(this);
        var idSequence = 1;
        if(curr[0].id != null){
            idSequence = curr[0].id.split('-')[1];
        }                        
        var dob_date_string = moment(this.value, "DD-MM-YYYY").format("YYYY-MM-DD");
    });     

    $('.osv_amned_done_check').attr('disabled',true);

    $('.gaurdian').css('display','none');

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
<script src="{{ asset('custom/js/amend_field.js') }}"></script>
<script src="{{ asset('custom/js/ovd_details.js') }}"></script>
<script  src="{{ asset('components/jsrsa/jsrsasign-all-min.js') }}"></script>
@endpush