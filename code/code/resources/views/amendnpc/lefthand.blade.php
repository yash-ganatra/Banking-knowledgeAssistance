@for($j=0;$j<count($getProofDocument);$j++)
    <h1 class="mt-4" style="display: inline-block;">{{$getProofDocument[$j]->evidence}}</h1>
    <button type="button" class="btn btn-primary mr-4   imageToggle minusBtn" id="{{$getProofDocument[$j]->id.'_imagetoggle'}}" style="border-radius: 50%; float: right; font-weight: bolder; margin-top: 15px;">-</button>
    <label for="{{$getProofDocument[$j]->id.'_imagetoggle'}}"></label>

    
        @php
            $checkExtension = explode('.',$getProofDocument[$j]->amend_proof_image)[1];
            $file_Path =  base64_encode($getProofDocument[$j]->amend_proof_image);
        @endphp

        @if($checkExtension != 'pdf')
        <div class="uploaded-img-ovd" style='filter:blur(30px);'>
            <img src="{{URL::to('/showamendimage/'.$file_Path)}}" class="img-fluid ovd_image">
        </div>
        @else
           <center> <p style="margin-top:70px;"><i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i><a href="{{URL::to('/showamendimage/'.$file_Path)}}" target="_blank" style="vertical-align: 10px;">{{explode('/',$getProofDocument[$j]->amend_proof_image)[3]}}</a>
                    </p>
           </center>
        @endif
    
    <div class="detaisl-left d-flex align-items-center">
        <span>
    </div>
    @php
        if(count($qcReviewDetails) == 0){
            $displayClass = '';
            $checked = '';
        }else{
            if(isset($qcReviewDetails[$j.'_comment']) && $qcReviewDetails[$j.'_comment'] != ''){
                $displayClass = '';
                $checked = '';
            }else{
                $displayClass = 'display-none';
                $checked = 'checked';
            }
        }

        if(in_array($masterDetails['crf_status'],[35,45,85,38,48])){
            $displayClass = 'display-none';
        }
    @endphp
    @if(in_array($role,[19,20,21,22]))
        <div class="d-flex flex-row">
            <div class="switch-blck {{$displayClass}}" style="margin-right: 20px;">
                <div class="toggleWrapper ">
                    <input type="checkbox" name="{{$getProofDocument[$j]->evidence}}" class="mobileToggle toggle_field reviewComments" id="{{$getProofDocument[$j]->id.'_toggle'}}" {{$checked}} >
                    <label for="{{$getProofDocument[$j]->id.'_toggle'}}"></label>
                </div>
            </div>
            <div class="comments-blck {{$displayClass}}" style="width:100%;">
                <input type="text" class="form-control commentsField" id="{{$getProofDocument[$j]->id.'_comment'}}" name="">
                <i title="save" class="fa fa-floppy-o saveAmendComments"></i>
            </div>
            <div class="details-custcol-row-bootm d-flex align-items-center"></div>
        </div>
    @endif
    <hr/>

@endfor