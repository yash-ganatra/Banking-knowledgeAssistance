@extends('layouts.app')
@php
use App\Helpers\CommonFunctions;
$masking_fields = ["Registered Email Id","Registered Mobile Number (RMN)","Aadhaar Number","PAN Number Updation","voter_id_number","Alternate Contact Number","passport_number","aadhaar_photocopy_number","driving_licence_number","pan_number","Passport","Voter ID","Driving Licence"];
$tokenParams = Cookie::get('token');
$encrypt_key = substr($tokenParams, -5);
$page = 4;
$crf_number = '';
$created_at = '';
$disabled = '';
$file_Paths = $getCurrentyear.'/'.$crfId.'/'.$custReqFormId;

if(count($getAmendData)>0){
    $crf_number = $getAmendData[0]->crf;
    $created_at = date('d-m-Y',strtotime(substr($getAmendData[0]->created_at,0,10)));
    $disabled = 'disabled';
}

$displaySelected = '';
// 24 ~ Approved CRF

if($getAmendMasterData['crf_status'] <= 24 && $getAmendMasterData['approval'] != 'offline'){
    $showSubmit = true;
}else{
    $showSubmit = false;
}

if($getAmendMasterData['approval'] == 'offline' && $getAmendMasterData['crf_status'] == 22){
    $showSubmit = true;
}

if($getAmendMasterData['crf_status'] == 23 && $getAmendMasterData['approval'] == 'offline'){
    $showPrint = true;
}else{
    $showPrint = false;
}

$approvalType = 'online';
if($voltNoMatch == ''){
    $approvalType = 'offline';
}else{
    
    $approvalType = $getAmendMasterData['approval'];
}

if(isset($breadcrumhide) && $breadcrumhide == 'Y'){

    $breadcrumshow = 'N';
}else{
    $breadcrumshow=  'Y';
}
@endphp
<style type="text/css">  
.step-4::before{
    display: none;
}
.step-4::after{
    content: '4';
}
.row-line-height{
    line-height: 40px;
}
.ptagPadding{
    margin: 0px;
    padding: 0px;
    text-align: right;
}
.ptagLeftPadding{
    margin: 0px;
    padding: 0px;
    text-align: left;
}
th ,td{
    white-space: wrap;
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
                        <div class="breadcrumBranch">
                            <div class="process-wrap active-step1" style="margin-left:340px">
                            @include('amend.amendbreadcrum',['page'=>$page,'breadcrumshow'=>$breadcrumshow])
                            </div>
                        </div>
                    <div class="page-body">
                        <div class="card">
                            <div class="card-block">
                                <div class="container" style="background-color:#F2F2F2;font-family:Franklin Gothic Book,arial, sans-serif; font-size:14px;">
                                    <div class="row">
                                        <div class="card-block" style="width: 100%;">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <img class="img-fluid" src="{{ asset('assets/images/dcb-logo.svg') }}" alt="DCB Logo" />
                                                </div>
                                                <div class="col-md-4">
                                                    <h4 style="text-align:center;">Customer Request Form</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row crfInfo">
                                        <table border="2">
                                            <tr>
                                                <td style="width:350px;">
                                                  {{-- <span>{!!DNS2D::getBarcodeHTML($crf_number,'QRCODE')!!}</span> --}}
                                                        <img src="data:image/png;base64,{{DNS2D::getBarcodePNG($crf_number,"QRCODE")}}" alt="barcode">

                                                </td>
                                                <td style="width:500px;">
                                                    <table style="line-height:2px;">
                                                        <tr>
                                                            <td>
                                                               <p class="ptagPadding" style="line-height:0.2px"> Request Number :</p>

                                                            </td>
                                                            <td>
                                                               <p  class="ptagLeftPadding" id="crf_number" style="line-height:0.2px">{{$crf_number}}</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                               <p class="ptagPadding" style="line-height:0.2px"> Customer Name :</p>
                                                            </td>
                                                            <td>
                                                               <p class="ptagLeftPadding" style="line-height:0.2px">
                                                                {{-- @php
                                                                    $wordCtn = strlen($getCustomerName);
                                                                    $eqlWord = $wordCtn/2;  
                                                                @endphp
                                                                    @if(strlen($getCustomerName) < 50)
                                                                        {{$getCustomerName}}
                                                                    @else
                                                                        <p>
                                                                        {{substr($getCustomerName,0,$eqlWord)}}<br>
                                                                        {{substr($getCustomerName,$eqlWord,$wordCtn)}}
                                                                        </p>
                                                                    @endif --}}
                                                                    {{$getCustomerName}}
                                                               </p>
                                                               
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <p class="ptagPadding" style="line-height:0.2px">Customer ID :</p>
                                                            </td>
                                                            <td>
                                                              <p class="ptagLeftPadding" style="line-height:0.2px">{{$getAmendMasterData['customer_id']}}</p>
                                                            </td>
                                                        </tr>

                                                        @if($getAmendMasterData['account_no'] != '')
                                                            <tr>
                                                                <td>
                                                                    <p class="ptagPadding" style="line-height:0.2px">Account Number :</p>
                                                                </td>
                                                                <td>
                                                                    <p class="ptagLeftPadding" style="line-height:0.2px">{{$getAmendMasterData['account_no']}}</p>
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        @if($kycNumber != '')
                                                        <tr>
                                                            <td>
                                                                <p class="ptagPadding" style="line-height:0.2px">E-KYC Number :</p>
                                                            </td>
                                                            <td>
                                                                <p class="ptagLeftPadding" style="line-height:0.2px">{{$kycNumber}}</p>
                                                            </td>
                                                        </tr>
                                                        @endif

                                                        <tr>
                                                            <td>
                                                               <p class="ptagPadding" style="line-height:0.2px">Branch Official :</p>
                                                            </td>
                                                            <td>
                                                            <?php 
                                                                 $getBranchOfficalName['emp_name'] = isset($getBranchOfficalName['emp_name']) && $getBranchOfficalName['emp_name'] != ''?$getBranchOfficalName['emp_name']:'';
                                                                ?>
                                                            <p class="ptagPadding text-start" style="line-height:0.2px">{{strtoupper($getBranchOfficalName['emp_name'])}}</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <p class="ptagPadding" style="line-height:0.2px">Date :</p>
                                                            </td>
                                                            <td>
                                                              <p class="ptagLeftPadding" style="line-height:0.2px">{{$created_at}}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="width:350px;">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <br>
                                    <h4>Amend Selected Information :</h4><br>
                                    <table>
                                        <tr>
                                            <td>
                                                <b style="margin-left:40px">Selected Change</b>
                                            </td>
                                            <td style="width: 450px;">
                                                <b style="margin-left:15%;">Existing Information</b>
                                            </td>
                                            <td style="width: 450px;">
                                                <b style="text-align:center;color: blue;margin-left: 15%;">New Information</b>
                                            </td>
                                        </tr>
                                    </table><br>
                                  {{--   <table>
                                        @for($i=0;$i<count($getAmendData);$i++)

                                            @php
                                                if($getAmendData[$i]->amend_item != $displaySelected){

                                                    $displaySelected = $getAmendData[$i]->amend_item;

                                                    $showSelected =  $getAmendData[$i]->amend_item;

                                                }else{

                                                    $showSelected = '';

                                                }

                                            @endphp

                                            <tr>
                                                @if((strlen($getAmendData[$i]->new_value_display) < 30 ) && (strlen($getAmendData[$i]->old_value) < 30))

                                                        <td>
                                                            <span style="font-size:12px;padding: 40px;">{{$showSelected}} </span><br> <span style="font-size:12px;padding: 40px;"> {{$getAmendData[$i]->account_no}}</span>  
                                                        </td>
                                                    <td  style="width: 450px;">
                                                        <input type="text" class="input_field form-control" {{$disabled}} value="{{strtoupper($getAmendData[$i]->old_value)}}" style="height:30px;background-color:white;">
                                                    </td>
                                                    <td  style="width: 450px;">
                                                        <input type="text" class="input_field form-control" {{$disabled}} value="{{strtoupper($getAmendData[$i]->new_value_display)}}" style="height:30px;background-color:white;border-color: blue;">
                                                    </td>
                                                @else
                                                    <tr>
                                                        <td>
                                                            <span style="font-size:12px;">{{$showSelected}}  {{$getAmendData[$i]->account_no}}</span>  
                                                        </td>

                                                        <td colspan="2">
                                                            <input type="text" class="input_field form-control" {{$disabled}} value="{{strtoupper($getAmendData[$i]->old_value)}}" style="height:30px;background-color:white">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <span style="font-size:12px;visibility: hidden;">{{$showSelected}}  {{$getAmendData[$i]->account_no}}</span>  

                                                        </td>
                                                        <td colspan="2">
                                                            
                                                            <input type="text" class="input_field form-control" {{$disabled}} value="{{strtoupper($getAmendData[$i]->new_value_display)}}" style="height:30px;background-color:white;border-color: blue;">
                                                        </td>
                                                          
                                                    </tr>
                                                @endif
                                            </tr>
                                        @endfor
                                    </table> --}}
                                    <table>
                                        @for($i=0;$i<count($getAmendData);$i++)
                                            @php
                                                if($getAmendData[$i]->amend_item != $displaySelected){

                                                    $displaySelected = $getAmendData[$i]->amend_item;
                                                    $showSelected =  $getAmendData[$i]->amend_item;

                                                }else{

                                                    $showSelected = '';
                                                }
                                            @endphp
                                        <tbody>
                                            <tr>
                                                <td style="width:15%;">
                                                    <span style="font-size:12px;">{{$showSelected}}  {{$getAmendData[$i]->account_no}}</span>  
                                                </td>
                                                <td style="width:30%;">
                                                @if(in_array($getAmendData[$i]->amend_item,$masking_fields) && $getAmendData[$i]->old_value != "")
                                                    <span class="oldpadding" id="oldpadding-{{$i}}" style="background:white;padding:0.4em 1.1em 0.2em 1.1em;width:26em; height:2.1em;color:black; word-wrap: break-all;display: block;white-space: normal;">
                                                    <label class="enc_label unmaskingfield" style="display:none;">
                                                        {{CommonFunctions::encrypt256($getAmendData[$i]->old_value,$encrypt_key)}}
                                                    </label>
                                                    <label class="maskingfield">
                                                        **************
                                                    </label>
                                                    </span>
                                                @else
                                                    <span class="oldpadding" id="oldpadding-{{$i}}" style="background:white;padding:0.4em 1.1em 0.2em 1.1em;width:26em; height:2.1em;color:black; word-wrap: break-all;display: block;white-space: normal;">
                                                        {{strtoupper($getAmendData[$i]->old_value)}}
                                                    </span>
                                                @endif
                                                </td>
                                                <td style="width:30%;">
                                                    <span class="newpadding" id='newpadding-{{$i}}' style="background:white;padding:0.4em 1.1em 0.2em 1.1em;width:26em; height:2.1em;  color:#0070C0; word-wrap: break-word;display: block;white-space: normal;">
                                                        @if($getAmendData[$i]->field_name == '_AADHAR_NUMBER')
                                                            {{'XXXX-XXXX-'.substr($getAmendData[$i]->new_value_display,8)}}
                                                        @elseif(in_array($getAmendData[$i]->amend_item,$masking_fields) && $getAmendData[$i]->new_value_display != "")
                                                        <label class="enc_label unmaskingfield" style="display:none;">
                                                        {{CommonFunctions::encrypt256($getAmendData[$i]->new_value_display,$encrypt_key)}}
                                                        </label>
                                                        <label class="maskingfield">
                                                            **************
                                                        </label>
                                                        @else
                                                            {{strtoupper($getAmendData[$i]->new_value_display)}}
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                        @endfor
                                    </table>
                          
                                @if((isset($additionalData['proofIdData']['proof_id']) && $additionalData['proofIdData']['proof_id'] != '') || (isset($additionalData['comuproofAddData']['addproof_id']) && $additionalData['comuproofAddData']['addproof_id'] != ''))
                                    <h5>Additional details :</h5> 
                                @endif
                                
                                @if($additionalData != '')
                                    <br>
                                        <table>
                                               @foreach($additionalData as $key => $value)
                                            <tr>
                                                @if(is_array($value))
                                                    @php $prev_ele = ""; @endphp
                                                    @foreach($value as $id => $data)
                                               
                                                        @if($data != '')
                                                        <td>
                                                                @if($id=="id_code")
                                                                    @if(in_array($prev_ele,$masking_fields) && $data !="")
                                                                        <input type="text" class="input_field form-control enc_input unmaskingfield" {{$disabled}} value="{{CommonFunctions::encrypt256($data,$encrypt_key)}}" style="height:30px;width:200px;background-color:white;color: #364fcc; display:none;">
                                                                        <input type="text" class="form-control maskingfield" {{$disabled}} value="***********" style="height:30px;width:200px;background-color:white;color: #364fcc;">
                                                                    @else
                                                               <input type="text" class="input_field form-control" {{$disabled}} value="{{strtoupper($data)}}" style="height:30px;width:200px;background-color:white;color: #364fcc;">
                                                                    @endif
                                                                @else
                                                                    <input type="text" class="input_field form-control" {{$disabled}} value="{{strtoupper($data)}}" style="height:30px;width:200px;background-color:white;color: #364fcc;">
                                                                @endif
                                                        </td>
                                                            @php $prev_ele = $data; @endphp
                                                    @endif
                                                @endforeach
                                                @endif
                                            </tr>
                                                @endforeach
                                        </table>
                                    <br>
                                @endif
                                @if(count($getImageData)>0)
                                        <h5>CRF proof document list :</h5><br>
                                @endif
                                
                                    @if(count($getImageData)>0)
                                        
                                    <div class="row">
                                        
                                        @foreach($getImageData as $eviID => $data)

                                            @php
                                              
                                                $checkExtension = explode('.',$data['imageName']);
                                              
                                                $osvtag = 'OSV_DONE_';
                                            @endphp
                                                <div class="col-6 pb-4">
                                                    <label>{{$data['evidenceName']}}</label>
                                                   <div class="p-2 border">
                                                        @if($imagecacheCheck == 'Y')
                                                            @if($data['osv_check'] == 'Y')
                                                                @php
                                                                    $file_Path = base64_encode($file_Paths.'/'.$osvtag.$data['imageName']);
                                                                @endphp
                                                                <div class="uploaded-img-ovd" style='filter:blur(30px);'>
                                                                <img  class="subOsvImage" id="subosvImg-{{$eviID}}" src="{{URL::to('/showamendimage/'.$file_Path)}}" style="height:240px;width:100%;border: 2px;" />
                                                                </div>
                                                            @else
                                                                @php
                                                                    $file_Path = base64_encode($file_Paths.'/'.$data['imageName']);
                                                                    
                                                                @endphp
                                                                @if(isset($checkExtension[1]) &&  $checkExtension[1]== 'pdf')
                                                                    @php
                                                                        $pdfName = base64_encode($file_Paths.'/'.$data['imageName']);
                                                                    @endphp
                                                                    <center> <p style="margin-top:70px;" id="subosvImg-{{$eviID}}"><i class="fa fa-file-pdf-o" 
                                                                            style="font-size:48px;color:red"></i><a class="subOsvImage" href="{{URL::to('/showamendimage/'.$pdfName)}}" id="pdf_image-{{$eviID}}" target="_blank" style="vertical-align: 10px;">
                                                                            {{$data['imageName']}}</a>
                                                                            </p>
                                                                    </center>
                                                                @else
                                                                <div class="uploaded-img-ovd" style='filter:blur(30px);'>
                                                                <img  class="subOsvImage" id="subosvImg-{{$eviID}}" src="{{URL::to('/showamendimage/'.$file_Path)}}" style="height:240px;width:100%;border: 2px;" />
                                                                </div>
                                                                @endif    
                                                            @endif
                                                        @else
                                                            @php
                                                                $file_Path = base64_encode($data['imageName']);
                                                            @endphp
                                                            @if(isset($checkExtension[1]) &&  $checkExtension[1] == 'pdf')
                                                           
                                                            <center> <p style="margin-top:70px;" id="subosvImg-{{$eviID}}"><i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i><a class="subOsvImage" href="{{URL::to('/showamendimage/'.$file_Path)}}" 
                                                                        target="_blank" id="pdf_image-{{$eviID}}" style="vertical-align: 10px;">{{explode('/',$data['imageName'])[3]}}</a>
                                                                    </p>
                                                            </center>
                                                            @else
                                                            <div class="uploaded-img-ovd" style='filter:blur(30px);'>
                                                                <img  class="subOsvImage" id="subosvImg-{{$eviID}}" src="{{URL::to('/showamendimage/'.$file_Path)}}" style="height:240px;width:100%;border: 2px;" />
                                                             </div>   
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            
                                        @endforeach
                                    </div>
                                    @endif
                                </div><br>
                            <div class="row">
                                    @if($showSubmit)
                                        <div class="col-md-12">
                                             <center><button type="button" id="onlineAmendCrf" class="btn btn-primary submitCrf">Review and Submit</button></center>
                                        </div>
                                    @endif
                                    <input type='hidden' id='approvalType' value={{$approvalType}}>

                            </div><br>
                            @if($showPrint)
                           
                            <div class="row pb-2 upload_crf_div"  style="padding-left:220px" >
                                <input type="text" class="form-control" id ="crf_document_div" value="crfDocument" hidden>
                                {{-- <input type="text" class="form-control" id ="crfIdPath" value="{{$file_Path}}" hidden> --}}
                                <div class="col-md-3">
                                    <div class="form-group" id="amend_print_card">
                                        <div class="detaisl-left align-content-center mt-1 w-100">
                                   
                                        </div>
                                        <div class="add-document d-flex align-items-center justify-content-around" id="amend_print_card">

                                            <center><span><a href="{{asset('/bank/printrequestform')}}" class="btn btn-primary" name="printamendForm" id="printamendForm">Print</a></span></center>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 uploadcrfImage">
                                    <div class="form-group" id="amend_card_proof-1">
                                            <div class="detaisl-left align-content-center mt-1 w-100">
                                       
                                            </div>
                                            @if(false)
                                            <div class="add-document d-flex align-items-center justify-content-around" id="amend_card-1">
                                                <div id="note_div">
                                                    <img class="uploaded_image imagetoenlarge" name="amend_image" id="document_preview_note" src="">
                                                </div>
                                            </div>  
                                            @else
                                
                                            <div class="add-document d-flex align-items-center justify-content-around" id="amend_card-1"  data-doc="pdf|image">
                                                <div class="add-document-btn adb-btn-inn">
                                                    
                                                    <button type="button" id="upload_amend_card-1" class="btn btn-outline-grey waves-effect upload_document_amend" data-toggle="modal" 
                                                    data-id="amend_card-1" data-name="crf_image-1"  data-document="Image / PDF" data-target="#upload_amend_crf" data-doc="pdf|image">
                                                        <span class="adb-icon">
                                                            <i class="fa fa-plus-circle"></i>
                                                        </span>
                                                        <span>Upload CRF</span>
                                                    </button>
                                                </div>
                                            </div> 
                                            @endif 
                                        <input type="text" style="opacity:0" name="amendCrfImage" id="amendCrfImage-1">
                                        <div class="sing-done-blck sing_amend_image_check_-1">
                                            <label class="radio">
                                                <input type="checkbox" class="sing_amned_done_check" name="key[1]" id="amend_image_check_-1"  
                                                  data-mandatory="Y" data-image-id = "document_preview_amend_card-1" data-doc="pdf|image">
                                                <span class="lbl padding-8">Confirm Signature Verified</span>
                                            </label>      
                                        </div>
                                    </div>
                                </div>

                                 <div class="col-md-3">
                                    <div class="form-group" id="amend_save_crf_card">
                                        <div class="detaisl-left align-content-center mt-1 w-100">
                                   
                                        </div>
                                        <div class="add-document d-flex align-items-center justify-content-around" id="amend_save_crf_card">

                                            {{-- <center><span><a href="javascript:void(0)" class="btn btn-primary" name="save_crf_form" id="save_crf_form">Submit</a></span></center> --}}
                                            <center><span><a href="javascript:void(0)" class="btn btn-primary" name="save_crf_form" id="save_crf_form">Submit</a></span></center>

                                        </div>
                                    </div>
                                </div>

                                <!-- end Image -->
                                <!-- Modal large-->
                                <div class="modal fade custom-popup" id="upload_amend_crf" tabindex="-1" role="dialog">
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
                                                <input type="file" class="" id="inputImage" name="file" accept="image/*,.pdf" data-doc="pdf|image">
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
                                                 <div class="col-md-12 text-center mt-3" id="pdfDocButton">
                                                   {{--  <center><button type="button" id="uploadPdfAmend" class="btn btn-lblue savePdfDocument" style="display:none;">Save document</button></center> --}}
                                                </div>
                                                <div class="col-md-12 text-center mt-3">
                                                    <center><button type="button" id="uploadImageAmend" class="btn btn-lblue saveDocument" disabled>Save document</button></center>
                                                </div>
                                            </div>              
                                        </div>
                                    </div>
                                </div> 
                            </div>
                            <hr>
                        </div>
                    </div>
                    @endif
                        <div class="modal fade" id="Username_passowrd-blck" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">User Authentication</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                            <!-- <span aria-hidden="true">&times;</span> -->
                                        </button>
                                    </div>
                                     <div class="br_submit_loader display-none-br-submit-loader" style="display: none;">
                                          <div class="br_submit_loader__element"></div>
                                        </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>User Name</label>
                                            <input type="text" id="submission_user_name" class="form-control" value="{{ ucfirst( Session::get('username'))}}" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" id="submission_user_password" class="form-control"  name="password" value="" autocomplete="false" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default waves-effect" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary waves-effect waves-light submitamenddetailNpc">Submit</button>
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
@endsection
@push('scripts')
	<script src="{{ asset('custom/js/util.js') }}"></script>
    <script>
        $('#amend_image_check_-1').attr('disabled','disabled');

        $(document).ready(function() {
            
            var dynamicPaddingNew = document.getElementsByClassName('newpadding');
            var dynamicPaddingOld = document.getElementsByClassName('oldpadding');

            for(var i=0;dynamicPaddingNew.length>i;i++){

                var getnewText = dynamicPaddingNew[i].innerText;
                var getoldText = dynamicPaddingOld[i].innerText;

                if(getnewText.length > 50){
                    $('#newpadding-'+i).css('height','3.5em');
                }else{
                    $('#newpadding-'+i).css('height','2.1em');
                }
                if(getoldText.length > 50){
                    $('#oldpadding-'+i).css('height','3.5em');
                }else{
                    $('#oldpadding-'+i).css('height','2.1em');
                }
            }
        });

    </script>
@endpush