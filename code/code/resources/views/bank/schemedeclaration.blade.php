@php
$declaration = (array) current($declaration);
            
$blade_applicant = $declaration['blade_id'].'-'.$declaration['applicant'];
$blade_applicant_review = $declaration['blade_id'].'_proof-'.$declaration['applicant'];
if($is_review == 1 && $declaration['blade_id']=="third_party_approval" || $declaration['blade_id']=="aof_back_img"|| $declaration['blade_id']=="pep_approval"){   
        $disabled = '';
}
if($redo){
    if(isset($userDetails['Declarations'][$blade_applicant])){
        if(isset($userDetails['Declarations'][$blade_applicant.'_proof'])){
            $imageName = $userDetails['Declarations'][$blade_applicant.'_proof'];
            $schemeDeclarationDisplay = 1;
        }else{
            $imageName = "";
            $schemeDeclarationDisplay = 0;
        }
    }else{
            $imageName = "";
            $schemeDeclarationDisplay = 0;
    }
}else{
    if(isset($declaration['blade_id']) && isset($userDetails['Declarations'][$blade_applicant.'_proof'])){
        $imageName = $userDetails['Declarations'][$blade_applicant.'_proof'];
        $schemeDeclarationDisplay = 1;
    }else{
        $imageName = "";
        $schemeDeclarationDisplay = 0;
    }
}
if($imageName == '' && $enable != 'display-none'){
    $otherDisplay = 'display-none';
}else{
    $otherDisplay = '';
}

if($schemeDeclarationDisplay == 0){
    $display = "display-none";
}else{
    $display = "";
}

if($declaration['blade_id'] == 'acknowledgement_receipt' || $declaration['blade_id'] == 'delight_kit_photograph'){
    $col_6 = 'col-sm-6';
    $display_none = 'display-none';
    $display = "";
}else{
    $col_6 = '';
    $display_none = '';
}

$image_mask_blur = "";
$def_blur_image = "";
if($is_review==1){
    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
}

$is_huf = false;

 if($accountDetails['constitution'] == 'NON_IND_HUF'){
    $is_huf_display = true;
    $is_huf = true;
 }else{
    $is_huf_display = false;
 }
@endphp

@if($declaration['declaration'] != 'OTHER')
    <div class="maindoc-row editColumnDiv {{$col_6}}">
@else
    <div class="maindoc-row editColumnDiv other-declaration-div {{$otherDisplay}} " name="{{$blade_applicant}}">
@endif
    <div class="row">
        <div class="col-md-12" id="declaration_{{$declaration['id']}}">
            <div class="detaisl-left d-flex align-content-center">
                {{-- huf declaration name typo its work for normal/huf both --}}
                    @php
                        $declarationType = $declaration['declaration'];
                        $applicantType = $declaration['applicant'];
                    @endphp
                    
                    @if($declarationType != 'OTHER')
                    @if($declaration['type'] == 'SCHEME')
                            <p class="lable-cus"><b>{{$declarationType}}</b></p>
                    @else
                            @if($is_huf)
                                @if($applicantType == 1)
                                    <p class="lable-cus"><b>KARTA/MANAGER {{$declarationType}}</b></p>
                                @elseif($applicantType == 2)
                                    <p class="lable-cus"><b>HUF {{$declarationType}}</b></p>
                    @endif
                @else
                                <p class="lable-cus"><b>APPLICANT {{$applicantType}} {{$declarationType}}</b></p>
                @endif
                        @endif
                    @else
                        <p class="lable-cus"><b>{{$declarationType}} DECLARATION</b></p>
                    @endif
                    
                {{-- end --}}
                <span class="{{$enable}}">
                    @if(isset($reviewDetails[$blade_applicant_review]))
                        <i class="fa fa-times"></i>
                        {{$reviewDetails[$blade_applicant_review]}}
                        <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                    @else
                        <i class="fa fa-check"></i>
                    @endif
                </span>
            </div>
            <div class="questions-blck-row {{$display_none}}">
                <div class="radio-selection">
                    @if($declaration['declaration'] != 'OTHER')
                        <label class="radio">
                            <input type="checkbox" class="declaration" name="{{$blade_applicant}}" id="{{$blade_applicant}}" @if($is_review == 1) {{ empty($imagePath) || empty($imageName) ? '' : 'disabled' }}  @endif {{ ($schemeDeclarationDisplay=="1")? "checked" : "" }} {{$disabled}}>
                            <span class="lbl padding-8">{{$declaration['description']}}</span><br>
                            <span class="lbl padding-8">{{App\Helpers\CommonFunctions::getdynatextfordeclaration($declaration['blade_id'],$declaration['data'])}}</span>
                        </label>
                    @else
                        <label class="radio display-none">
                             <input type="checkbox" class="declaration" name="{{$blade_applicant}}" id="{{$blade_applicant}}" {{ ($schemeDeclarationDisplay=="1")? "checked" : "" }} {{$disabled}}>
                                 <span class="lbl padding-8">{{$declaration['description']}}</span><br>
                            <span class="lbl padding-8">{{App\Helpers\CommonFunctions::getdynatextfordeclaration($declaration['blade_id'],$declaration['data'])}}</span>
                        </label>
                    @endif
                </div>
            </div>
        </div>
        
            
            <div class="col-md-12 ">
                @if($declaration['declaration'] != 'OTHER')
                    <div class="upload-doc-mdr {{$display}}" id="{{$blade_applicant}}_image_proof_div">
                @else
                    <div class="upload-doc-mdr" id="{{$blade_applicant}}_image_proof_div">
                @endif
                <div class="form-group">
                    <div class="add-document d-flex align-items-center justify-content-around" id="{{$blade_applicant}}_proof">
                        @if(isset($imageName) && ($imageName != ''))
                            <div id="{{$blade_applicant}}_declaration">
                                @if(substr(strtolower($imageName),-3) != 'pdf')
                                    @php
                                    $class  = '';
                                    $data_id  = '';
                                    @endphp
                                @else
                                @php
                                    $class = 'pdf';
                                    $data_id = $blade_applicant;@endphp
                                @endif
                                @if($enable == 'display-none')
                                    <div class="upload-delete">
                                            <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage {{$class}}" data-id={{$data_id}}>
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                @else
                                    @if(isset($reviewDetails[$blade_applicant_review]))
                                        <div class="upload-delete">
                                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage {{$class}}" data-id={{$data_id}}>
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                            </button>
                                            </div>       
                                    @endif
                                @endif

                                 @if(substr(strtolower($imageName),-3) != 'pdf')
                                 <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                <img class="uploaded_image" name="{{$blade_applicant}}_proof" id="document_preview_{{$blade_applicant}}" src="{{$imagePath.'/'.$imageName}}" onerror="resetUiForImageNotFound('{{$blade_applicant}}_declaration')">
                                 </div>
                                @else
                                    <i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i>
                                    <a id = {{$blade_applicant}}_proof_pdf href="{{$imagePath.'/'.$imageName}}" target="_blank" style="font-size:14px;text-decoration:none;margin-top:14px;">{{$imageName}}</a>
                                @endif
                            </div>
                            <div class="add-document-btn adb-btn-inn display-none">
                        @else
                            <div class="add-document-btn adb-btn-inn">
                        @endif
                        @php 
                            $imageorpdf = 'image';
                        @endphp

                        @if($blade_applicant == 'extra_declaration_pdf-1')
                            @php
                                $imageorpdf = 'pdf';
                            @endphp
                        @endif

                            <button type="button" id="upload_{{$blade_applicant}}_proof" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal"
                                data-id="{{$blade_applicant}}_proof" data-name="{{$blade_applicant}}_proof" data-document="{{$blade_applicant}}" data-target="#upload_proof" data-doc = "{{$imageorpdf}}">
                                <span class="adb-icon">
                                    <i class="fa fa-plus-circle"></i>
                                </span>
                                Add {{$declaration['declaration']}} Declaration
                            </button>
                        </div>
                        <input type="text" style="opacity:0" class="{{$imageorpdf}}-input" name="{{$blade_applicant}}_proof">
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>                
