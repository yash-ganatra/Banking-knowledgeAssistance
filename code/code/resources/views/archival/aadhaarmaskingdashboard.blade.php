@extends('layouts.app')

@php

if(!empty($aadhaarMaskingDetails)){
    
    $formId = isset($aadhaarMaskingDetails['form_id'])&& $aadhaarMaskingDetails['form_id']!=''?$aadhaarMaskingDetails['form_id']:'';
    $aof_number = isset($aadhaarMaskingDetails['aof_number'])&& $aadhaarMaskingDetails['aof_number']!=''? $aadhaarMaskingDetails['aof_number']:'';
    $name=$custName['first_name'] .' '. $custName['last_name'];
    $folder = "markedattachments";  
    $mask_image = $image_name;
    $image_type = 'Aadhaar Card';
    $applicant_sequence=isset($aadhaarMaskingDetails['applicant_sequence'])&&$aadhaarMaskingDetails['applicant_sequence']!=''?$aadhaarMaskingDetails['applicant_sequence']:'';
    $display = "";
    $identity=isset($aadhaarMaskingDetails['result2'])&& $aadhaarMaskingDetails['result2']!=''?$aadhaarMaskingDetails['result2']:'';
  
}

if($imagePresent != "" && $image_name != ""){
    $disabled = 'disabled';
}else{
    $disabled = '';
}

@endphp

@section('content')
<style>
    /* body {
      margin: 0px;
      padding: 0px;
    }
    
    #wrapper {
      position: relative;
      border: 1px solid #9C9898;
      width: 578px;
      height: 200px;
    }
    
    #buttonWrapper {
      position: absolute;
      width: 30px;
      top: 2px;
      right: 2px;
    }
    
    input[type="button"] {
      padding: 5px;
      width: 30px;
      margin: 0px 0px 2px 0px;
    } */
    #wrapperimage {
    margin: 5px;
    padding: 5px;
    background-color: #fff;
    width: 900px;
    height: 600px;
    overflow: auto;
    }
    input#plus {    
    padding-left: 10px;
    margin-right: 7px;
    padding-right: 10px;
    }
    input#minus {   
    padding-left: 11px;
    margin-right: 20px;
    padding-right: 11px;
    }



    </style>

 <body  onmousedown="return false;">

    <form action="" method="post" id="masking">
    <input type="hidden" value="{{$side}}" id="side">
    <input type="hidden" value="{{$multiside}}" id="multiside">
    <input type="hidden" value="{{$doc_id}}" id="doc_id">
    

        @if(!empty($aadhaarMaskingDetails))
          
            <div id='image_append'>
                    <input type="hidden" id="ovd_id" value="{{ $aadhaarMaskingDetails['id']}}" data-id = "{{$formId}}">
                    @if(str_contains($maskimageCount['image_type'], 'l3_update'))
                    <input type="hidden" id="image" value="{{URL::to('/imageslevelthree/'.$aadhaarMaskingDetails['form_id'].'/'.$mask_image)}}" data-imagePresent = '{{$imagePresent}}'>
                    @else
                    <input type="hidden" id="image" value="{{URL::to('/imagesmarkedattachments/'.$aadhaarMaskingDetails['form_id'].'/'.$mask_image)}}" data-imagePresent = '{{$imagePresent}}'>
                    @endif
            </div>
           
            <!-- <div class="row filter mb-4 mt-5  d-flex justify-content-center">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="aof_number" name="aof_number" placeholder="AOF Number">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary" id="aofSearch">Search</button>
                </div>
            </div> -->

            <div class="row d-flex justify-content-center mb-5">                               
                <div class="col-md-6">
                    <div style="width: auto; padding-top: 10px;">
                        <b style="font-size: 18px;">AOF Number: {{$aof_number}}</b> <br>
                        <b style="font-size: 18px;">Name: {{$name}}</b> <br>
                        <b style="font-size: 18px;">Document Type: {{$imageField}}</b> <br>
                        <b style="font-size: 18px;">Side: {{strtoupper($side)}}</b> <br>
                        <b style="font-size: 18px;">{{$image_type}}: {{$mask_number}}</b><br>   
                        <b style="font-size: 18px;">Applicant Sequence: {{$applicant_sequence}}</b>                             
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
                                <div id="wrapperimage">
                                    <canvas id="canvas" width="500" height="500" class="{{$display}}"></canvas>                                   
                               
                                </div>  
                                <div id="buttonWrapper">
                                    <input type="button" id="plus" value="+"><input type="button" id="minus" value="-">
                                </div>                        
                            </div>
                        </div>

                    </div>
                    <div class="col-md-12">
                        <center>
                            <!-- <button type="button" class="btn btn-primary mt-3 mb-3" id="reduceImage" style="width: 15%; height: 10% " data-value="reduceImage" onclick="reduceCanvas20pc()" >Reduce Image</button> -->
                            <button type="button" class="btn btn-primary mt-3 mb-3 submit {{$display}}" id="saveImage" style="width: 15%; height: 10% " data-value="saveImage">Save</button>
                            <button type="button" class="btn btn-primary mt-3 mb-3 submit" id="submitImage" data-value="submitImage" style="width: 15%; height: 10%">Submit</button>
                        </center>
                    </div>
                    <div class="col-md-12 my-5">
                        <!-- <center><button type="submit" class="btn btn-secondary" id="aadhaarNumberSkip" {{$disabled}}>Skip</button></center>  -->
                </div>
                </div>
            </div>
            </div>
            </div>
        @endif
        
    </div>
</form>
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
    // var canvas = document.getElementById('canvas');
    // var ctx = canvas.getContext('2d');
    // var rect = {};
    // var drag = false;
    // var imageObj = null;
    // var imgDrow, w, h;
    // var masked = false;

    // function getMousePos(canvas, evt) {
    //         var rectangle = canvas.getBoundingClientRect();
    //         return {
    //                 x: evt.clientX - rectangle.left,
    //                 y: evt.clientY - rectangle.top,
    //         };
    //     }

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
            
        // canvas.height = imageObj.height > 600 ? 600 : imageObj.height;
        // canvas.width = imageObj.width > 600 ? 600 : imageObj.width;
        //ctx.drawImage(imageObj, 0, 0, 800, 800);
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
        //console.dir(e.offsetX); console.dir(e.offsetY);  
        mx = e.offsetX;
        my = e.offsetY;               
    }

    function mouseUp() 
    { drag = false; }

    function mouseMove(e) {
        if (drag) {
            var getmousepos = getMousePos(canvas, e);
            canvas.height = imageObj.height > 900 ? 900 : imageObj.height;
            canvas.width = imageObj.width > 900 ? 900 : imageObj.width;
            // canvas.width = imageObj.width;
            // canvas.height = imageObj.height;
            //console.dir(getMousePos);
            ctx.filter = 'blur(25px)';
            //ctx.drawImage(imageObj, 0, 0, 800, 800);
            ctx.drawImage(imageObj, 0, 0, canvas.width,canvas.height);
            rect.w = (e.pageX - canvas.offsetLeft) - rect.startX;
            rect.h = (e.pageY - canvas.offsetTop) - rect.startY;
            //ctx.strokeStyle = 'blue';
            //imgDrow=ctx.getImageData(mx, my, e.offsetX-mx, e.offsetY-my); //mouseX and MouseY use today
            //if(rect.w>0 && rect.h>0)
            if(e.offsetX-mx > 0 && e.offsetY-my > 0)
            {
                ctx.filter = 'blur(100px)';
                //imgDrow=ctx.getImageData(rect.startX, rect.startY, rect.w, rect.h); //mouseX and MouseY use today
                imgDrow=ctx.getImageData(mx, my, e.offsetX-mx, e.offsetY-my); //mouseX and MouseY use today    
                //console.log(imgDrow + " " + rect.startX + " "+ rect.startY);
            }
            // ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.filter = 'none';
            //ctx.drawImage(imageObj, 0, 0, 800, 800);            
            ctx.drawImage(imageObj, 0, 0, canvas.width,canvas.height);
            w=rect.w<0?rect.startX+rect.w:rect.startX;
            h=rect.h<0?rect.startY+rect.h:rect.startY;
            if(imgDrow)
            {
                //ctx.putImageData(imgDrow,w, h);
                ctx.putImageData(imgDrow,mx, my);
            }
            masked = true;
        }
    }

       $('.submit').click(function(e){
            var maskbtn =$(this).attr('id');
           
           var maskUpdateObject = [];
           maskUpdateObject.data = {};
           maskUpdateObject.data['updatedCanvasImage'] = '';
           imagePresent = $('#image').attr('data-imagePresent');       
           
       
           if(rect.w >= 1 && rect.h >= 1){
                   
                if("{{$maskimageCount['image_type']}}".includes('l3_update')){
                    
                    var updatedCanvasImage = canvas.toDataURL(("{{URL::to('/imageslevelthree/'.$aadhaarMaskingDetails['form_id'].'/'.$mask_image)}}")); 
                }else{
                    var updatedCanvasImage = canvas.toDataURL(("{{URL::to('/imagesmarkedattachments/'.$aadhaarMaskingDetails['form_id'].'/'.$mask_image)}}")); 
                    
                }
                maskUpdateObject.data['updatedCanvasImage'] = updatedCanvasImage;
                   
                }else{
                    if("{{$identity}}" == 1 && "{{$maskimageCount['mask_count']}}" == '')
                    {               
                        $.growl({message: "Mask has not been applied to image"},{type: "warning"});
                        return false;
                    }
                }
        
           
            if($('#image').attr('data-imagePresent') != ''){
                var ovdid = $('#ovd_id').val();                
                var formId = $('#ovd_id').attr('data-id');
                maskUpdateObject.data['id'] = ovdid;
                maskUpdateObject.data['aof_number'] = "{{$aof_number}}";
                maskUpdateObject.data['is_back_image'] = $('#back_image').val();
                maskUpdateObject.data['form_id'] = formId;
                maskUpdateObject.data['originalImage'] = "{{$mask_image}}";
                maskUpdateObject.data['imageName'] = "{{$mask_image}}";
                maskUpdateObject.data['button'] = maskbtn;
                maskUpdateObject.data['imageField'] = "{{$imageField}}";
                maskUpdateObject.data['side'] = $('#side').val();
                maskUpdateObject.data['multiside'] = $('#multiside').val();
                maskUpdateObject.data['doc_id'] = $('#doc_id').val();
               
                // maskUpdateObject.data['updatedCanvasImage'] = imageObj.src;
                maskUpdateObject.data['applicant_sequence'] = "{{$applicant_sequence}}";
                maskUpdateObject.data['document_id'] = $('#submitImage').attr('data-document_id');
                maskUpdateObject.url = '/archival/aadhaarmaskingsave';
                maskUpdateObject.data['functionName'] = 'updateAadhaarMaskingAppCallBack';
                
                crudAjaxCall(maskUpdateObject);
                return false;
            }
         
            
    });

    $("body").on("click","#aofSearch",function(){
		
		if($("#aof_number").val() == "")
        {
            $.growl({message: "Please Enter AOF Number"},{type: "warning"});
            return false;
        }

        if($("#aof_number").val().length < 11 || $("#aof_number").val().length > 11 )
        {
            $.growl({message: "Please Enter Valid AOF Number"},{type: "warning"});
            return false;
        }

        aofNumber = $('#aof_number').val();
    
        aadhaarMaskAOF(aofNumber,'/archival/aadhaarmasking');
        return false;
    });

    $("body").on("click", "#aadhaarNumberSkip",function(){
        var aadhaarNumSkip = [];
        aadhaarNumSkip.data = {};

        if($('#image').attr('data-imagePresent') != ''){
            
            var ovdid = $('#ovd_id').val();

            var formId = $('#ovd_id').attr('data-id');
            aadhaarNumSkip.data['id'] = ovdid;
            aadhaarNumSkip.data['form_id'] = formId;
            aadhaarNumSkip.data['image_name'] = "{{$imageName}}";
            aadhaarNumSkip.data['imagePresent'] = $('#image').attr('data-imagePresent');
            aadhaarNumSkip.data['imageField'] = "{{$imageField}}";
            aadhaarNumSkip.data['applicant_sequence'] = "{{$aadhaarMaskingDetails['applicant_sequence']}}";
            aadhaarNumSkip.data['side'] = $('#side').val();
            aadhaarNumSkip.data['multiside'] = $('#multiside').val();
            aadhaarNumSkip.data['doc_id'] = $('#doc_id').val();
            aadhaarNumSkip.url = '/archival/aadhaarmaskingsave';
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

        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
            if(response['data']!=''){               
            var params=response['data'];               
            redirectaadhaarurl(params, '/archival/allimagemask');
            }
            else{
                window.location.reload();
            }
            }
}          

    // function reduceCanvas20pc(){

    //         var originalCanvas = document.getElementById("canvas");
    //         var originalContext = originalCanvas.getContext('2d');
    //         originalCanvas.id = 'old_canvas';
    //         var newCanvas = document.createElement('canvas');
    //         newCanvas.id = 'canvas';

    //     const vw = window.innerWidth;        
    //     const canvasWidthheight = 0.42 * vw;
    //     newCanvas.width = canvasWidthheight;
    //     newCanvas.height = canvasWidthheight;
    //     var newContext = newCanvas.getContext('2d');
        
    //         newContext.drawImage(
    //           originalCanvas,  0,  0,  originalCanvas.width,  originalCanvas.height,  0,  0,  newCanvas.width,  newCanvas.height
    //         );

    //         var parentElement = originalCanvas.parentNode;
    //         parentElement.insertBefore(newCanvas, originalCanvas);
    //         originalContext.clearRect(0, 0, originalCanvas.width, originalCanvas.height);
    //         parentElement.removeChild(originalCanvas);

    //         newCanvas.addEventListener('mousedown', mouseDown, false);
    //         newCanvas.addEventListener('mouseup', mouseUp, false);
    //         newCanvas.addEventListener('mousemove', mouseMove, false);
            
    //         ctx = newCanvas.getContext('2d');
    //         rect = {};
    //         drag = false;
    //         imageObj.src = newCanvas.toDataURL('image/png');

    //         masked = false;
    //         flag = false;
    //         canvas = document.getElementById('canvas'); 
    // }    

    function draw(scale, translatePos) {
  
        var canvas = document.getElementById("canvas");
        var imgcontext = canvas.getContext("2d");
        var newCanvas = document.createElement('canvas');
                
        imgcontext.clearRect(0, 0, canvas.width, canvas.height);
    
        var img = new Image();

        if("{{$maskimageCount['image_type']}}".includes('l3_update')){
            img.src = "{{URL::to('/imageslevelthree/'.$aadhaarMaskingDetails['form_id'].'/'.$mask_image)}}"; // Replace with the path to your image
        }else{
            img.src = "{{URL::to('/imagesmarkedattachments/'.$aadhaarMaskingDetails['form_id'].'/'.$mask_image)}}"; // Replace with the path to your image     
        }
        
        newCanvas.addEventListener('mousedown', mouseDown, false);
        newCanvas.addEventListener('mouseup', mouseUp, false);
        newCanvas.addEventListener('mousemove', mouseMove, false);
        
        // canvas.height = imageObj.height > 600 ? 600 : imageObj.height;
        // canvas.width = imageObj.width > 600 ? 600 : imageObj.width;
        canvas.width = imageObj.width;
        canvas.height = imageObj.height;
        // ctx.drawImage(imageObj, 0, 0, canvas.width, canvas.height);           
    
        img.onload = function() {
            imgcontext.save();
            imgcontext.translate(translatePos.x, translatePos.y); 
            imgcontext.scale(scale, scale);   
        
            imgcontext.drawImage(img, 0, 0, canvas.width,canvas.height); 

            imgcontext.restore();
        };
    }

    $(document).ready(function(){  
      init();
    //   const container= document.getElementById('canvas');
    //   container.scrollTo({top:100 , left:100 ,behavior: 'smooth'});

      var canvas = document.getElementById("canvas");
      var translatePos = {
        x:imageObj.width > 600 ? 600 : imageObj.width,
        y: imageObj.height > 600 ? 600 : imageObj.height
      };

      var scale = 0.8;
      var scaleMultiplier = 0.8;
      var startDragOffset = {};
      var mouseDown = false;

      document.getElementById("plus").addEventListener("click", function() {
        scale /= scaleMultiplier;
        draw(scale, translatePos);
      }, false);

      document.getElementById("minus").addEventListener("click", function() {
        scale *= scaleMultiplier;
        draw(scale, translatePos);
      }, false);
    //   draw(scale, translatePos);  
    

   
    });

  


</script>


@endif

@endpush
    