@extends('layouts.app')
@section('content')
@php
    $role = Session::get('role'); 

    if(isset($masterDetails['reference_no'])){
        $imgString = '/amendnpc/viewekycphoto/'.$masterDetails['reference_no'];
    }else{ 
        $imgString = '';
    }
    $messageCrfUploadType = '';

    if($masterDetails['upload_crf_flag'] == 'SIGN'){
        $messageCrfUploadType = 'SIGN CRF UPLOD';
    }elseif($masterDetails['upload_crf_flag'] == 'ONLINE'){
        $messageCrfUploadType = 'ONLINE';
    }else{
        $messageCrfUploadType = 'AUTO';
    }
@endphp

@if($role == 19 && (!in_array($masterDetails['crf_status'],[35])))
    @include('amendnpc.amendreviewedit')
@endif
<script>
    function fetchphoto(path){
        setTimeout(function(){
            fetch(path).then(response => response.text()).then(data => $('#ekycPhoto').attr('src', data));    
        }, 1200);
    }
</script>
<input type="hidden" id="crfNumber" value="{{$crfNumber}}">
<input type="hidden" id="role" value="{{$role}}">

<div class="pcoded-content1" id="verify-checkbox">
    <div class="pcoded-inner-content1">
        <div class="main-body">
            <div class="page-wrapper">
                @include("bank.mask_unmask_btn")
                <div class="page-body">
                     @if(count($hold_comment) >= 1)
                    @php
                      $hold_comment = (array) current($hold_comment);
                    @endphp
                     <div class="card">
                       <div id="casatd-key-block" class="card-block pb-0">
                           <div class="row">
                             <div class="col-md-12">
                              @if(in_array($masterDetails['crf_status'],[35,45]))
                                <h4 class="hold_reject_title">Form on hold: <span class="hold_reject">
                                  {{$hold_comment['comments']}}</span></h4>
                              @endif
                              @if(in_array($masterDetails['crf_status'],[36,38,48]))
                                 <h4 class="hold_reject_title">Form rejected: <span class="hold_reject">
                                  {{$hold_comment['comments']}}</span></h4>
                              @endif
                             </div>
                           </div>
                        </div>
                    </div>
                    @endif
                    <div class="card">
                    <div id="casatd-key-block" class="card-block pb-0">
                        @include('amendnpc.amendnpcheader')
                    </div></div>
                    <div class="card" id="crf_review">
                        <div class="card-block">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="proofs-blck">
                                        <div class="row">
                                            <div class="custom-col-review col-md-4">
                                                <span>CUSTOMER APPROVAL : {{$messageCrfUploadType}}</span> <br><br>
                                                <div class="form-group">
                                                    @if($imgString != '')
                                                        <div class='uploaded-img-ovd' style="filter:blur(30px);">
                                                        <img src='' id='ekycPhoto' onerror=fetchphoto('{{asset($imgString)}}') />
                                                        </div>
                                                    @endif    
                                                </div>
                                                <div class="crf toggleWrapper">
                                                    @include('amendnpc.lefthand')
                                                </div>
                                            </div>
                                            <div class="custom-col-review proof-of-identity col-md-8">
                                                @include('amendnpc.righthand')
                                            </div>
                                        </div>
                                        @if(in_array($role,[21,22,23]) && false)
                                            @include('amendnpc.amendl3update')
                                        @endif
                                    </div>
                                    </div>        
                                     <!-- // 22May23 - For BS5 - commented below line        -->
                                        <!-- <div class="row mx-auto"> OLD-->
                                            <div class="col-md-3 position-relative bottom-0 start-50 translate-middle-x">
                                            @include('amendnpc.button')
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
@include('amendnpc.modal')
@endsection
@push('scripts')
<script type="text/javascript">


// Get the modal
var hold = document.getElementById("Hold");
var reject = document.getElementById("Reject");

// Get the button that opens the modal
var btn = document.getElementById("hold_modal");
var btn2 = document.getElementById("reject_modal");

// Get the <span> element that closes the modal
var span1 = document.getElementsByClassName("hold-no")[0];
var span2 = document.getElementsByClassName("reject-no")[0];
// var span = document.getElementsByClassName("reject")[0];

// When the user clicks the button, open the modal
if(btn != null){
    btn.onclick = function() {
      hold.style.display = "block";
    }
}
if(btn2 != null){
    btn2.onclick = function() {
      reject.style.display = "block";
    }
}


// When the user clicks on <span> (x), close the modal
if(span1 != null){
    span1.onclick = function() {
      hold.style.display = "none";
    } 
} 

if(span2 != null){
    span2.onclick = function() {
      reject.style.display = "none";
    }
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == hold) {
    hold.style.display = "none";
  }
}

window.onclick = function(event) {
  if (event.target == reject) {
    reject.style.display = "none";
  }
}
var _savedComments = JSON.parse('<?php echo json_encode($lastSavedComments); ?>');

  function showPreviewModal(elementSrc){
         if (window.event.ctrlKey) {
                document.getElementById("imagePreviewModal").style.display = 'block';
                $('#imagePreviewSrc').attr('src', $(elementSrc.srcElement).attr('src'));
                $('#imagePreviewSrc').attr('width','100%');
            }
    }

</script>
<script src="{{asset('custom/js/amendreview.js')}}"></script>
@endpush