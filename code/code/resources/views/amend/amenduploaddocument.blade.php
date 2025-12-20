@extends('layouts.app')
@php
$page = 3;
$amendItemData  = '';
$amendCustData = '';
$amendAccData = '';
$amendEvidence = '';
if(count($getCustomerDetailsArray)>0){

    $amendItemData = $getCustomerDetailsArray[0];
    $amendCustData = $getCustomerDetailsArray[1];
    $amendAccData = $getCustomerDetailsArray[2];
    $amendEvidence = $getCustomerDetailsArray[3];
    $customerId = $getCustomerDetailsArray[4];
}
 //echo "<pre>";print_r($getCustomerDetailsArray);exit;

if($html == 'hide'){
    $mx_auto = 'mx-auto';
}else{
    $mx_auto = '';
}

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
</style>
@section('content')
<div class="dnone-ryt">
    <div class="pcoded-content1">
        <div class="pcoded-inner-content1">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
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
                                    <div class="col-md-2 {{$mx_auto}}">
                                        <b>Existing Information</b>
                                    </div>
                                     <div class="col-md-3 {{$mx_auto}}">
                                       <b>Update Information</b>
                                    </div>
                                    <div class="col-md-1 {{$mx_auto}}">
                                        <center><b>Select</b></center>
                                    </div>
                                </div><hr>

                                @foreach($amendItemData as $key => $amendItem)

                                    <div class="row amendRow" id="{{$amendItem['id']}}">
                                     {{-- select change  --}}  

                                        <div class="col-md-2 {{$mx_auto}}">
                                        @if($amendItem['type2'] == 'ACM')
                                            @for($j=0;$j<count($amendAccData[$amendItem['id']]);$j++)

                                                @if($j == 0)
                                                    <p style="font-size: 12px;" class="description_field" id="description_{{$amendItem['id']}}_{{$j}}">
                                                        {{$amendItem['description']}}
                                                    </p>


                                                @else
                                                    <p style="font-size: 12px;display: none;" class="description_field" id="description_{{$amendItem['id']}}_{{$j}}">
                                                        {{$amendItem['description']}}
                                                    </p>

                                                @endif
                                                
                                            @endfor
                                        @else
                                            @for($i=0;$i<count($amendCustData[$amendItem['id']]);$i++)

                                                @if($i == 0)
                                                    <p style="font-size: 12px;" class="description_field" id="description_{{$amendItem['id']}}_{{$i}}">
                                                        {{$amendItem['description']}}
                                                    </p>
                                                @else
                                                    <p style="font-size: 12px;display: none;" class="description_field" id="description_{{$amendItem['id']}}_{{$i}}">
                                                        {{$amendItem['description']}}
                                                    </p>
                                                @endif

                                            @endfor
                                        @endif
                                        {{--  --}}
                                         @if($amendItem['type2'] == 'ACM')
                                                   
                                                <div class="col-md-2">
                                            @foreach($amendAccData[$amendItem['id']] as $key => $accountSeq)
                                          
                                                    <div class="row customCheckAccNo" style="margin-bottom: 20px;">      
                                                        <input type="checkbox" class="custom-control-input customChecks" name="type" id="customCheck_{{$amendItem['id']}}_{{$key}}" style="vertical-align: text-top;" checked><span class="account_No"style="margin-left:20px;vertical-align: text-top;">{{$accountSeq['accountNo']}}</span>
                                                    </div>
                                            @endforeach
                                                </div>
                                        @else
                                                <div class="col-md-2 {{$mx_auto}}">
                                            @foreach($amendCustData[$amendItem['id']] as $key => $amendCust)

                                                    <span class="account_No"style="margin-left:20px;vertical-align: text-top;display: none;"></span>
                                            @endforeach
                                                </div>
                                        @endif
                                        {{--  --}}
                                        </div>
                                        {{-- end selected change --}}

                                        {{-- field name --}}
                                        <div class="col-md-2 fieldName">
                                            @if($amendItem['type2'] == 'ACM')
                                                @foreach($amendAccData[$amendItem['id']] as $key => $amendAcc)
                                                   
                                                    <div class="row finacleField rowAlign">
                                                        <span class="finacleField_value" id="finacleField_{{$key}}-{{$amendItem['id']}}" style="font-size:12px">{{$amendAcc['finacleField']}}</span>
                                                   
                                                    </div> 
                                                @endforeach
                                            @else
                                                @foreach($amendCustData[$amendItem['id']] as $key => $amendCust)
                                                
                                                    <div class="row finacleField" style="height:50px;margin-right:20px;">
                                                       <span class="finacleField_value" id="finacleField_{{$key}}-{{$amendItem['id']}}" style="font-size:12px">{{$amendCust['finacleField']}}</span>
                                                    </div>
                                                
                                                @endforeach
                                            @endif
                                        </div>
                                     {{-- end field name --}}
                                     {{-- old value --}}
                                        <div class="col-md-2 {{$mx_auto}}">
                                            @if($amendItem['type2'] == 'ACM')

                                                @foreach($amendAccData[$amendItem['id']] as $key => $amendAcc)

                                                    <div class="row oldValue rowAlign">
                                                        <span class="old_value" id="olddata_{{$key}}-{{$amendItem['id']}}">{{$amendAcc['oldValue']}}</span>
                                                    </div>

                                                @endforeach
                                            @else
                                                @foreach($amendCustData[$amendItem['id']] as $key => $amendCust)

                                                    @if($amendItem['id'] == '6')
                                                         <div class="row oldValue" style="height:50px;font-size: 12px;margin-left:1px;">
                                                            <span class="old_value" id="olddata_{{$key}}-{{$amendItem['id']}}">
                                                                {{date('d-m-Y',strtotime(substr($amendCust['oldValue'],0,10)))}}
                                                           </span>
                                                        </div> 
                                                    @else
                                                        <div class="row oldValue" style="height:50px;font-size: 12px;margin-left:1px;">
                                                            <span class="old_value" id="olddata_{{$key}}-{{$amendItem['id']}}">{{$amendCust['oldValue']}}</span>
                                                        </div> 
                                                    @endif

                                                @endforeach
                                            @endif
                                        </div>
                                        {{-- end old value --}}
                                        {{-- insert new values and toggle --}}
                                        <div class="col-md-3 {{$mx_auto}}">
                                            @if($amendItem['type2'] == 'ACM')
                                                @foreach($amendAccData[$amendItem['id']] as $key => $amendAcc)

                                                    @if(strtolower($amendItem['type1']) == 'amendment')
                                                        @if((in_array($amendItem['id'],[24])))
                                                                <select class="input_field form-control" id="dropdown_{{$amendItem['id']}}">

                                                                </select>
                                                            @else
                                                                <div class="row d-flex mx-auto newValue rowacmAlign">
                                                                    <input type="text" class="input_field form-control validation_{{$amendItem['id']}}" id="input_{{$amendItem['id']}}_{{$key}}" name="get_data" placeholder="{{$amendItem['placeholder']}}" style="height:36px;" autocomplete="off">
                                                                </div>
                                                        @endif
                                                    @else
                                                    <div class="row d-flex mx-auto newValue" style="height:50px;margin-top: 5px;">
                                                    <center>
                                                        <div class="switch-blck">
                                                            <div class="toggleWrapper">
                                                                <input type="checkbox" name="amend_toggle_{{$amendItem['id']}}_{{$key}}" class="mobileToggle toggle_field" id="amend_toggle_{{$amendItem['id']}}_{{$key}}">
                                                                <label for="amend_toggle_{{$amendItem['id']}}_{{$key}}"></label>
                                                            </div>
                                                        </div>
                                                    </center>
                                                    </div>
                                                    @endif

                                                @endforeach
                                            @else
                                                @foreach($amendCustData[$amendItem['id']] as $key => $amendCust)
                                                    
                                                    @if(strtolower($amendItem['type1']) == 'amendment')
                                                    

                                                        @if((in_array($amendItem['id'], [5,6,10,15])) && (count($eKYCDetails)>0))
                                                             <div class="row d-flex mx-auto newValue" style="height:50px">
                                                            <input type="text" class="input_field form-control" id="input_{{$amendItem['id']}}_{{$key}}" name="get_data" placeholder="{{$amendItem['placeholder']}}" value="{{$eKYCDetails[$amendCust['finacleField']]}}" style="height:36px;" disabled>
                                                            </div>
                                                        @else
                                                            @if((in_array($amendItem['id'],[14,15,17])))
                                                                
                                                                    <div class="newValue">
                                                                        {!! Form::select('account_type',$dropdownData[$amendItem['id']],'',array('class'=>'form-control account_type input_field', 'id'=>'input_'.$amendItem['id'].'_'.$key , 'placeholder'=>'Select')) !!}
                                                                    </div>
                                                                
                                                            @else
                                                                <div class="row d-flex mx-auto newValue" style="height:50px">
                                                                    <input type="text" class="input_field form-control validation_{{$amendItem['id']}}" id="input_{{$amendItem['id']}}_{{$key}}" name="get_data" placeholder="{{$amendItem['placeholder']}}" value="" style="height:36px;" autocomplete="off">
                                                                </div>
                                                            @endif
                                                        @endif

                                                     
                                                    @else
                                                        <div class="row d-flex mx-auto newValue" style="height:50px">
                                                        <center>
                                                            <div class="switch-blck">
                                                                <div class="toggleWrapper">
                                                                    <input type="checkbox" name="amend_toggle_{{$amendItem['id']}}_{{$key}}" class="mobileToggle toggle_field" id="amend_toggle_{{$amendItem['id']}}_{{$key}}">
                                                                    <label for="amend_toggle_{{$amendItem['id']}}_{{$key}}"></label>
                                                                </div>
                                                            </div>
                                                        </center>
                                                        </div>
                                                    @endif

                                                @endforeach
                                            @endif
                                        </div>
                                        {{-- insert new values and toggle --}}
                                        {{-- save button logic --}}
                                        <div class="col-md-1 {{$mx_auto}}">
                                            @if($amendItem['type2'] == 'ACM')
                                                @foreach($amendAccData[$amendItem['id']] as $key => $amendAcc)

                                                    <div class="row d-flex mx-auto save_button rowacmAlign">
                                                        <div class="col-md-2">
                                                          <a href="javascript:void(0)" class="save_field btn btn-sm btn-outline-primary mb-3" id="save_{{$amendItem['id']}}_{{$key}}" >Save</a>
                                                          <a href="javascript:void(0)" class="edit_field btn btn-sm btn-outline-primary mb-3" id="edit_{{$amendItem['id']}}_{{$key}}" style="display:none">Edit</a>
                                                        </div>
                                                    </div>

                                                @endforeach  
                                            @else
                                                @foreach($amendCustData[$amendItem['id']] as $key => $amendCust)
                                                    @if((in_array($amendItem['id'], [5,6,10,15])) && (count($eKYCDetails)>0))
                                                    @else
                                                    <div class="row d-flex mx-auto save_button" style="height:50px">
                                                        <div class="col-md-2">
                                                          <a href="javascript:void(0)" class="save_field btn btn-sm btn-outline-primary mb-3" id="save_{{$amendItem['id']}}_{{$key}}" >Save</a>
                                                          <a href="javascript:void(0)" class="edit_field btn btn-sm btn-outline-primary mb-3" id="edit_{{$amendItem['id']}}_{{$key}}" style="display:none">Edit</a>
                                                        </div>
                                                    </div>
                                                    @endif

                                                @endforeach
                                            @endif
                                        </div>
                                        {{-- end save button logic --}}

                                    </div>
                                <hr>
                                @endforeach
                            </div>
                        @if($amendItemData[0]['id'] == 18)   
                            <div class="card-block">
                                @include('amend.amendekyc')
                            </div>
                        @endif
                        </div>
                        <div class="card" style="background-color:#F2F2F2;font-family:Franklin Gothic Book,arial, sans-serif; font-size:14px;">
                            <div class="card-block pb-0">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 style="text-align: center;">Please upload below required documents.</h4>
                                    </div>
                                </div>
                                <!-- Upload image-->
                                <div class="row">
                               @php
                                   $evidenceTotalList =  array();
                                   foreach($amendEvidence as $array){
                                    $evidenceTotalList = array_merge($evidenceTotalList,$array);
                                   }
                                   $evidenceTotalList = collect($evidenceTotalList)->unique();
                               @endphp
                               @foreach($evidenceTotalList as $key => $evidenceList)
                                <div class="col-md-3">
                                    <div class="form-group" id="amend_card_proof-{{$key}}">
                                            <div class="detaisl-left align-content-center mt-1 w-100">
                                                <label for="amend_card-{{$key}}"><li>{{$evidenceList['evidence']}}</li></label>
                                            </div>
                                            @if(false)
                                            <div class="add-document d-flex align-items-center justify-content-around do-not-crop" id="amend_card-{{$key}}">
                                                <div id="note_div">
                                                    <img class="uploaded_image imagetoenlarge" name="amend_image" id="document_preview_note" src="">
                                                </div>
                                            </div>  
                                            @else
                                            <div class="add-document d-flex align-items-center justify-content-around do-not-crop" id="amend_card-{{$key}}">
                                                <div class="add-document-btn adb-btn-inn">
                                                    <button type="button" id="upload_amend_card" class="btn btn-outline-grey waves-effect upload_document_amend" data-toggle="modal" 
                                                    data-id="amend_card-{{$key}}" data-name="amend_image-{{$key}}"  data-document="Image" data-target="#upload_amend">
                                                        <span class="adb-icon">
                                                            <i class="fa fa-plus-circle"></i>
                                                        </span>
                                                        <span>{{substr($evidenceList['evidence'],0,17)}}</span>
                                                    </button>
                                                </div>
                                            </div>  
                                            {{-- <button onclick="submitAmendUpdate('image',{{$key}})" class="btn mt-2">Submit</button> --}}
                                             @endif                           
                                        <input type="text" style="opacity:0" name="amendImage" id="amendImage-{{$key}}">
                                    </div>
                                </div>
                               @endforeach
                                </div>
                                <!-- end Image -->
                                <!-- Modal large-->
                                <div class="modal fade custom-popup" id="upload_amend" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-xl" role="document">
                                        <div class="modal-content">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <div class="modal-body">
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
                                                <div class="col-md-12 text-center mt-3">
                                                    <button type="button" id="uploadImageAmend" class="btn btn-lblue saveDocument" disabled>Save document</button>
                                                </div>
                                            </div>              
                                        </div>
                                    </div>
                                </div> 
                            <!-- end modal -->
                            <!-- final submit button -->
                            <div class="row pb-4">
                                <div class="col-md-12">
                                   <center> <a href="javascript:void(0)" class="btn btn-primary" id="saveAmendData">Save and Generate CRF</a></center>
                                </div>
                            </div>
                            <!-- end button -->
                            <!-- login moadal-->
                                {{-- <div class="modal fade" id="crfRequestForm" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">User Authentication</h4>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="br_submit_loader display-none-br-submit-loader">
                                                  <div class="br_submit_loader__element"></div>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>User Name</label>
                                                    <input type="text" id="submission_user_name" class="form-control" value="{{ ucfirst( Session::get('username'))}}" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label>Password</label>
                                                    <input type="password" id="submission_user_password" class="form-control" id="password" name="password" value="" autocomplete="false" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                                                <button type="button" class="btn btn-primary waves-effect waves-light submit_to_npc" id="">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}
                            <!-- endlogin moadl -->
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
<script>

    _html = JSON.parse('<?php echo json_encode($html); ?>');
    _validation = JSON.parse('<?php echo json_encode($validation); ?>');


   for(let i=0;i<_validation.length;i++){
        
        switch(_validation[i].class){
            case 'date':
                
                $('.validation_'+_validation[i].id).attr({onkeydown:'return /[0-9]/i.test(event.key)',
                                                                maxlength:_validation[i].maxlength})
            break;
            case 'string':

                $('.validation_'+_validation[i].id).attr({onkeydown:'return /[a-zA-Z]/i.test(event.key)',
                                                                maxlength:_validation[i].maxlength})
            break;

            case 'emailcase':
                $('.validation_'+_validation[i].id).attr({onkeydown:'return /[a-zA-Z0-9.a-zA-z0-9+@+a-zA-Z0-9+a-zA-Z]/i.test(event.key)',maxlength:_validation[i].maxlength})
            break;

            case 'mobilecase':

                $('.validation_'+_validation[i].id).attr({onkeydown:'return /[6-9]/i.test(event.key)',maxlength:_validation[i].maxlength})

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
   
    $('.validation_6').datepicker({
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



</script>
@endpush