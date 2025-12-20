@php

    $mandatorySign = $value['mandatory'] == 'Y' ? '*' : '';
    $imageExits = true;
    if(isset($value['storageExist']) && $value['storageExist'] == 'Y'){
        $imageExits = true;
    }else{
        $imageExits = false;
    }

    if($key == 21 || $key == 22){
        $setPdftype = 'pdf';
    }else{
        $setPdftype = 'image';
    }
    $checkPdfExt = '';
    
    //echo "<pre>";print_r($getEvidenceData);exit;
    if(isset($getEvidenceData) && count($getEvidenceData)>0){
       // foreach ($getEvidenceData as $evedId => $imageName) {
            
            $imageName = isset($getEvidenceData[$key]) && $getEvidenceData[$key] != ''? $getEvidenceData[$key] :'';

           // if($evedId == $key){
                if($imageName != ''){
                    $extension =  explode('.',$getEvidenceData[$key]);
                    $checkPdfExt = $extension;
                    $pdfName = $imageName;
                        $displayImage = URL::to('/imagestemp/'.$imageName);
    
                        if(!File::exists($displayImage)){
                            $storage_path =  base64_encode($getCurrentyear.'/'.$crfId.'/'.$customerReqId.'/'.$imageName);
                           
                            $displayImage = URL::to('/showamendimage/'.$storage_path);
                        }

                }else{
                    $imageExits = false;                                             
                }
            //}
        //} 
    }else{
        $imageExits = false;
    }
@endphp

<div class="col-md-3 imageEvidenceData">
    <div class="form-group" id="amend_card_proof-{{$key}}">
            <div class="detaisl-left align-content-center mt-1 w-100">
                <label style="color:red" id="red-{{$key}}">{{$mandatorySign}}</label>
                <label class="imageLabel" id="image_label_{{$key}}" for="amend_card-{{$key}}">{{$value['evidence']}}</label>
            </div>
            
            @if($imageExits)
            
                @if($checkPdfExt[1] == 'pdf')
                <div class="add-document d-flex align-items-center justify-content-around" id="amend_card-{{$key}}" data-doc={{$setPdftype}}>
                        <div id="amend_card_div-{{$key}}">
                            <div class="upload-delete">
                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light  deleteamendImage" id="delete_image_crf-{{$key}}"><i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                            <center> <p style="margin-top:70px;" id="subosvImg-{{$key}}"><i class="fa fa-file-pdf-o" 
                                    style="font-size:48px;color:red"></i><a class="subOsvImage uploaded_image amend_image" href="{{$displayImage}}" name="amend_image-{{$key}}" id="document_preview_amend_card_pdf-{{$key}}" target="_blank" style="vertical-align: 10px;">
                                {{$pdfName}}    
                                </a>
                                    </p>
                            </center>
                        </div>
                </div>
                @else
                    <div class="add-document d-flex align-items-center justify-content-around" id="amend_card-{{$key}}" data-doc={{$setPdftype}}>
                        <div id="amend_card_div-{{$key}}">
                            <div class="upload-delete">
                                <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light  deleteamendImage" id="delete_image_crf-{{$key}}"><i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                            <img class="imagetoenlarge uploaded_image amend_image" name="amend_image-{{$key}}" id="document_preview_amend_card-{{$key}}" src="{{$displayImage}}" data-mandatory="{{$value['mandatory']}}">
                        </div>
                        <div class="add-document-btn adb-btn-inn display-none amend_card_div-{{$key}}">
                            <button type="button" id="upload_amend_card-{{$key}}" class="btn btn-outline-grey waves-effect upload_document_amend" data-toggle="modal" 
                            data-id="amend_card-{{$key}}" data-name="amend_image-{{$key}}"  data-document="Image" data-target="#upload_amend" 
                            mandatory="{{$value['mandatory']}}" data-doc={{$setPdftype}}>
                                <span class="adb-icon">
                                    <i class="fa fa-plus-circle"></i>
                                </span>
                                <span>{{substr($value['evidence'],0,17)}}</span>
                            </button>
                        </div>                                                                                                
                    </div>
                @endif
            
            @else
           
            <div class="add-document d-flex align-items-center justify-content-around" id="amend_card-{{$key}}" data-doc={{$setPdftype}}>
                <div class="add-document-btn adb-btn-inn amend_card_div-{{$key}}">
                    <button type="button" id="upload_amend_card-{{$key}}" class="btn btn-outline-grey waves-effect upload_document_amend" data-toggle="modal" 
                    data-id="amend_card-{{$key}}" data-name="amend_image-{{$key}}"  data-document="Image" data-target="#upload_amend"
                     mandatory="{{$value['mandatory']}}"  data-doc={{$setPdftype}}>
                        <span class="adb-icon">
                            <i class="fa fa-plus-circle"></i>
                        </span>
                        <span>{{substr($value['evidence'],0,17)}}</span>
                    </button>
                </div>
            </div>
            @endif  
            {{-- <button onclick="submitAmendUpdate('image',{{$key}})" class="btn mt-2">Submit</button> --}}
             {{-- @endif                            --}}
        <input type="text" style="opacity:0" name="amendImage" id="amendImage-{{$key}}">
        @php
            
            if(isset($value['storageExist']) && $value['storageExist'] == 'Y'){

                if(isset($ovd_check[$key]) && $ovd_check[$key] == 'Y'){
                    $checked = 'checked';
                }else{
                     $checked = '';
                }

            }else{
                $checked = '';
            }
                
        @endphp
        @if(!(in_array($key,[4,39])))
        <div class="osv-done-blck osv_amend_image_check_{{$key}}">
            <label class="radio">
                <input type="checkbox" class="osv_amned_done_check" name="key[{{$key}}]" id="amend_image_check_-{{$key}}"  
                {{$checked}}  data-mandatory="{{$value['mandatory']}}" data-image-id = "document_preview_amend_card-{{$key}}" >
                <span class="lbl padding-8">Confirm Original Seen and Verified</span>
            </label>      
        </div>
        @endif
    </div>
</div>