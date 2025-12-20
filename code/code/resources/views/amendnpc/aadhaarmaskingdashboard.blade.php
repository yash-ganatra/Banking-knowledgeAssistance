@extends('layouts.app')

@php

if(!empty($aadhaarMaskingDetails)){    
  
    $crf_number = isset($aadhaarMaskingDetails['crf_number'])&& $aadhaarMaskingDetails['crf_number']!=''? $aadhaarMaskingDetails['crf_number']:'';
    $name=isset($custName['customer_name'])&& $custName['customer_name']!=''?$custName['customer_name']:'';
   
    $documentType=isset($documentType['evidence'])&& $documentType['evidence'] != '' ? $documentType['evidence']:'';   
    $mask_image = base64_encode($image_name); 
    $identity=isset($aadhaarMaskingDetails['evidence_id'])&& $aadhaarMaskingDetails['evidence_id']!=''?$aadhaarMaskingDetails['evidence_id']:'';
    $proofidentity=isset($custName['additional_data'])&& $custName['additional_data'] !='' ? $custName['additional_data']:'';
    $proofidentity=json_decode($proofidentity,true);
    $id_proof='';
    $maskCount = $aadhaarMaskingDetails['mask_count'];
    if(isset($proofidentity['proofIdData']['proof_id']) && $proofidentity['proofIdData']['proof_id'] != ''){      

       $id_proof= $proofidentity['proofIdData']['proof_id'];
        
    }   



    $display = "";
   
}

if($imagePresent != "" && $image_name != ""){
    $disabled = 'disabled';
}else{
    $disabled = '';
}

@endphp

@section('content')

 <body>
    <input type="hidden" value="{{$side}}" id="side">
    <input type="hidden" value="{{$multiside}}" id="multiside">
    <input type="hidden" value="{{$doc_id}}" id="doc_id">    

        @if(!empty($aadhaarMaskingDetails))
          
            <div id='image_append'>
                    <input type="hidden" id="amenddoc_id" value="{{ $aadhaarMaskingDetails['id']}}">
                    <input type="hidden" id="image" value="{{URL::to('/showamendimage/'.$mask_image)}}" data-imagePresent = '{{$imagePresent}}'>
            </div>
           
            <!-- <div class="row filter mb-4 mt-5  d-flex justify-content-center">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="crf_number" name="crf_number" placeholder="CRF Number">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary" id="crfSearch">Search</button>
                </div>
            </div> -->

            <div class="row d-flex justify-content-center mb-5">                               
                <div class="col-md-6">
                    <div style="width: auto; padding-top: 10px;">
                        <b style="font-size: 18px;">CRF Number: {{$crf_number}}</b> <br>
                        <b style="font-size: 18px;">Name: {{$name}}</b> <br>
                        <b style="font-size: 18px;">Document Type: {{$documentType}}</b> <br>                                                                           
                    </div>
                </div>
            </div> 

            <div class="row">
                <div class="col-md-12">
                <div class="card" style=" margin-top: 11px; margin-right: 17px; overflow: auto;">
                <div class="card-body mx-5 wrapper">
                    <div class="row mx-auto">
                        <div class="col-md-7" style="margin-left: 10px;">
                            <div style="width: auto">
                                @if($imagePresent == false)
                                @php
                                    $display = "display-none";
                                @endphp
                                <div col-md-12 style="padding-top: 10%; padding-left: 20%; font-weight: bolder; font-size: 30px;">Image Not Present</div>
                                @endif
                               
                                <canvas id="canvas" width="500" height="500" class="{{$display}}"></canvas>
                          
                            </div>
                        </div>

                    </div>
                    <div class="col-md-12">
                        <center>
                            <button type="button" class="btn btn-primary mt-3 mb-3" id="reduceImage" style="width: 15%; height: 10% " data-value="reduceImage" onclick="reduceCanvas20pc()" >Reduce Image</button>
                            <button type="button" class="btn btn-primary mt-3 mb-3 submit {{$display}}" id="saveImage" style="width: 15%; height: 10% " data-value="saveImage">Save</button>
                            <button type="button" class="btn btn-primary mt-3 mb-3 submit " id="submitImage" data-value="submitImage" style="width: 15%; height: 10%">Submit</button>
                        </center>
                    </div>
                    <div class="col-md-12 my-5">
                        <!-- <center><button type="submit" class="btn btn-secondary" id="aadhaarNumberSkip" {{$disabled}}>Skip</button></center>  -->
                </div>
            </div>
            </div>
            </div>
            </div>

        <!-- @else

            <div class="row">
                <div class="col-md-12">
                    <div class="card" style="margin-left: -515px; margin-top: 10%; margin-right: 17px;">
                        <center>
                            <div style="font-weight: bold;">No More Images.</div>
                            <a type="button" class="btn btn-danger mt-3 mb-3" id="returnToDashboard" style="width: 20%; height: 10%" href="{{ URL::route('amendnpcdashboard') }}">Return To Dashboard</a>
                        </center>
                    </div>
                </div>
            </div>
        @endif -->
    </div>





</body>     

@endsection

@push('scripts')

@if(!empty($aadhaarMaskingDetails))
<script type="text/javascript">
        $('canvas').css('border', '2px solid blue');
        var canvas = document.getElementById('canvas');
        var ctx = canvas.getContext('2d');
        var rect = {};
        var drag = false;
        var imageObj = new Image();
        var imgDrow, w, h;
        var mx, my;
        var masked = false;
        var flag = false;
    

function getMousePos(canvas, evt) {
    var rect = canvas.getBoundingClientRect();
    return {
        x: (evt.clientX - rect.left) / (rect.right - rect.left) * canvas.width,
        y: (evt.clientY - rect.top) / (rect.bottom - rect.top) * canvas.height
    };
}

    function init() {  
        imageObj.src = $('#image').val();
        imageObj.onload = function () {        
        canvas.width = imageObj.width;
        canvas.height = imageObj.height;
        ctx.drawImage(imageObj, 0, 0, canvas.width, canvas.height);
        console.log(imageObj.src.substr(imageObj.src.lastIndexOf('/') + 1));
    };
        
        canvas.addEventListener('mousedown', mouseDown, false);
        canvas.addEventListener('mouseup', mouseUp, false);
        canvas.addEventListener('mousemove', mouseMove, false);
    }


    function mouseDown(e) {
        rect.startX = e.pageX - canvas.offsetLeft;
        rect.startY = e.pageY - canvas.offsetTop;
        drag = true;
        mx = e.offsetX;
        my = e.offsetY;               
    }

    function mouseUp() 
    { drag = false; }

    function mouseMove(e) {
        if (drag) {
            var getmousepos = getMousePos(canvas, e);            
            ctx.filter = 'blur(25px)';           
            ctx.drawImage(imageObj, 0, 0, imageObj.width,imageObj.height);
            rect.w = (e.pageX - canvas.offsetLeft) - rect.startX;
            rect.h = (e.pageY - canvas.offsetTop) - rect.startY;           
            if(e.offsetX-mx > 0 && e.offsetY-my > 0)
            {
                ctx.filter = 'blur(100px)';                
                imgDrow=ctx.getImageData(mx, my, e.offsetX-mx, e.offsetY-my); //mouseX and MouseY use today   
                
            }
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.filter = 'none';
            ctx.drawImage(imageObj, 0, 0, imageObj.width,imageObj.height);
            w=rect.w<0?rect.startX+rect.w:rect.startX;
            h=rect.h<0?rect.startY+rect.h:rect.startY;
            if(imgDrow)
            {
                ctx.putImageData(imgDrow,mx, my);
            }
            masked = true;
        }
    }

    $(document).ready(function(){
        init();
    });



    $('.submit').click(function(){
       
        var maskUpdateObject = [];
        maskUpdateObject.data = {};
        maskUpdateObject.data['updatedCanvasImage'] = '';
        imagePresent = $('#image').attr('data-imagePresent');       
        if(rect.w >= 1 && rect.h >= 1){
            var updatedCanvasImage = canvas.toDataURL(("{{URL::to('/showamendimage/'.$mask_image)}}")); 
            maskUpdateObject.data['updatedCanvasImage'] = updatedCanvasImage;
        }
        else{
            if("{{$identity}}" == 5 && "{{$maskCount}}" == '')
            {
                $.growl({message: "Mask has not been applied to image"},{type: "warning"});
                return false;
            }

            if("{{$identity}}" == 2 || "{{$identity}}" == 3){
         
                if("{{$id_proof}}" == 1 && "{{$maskCount}}" == ''){
                    $.growl({message: "Mask has not been applied to image"},{type: "warning"});
                    return false;
                }
            }
        }

        if($('#image').attr('data-imagePresent') != ''){
            var ovdid = $('#amenddoc_id').val();           
            maskUpdateObject.data['id'] = ovdid;
            maskUpdateObject.data['is_back_image'] = $('#back_image').val();           
            maskUpdateObject.data['originalImage'] = "{{$mask_image}}";
            maskUpdateObject.data['imageName'] = "{{$mask_image}}";
            maskUpdateObject.data['button'] = $(this).attr('id');
            maskUpdateObject.data['imageField'] = "{{$imageField}}";          
            maskUpdateObject.data['crf_number'] = "{{$crf_number}}";    
            maskUpdateObject.data['document_id'] = $('#submitImage').attr('data-document_id');
            maskUpdateObject.url = '/amendnpc/aadhaarmaskingsave';
            maskUpdateObject.data['functionName'] = 'updateAadhaarMaskingAppCallBack';

            crudAjaxCall(maskUpdateObject);
        }
        return false;
    });

    $("body").on("click","#crfSearch",function(){
        		
		if($("#crf_number").val() == ""){
            $.growl({message: "Please Enter AOF Number"},{type: "warning"});
            return false;
        }

        if($("#crf_number").val().length < 12 || $("#crf_number").val().length > 12 ){
            $.growl({message: "Please Enter Valid AOF Number"},{type: "warning"});
            return false;
        }

        aofNumber = $('#crf_number').val();
    
        aadhaarMaskAOF(aofNumber,'/amendnpc/aadhaarmasking');
        return false;
    });

    $("body").on("click", "#aadhaarNumberSkip",function(){
        var aadhaarNumSkip = [];
        aadhaarNumSkip.data = {};

        if($('#image').attr('data-imagePresent') != ''){
            
            var ovdid = $('#amenddoc_id').val();           
            aadhaarNumSkip.data['id'] = ovdid;          
            aadhaarNumSkip.data['image_name'] = "{{$imageName}}";
            aadhaarNumSkip.data['imagePresent'] = $('#image').attr('data-imagePresent');
            aadhaarNumSkip.data['imageField'] = "{{$imageField}}";
            aadhaarNumSkip.data['crf_number'] = "{{$crf_number}}";        
            aadhaarNumSkip.data['side'] = $('#side').val();
            aadhaarNumSkip.data['multiside'] = $('#multiside').val();
            aadhaarNumSkip.data['doc_id'] = $('#doc_id').val();
            aadhaarNumSkip.url = '/amendnpc/aadhaarmaskingsave';
            aadhaarNumSkip.data['functionName'] = 'updateAadhaarMaskingAppCallBack';
            crudAjaxCall(aadhaarNumSkip);
            
        }

        return false;

    });


    function aadhaarMaskAOF(aofNumber,url)
    {
        var baseUrl = $('meta[name="base_url"]').attr('content');
        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        var csrf_field = '<input type="hidden" name="_token" value="'+csrf+'">';
        var encodedParams =  $.base64.encode(aofNumber);
        var key = $('meta[name="cookie"]').attr('content').split('.')[2];
        var encryptedData = encrypt(encodedParams,key);
        var form = $('<form action="' + baseUrl + url + '" method="post">' +
                        '<input type="text" name="encodedString" value="' + encryptedData + '" />' + csrf_field +
                    '</form>');
        $('body').append(form);
        form.submit();
        return false;
    }

    
    function updateAadhaarMaskingCallBack(response, object){
        // if(response['status'] == "success"){
        //     $.growl({message: response['msg']},{type: response['status']});
        //         window.location.reload();              
        //     }
        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
            if(response['data']!=''){               
            var params=response['data'];               
            redirectaadhaarurl(params, '/amendnpc/all_amend_aadharmasking');
            }
            else{
           window.location.reload();
    }
        }
    }


    function reduceCanvas20pc(){

            var originalCanvas = document.getElementById("canvas");
            var originalContext = originalCanvas.getContext('2d');
            originalCanvas.id = 'old_canvas';
            var newCanvas = document.createElement('canvas');
            newCanvas.id = 'canvas';
            newCanvas.width = originalCanvas.width * 0.8;  // New width
            newCanvas.height = originalCanvas.height * 0.8; // New height
            var newContext = newCanvas.getContext('2d');


            newContext.drawImage(
              originalCanvas,  0,  0,  originalCanvas.width,  originalCanvas.height,  0,  0,  newCanvas.width,  newCanvas.height
            );

            var parentElement = originalCanvas.parentNode;
            parentElement.insertBefore(newCanvas, originalCanvas);
            originalContext.clearRect(0, 0, originalCanvas.width, originalCanvas.height);
            parentElement.removeChild(originalCanvas);

            newCanvas.addEventListener('mousedown', mouseDown, false);
            newCanvas.addEventListener('mouseup', mouseUp, false);
            newCanvas.addEventListener('mousemove', mouseMove, false);
            
            ctx = newCanvas.getContext('2d');
            rect = {};
            drag = false;
            imageObj.src = newCanvas.toDataURL('image/png');

            masked = false;
            flag = false;
            canvas = document.getElementById('canvas');           
    }

</script>

@endif

@endpush
    