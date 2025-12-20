@extends('layouts.app') 
@section('content')
@php
    $checked = "";
    $display = "";
    $pf_type = 'pancard';
    $disabled = '';
    $accountHoldersCount = $accountDetails['no_of_account_holders'];
	$aof_number = $accountDetails['aof_number'];
	$roleId = Session::get('role');
  Session::put('getCCDeclarationImages',$getCCDeclarationImages);
@endphp
@if(count($hold_reject_comment) > 0)
@php
    if(isset($hold_reject_comment['comments']))
            {
                //$hold_reject_comment = $hold_reject_comment['comments'];
            }
@endphp
@endif

{{-- For L3 role need not show the green switches --}}
@if($roleId == 8)
	<style> .switch-blck { display: none;} </style>
	<input type='hidden' id='roleId' value='L3' />
@else 
	<input type='hidden' id='roleId' value='NPC' />	
@endif

<style>
.details-custcol-row {
border-bottom: 1px solid rgba(239, 240, 241, 0.54)!important;
    padding-bottom: 10px !important;
    margin-bottom: 10px !important;
}
 .display-none-npc-loader{
        display: none;
    }

	.cropper-container {
        min-width: 400px;
		min-height: 250px;
        max-width: 100%;
        margin-bottom: 1rem;
        background-color: white;
        text-align: center;
        width: 100%;
    }
	.cropper-container {

        max-width: 100%;
		min-width: 400px;
		min-height: 250px;
        margin-bottom: 1rem;
        background-color: white;
        text-align: center;
        width: 100%;
		left: 0;
		top: 0;
    }
	 .cropper-container > img {        
		width: 100%;
		/*min-height: 250px;
		max-width: 100%;*/
		left: 0;
    } 

 /*.display-none-npc2-loader{
        display: none;
    }*/
     a{text-decoration: none!important;}
</style>

        @if(($accountDetails['account_type'] == 'Term Deposit') && ($accountDetails['is_new_customer'] == 0) && ($accountDetails['source'] == 'CC'))

              @include('npc.npccallcenterreview')
        @else
        
              @include('npc.npcreview')
        @endif


@if($roleId == 3 && count($reviewDetails) == 0 && $aofStatus != 'HOLD')    
	<input type='hidden' id='L1EDIT' value='L1EDIT' />
  @include('npc.eloneedit')
@endif


@endsection
@push('scripts')
<script  src="{{ asset('custom/js/npc.js') }}"></script>
<script>


var number = $('#mobile').text();
var mask = number.replace(/\D/g,'');
var maskedNumber = mask.replace(/(\d{3})\-?(\d{3})\-?(\d{4})/,'$1-$2-$3');
$('#mobile').text(maskedNumber);

var _L3_Responses = JSON.parse('<?php echo addslashes(json_encode($L3_Responses,JSON_HEX_APOS | JSON_HEX_QUOT)) ?>');
var _savedComments = JSON.parse('<?php echo json_encode($lastSavedComments); ?>');
var _lastIterationNpcComments = JSON.parse('<?php echo json_encode($npcbankreviewDetails); ?>');

var _aof = JSON.parse('<?php echo $aof_number; ?>');
var _delightSavings = JSON.parse('<?php echo json_encode($delightSavings); ?>');
var _sourceofapp = JSON.parse('<?php echo json_encode($accountDetails['source']); ?>');
var _startTime = new Date();
if (JSON.parse('<?php echo json_encode($accountDetails['is_new_customer']); ?>') == '0') {
  _customerType = 'ETB';
}else{
  _customerType = 'NTB';
}


showHideSections(_customerType);

// Show the first tab and hide the rest
$('#reviewcod-tabs-nav li:first-child').addClass('active');
$('.reviewcod-tab-content-cust').hide();
$('.reviewcod-tab-content-cust:first').show();

// Click function
$('#reviewcod-tabs-nav li').click(function(){
  $('#reviewcod-tabs-nav li').removeClass('active');
  $(this).addClass('active');
  $('.reviewcod-tab-content-cust').hide();

  var activeTab = $(this).find('a').attr('href');
  $(activeTab).fadeIn();
  return false;
});


$('#reviewrisk-tabs-nav li:first-child').addClass('active');
$('.reviewrisk-tab-content-cust').hide();
$('.reviewrisk-tab-content-cust:first').show();

// Click function
$('#reviewrisk-tabs-nav li').click(function(){
  $('#reviewrisk-tabs-nav li').removeClass('active');
  $(this).addClass('active');
  $('.reviewrisk-tab-content-cust').hide();

  var activeTab = $(this).find('a').attr('href');
  $(activeTab).fadeIn();
  return false;
});


var els = document.querySelectorAll('input[disabled=""]');
for (var i=0; i<els.length; i++){
    els[i].classList.add('showSwitchDisabled');
	els[i].parentNode.style.display='none';
}

// Show the first tab and hide the rest
$('#reviewProofs-tabs-nav li:first-child').addClass('active');
$('.reviewProofs-tab-content-cust').hide();
$('.reviewProofs-tab-content-cust:first').show();

// Click function
$('#reviewProofs-tabs-nav li').click(function(){
  $('#reviewProofs-tabs-nav li').removeClass('active');
  $(this).addClass('active');
  $('.reviewProofs-tab-content-cust').hide();

  var activeTab = $(this).find('a').attr('href');
  $(activeTab).fadeIn();
  return false;
});

$(document).ready(function(){

	if(typeof(_aof) != 'undefined' && _aof != ''){
		document.title = 'CUBE AOF: '+_aof;
	}else{
		document.title = 'DCB - CUBE - AIS';
	}
	
    $('.mobile').inputmask('9999-999-999', {
            clearMaskOnLostFocus: false,
            autoUnmask: true,
        });

    $(".proof-tab-content-cust").hide();
	
	// Temporary hide the unwanted green ticks
	$('.fa-check').hide();
 
    var cropperOptionsForReview = { 
		viewMode: 2,
		dragMode: 'move',
        autoCrop:false,
        dragCrop: false,
        resizable: false,
		cropBoxMovable: false,
        rotatable: true,
		cropBoxResizable: false,
		autoCrop: false, 
		autoCropArea: 1,  
        dragCrop: false,
        resizable: false,
		toggleDragModeOnDblclick: false,
	};
	
   $(".ovd_image").cropper(cropperOptionsForReview).resize();
   $(".proof_of_address-zoom").cropper(cropperOptionsForReview).resize();
   $(".photograph-zoom").cropper(cropperOptionsForReview).resize(); 
   
   $('a[href="#photograph-tab"]').click(function(){ setTimeout(function(){$(".photograph-zoom").resize(); },200);   });   
   $('a[href^="#proof-of-permanent-address"]').click(function(){ setTimeout(function(){$(".proof_of_address-zoom").resize(); },200);   });   
   
   $('a[href^="#proof-of-identity"]').click(function(){ setTimeout(function(){$(".ovd_image").resize(); },200);   });   
   $('a[href^="#reviewProofs-tab"]').click(function(){ setTimeout(function(){$(".ovd_image").resize(); },200);   });

   setTimeout(function(){$(".photograph-zoom").resize(); },2500);
   $('a[href^="#reviewcod-tab"]').click(function(){ setTimeout(function(){$(".ovd_image").resize(); },200);   });
   
    $('.rotate').click(function(){
        $(this).parent().next().find('.rotate_image').cropper('rotate',90);
    });

	// To Check	
	//$('.ovd_image').cropper("getImageData").naturalWidth
	//$('.proof_of_address-zoom').cropper("setImageData","{width:385}")
	//$('.proof_of_address-zoom').resize();

    $('#reviewProofs-tabs-nav li:first-child').click(function(){
        $(".proof-tab-content-cust").removeAttr('style');
        $(".proof-tab-content-cust").hide();
    });
    
    blur_img(30,$(".uploaded-img-ovd"),"blur");
    
});

/*For next tab*/
function nexttab(source,applicantcount) {
    var token = source.split('-');
    var tokenLength = token.length;

    switch(token[2]){
        case 'identity':
         // 22May23 - For BS5 - commented below line
            // $('a[data-id="proof-of-permanent-address-'+token[tokenLength-1]+'"]').click();
            $('a[data-id="proof-of-permanent-address-'+token[tokenLength-1]+'"]').tab('show');
            break;
        case 'permanent':
        // 22May23 - For BS5 - commented below line
            // $('a[data-id="proof-of-current-address-'+token[tokenLength-1]+'"]').click();
            $('a[data-id="proof-of-current-address-'+token[tokenLength-1]+'"]').tab('show');
            break;
        case 'current':
            // var current_user  = parseInt(token[tokenLength-1]);
            // if(current_user == applicantcount){
            //     $('a[data-id="photographsignature"]').click();
            //     // $('a[data-id="photographsignature"]').tab('show');
            //     break;
            // }
            // if(current_user < applicantcount){
            //     var next_user  = current_user + 1 ;
            //  // 22May23 - For BS5 - commented below line
            //     $('a[data-id="tab_applicant_'+next_user+'"]').click();
            //      // $('a[data-id="tab_applicant_'+next_user+'"]').tab('show');
            //     break;
            // }else{
            //     $('a[data-id="photographsignature"]').click();
            //     // $('a[data-id="photographsignature"]').tab('show');
            //     break;
            // }
            
            if(applicantcount != "N"){
              $('a[data-id="tab_applicant_'+applicantcount+'"]').click();
            }else{
                $('a[data-id="photographsignature"]').click();
            }
       default:
    }
}

/*For Previous tab*/
function previoustab(source,applicantcount) {
    var token = source.split('-');
    var tokenLength = token.length;

    switch(token[2]){
        case 'permanent':
         // 22May23 - For BS5 - commented below line
            // $('a[data-id="proof-of-identity-'+token[tokenLength-1]+'"]').click();
             $('a[data-id="proof-of-identity-'+token[tokenLength-1]+'"]').tab('show');
            break;
        case 'current':
         // 22May23 - For BS5 - commented below line
            // $('a[data-id="proof-of-permanent-address-'+token[tokenLength-1]+'"]').click();
             $('a[data-id="proof-of-permanent-address-'+token[tokenLength-1]+'"]').tab('show');
            break;

       default:
    }
}

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

function showPreviewModal(elementSrc){
	 if (window.event.ctrlKey) {
			document.getElementById("imagePreviewModal").style.display = 'block';
			$('#imagePreviewSrc').attr('src', $(elementSrc.srcElement).attr('src'));
			$('#imagePreviewSrc').attr('width','100%');
			//uploaded-img-ovd ovd_image
		}
}

$('.img-fluid, .proof_of_address-zoom, .photograph-zoom, .imagetoenlarge').on('click',function(){
	 if (window.event.ctrlKey) {
			document.getElementById("imagePreviewModal").style.display = 'block';
			$('#imagePreviewSrc').attr('src',$(this).attr('src'));
			$('#imagePreviewSrc').attr('width','100%');
			//uploaded-img-ovd ovd_image
		}
});


$('.cancel-preview').on('click',function(){
		$('#imagePreviewSrc').attr('src','');
		document.getElementById("imagePreviewModal").style.display = 'none';
});


 function setL3values(){
	if(typeof(_L3_Responses) != 'undefined'){
		for(var resp = 0; resp < _L3_Responses.length; resp++){
			var id = _L3_Responses[resp].column_name;
			var iteration = _L3_Responses[resp].iteration;
			var comments = _L3_Responses[resp].comments; 
			var cDate = moment(_L3_Responses[resp].response_date).format('DD-MMM-YYYY HH:MM');  
			var response = _L3_Responses[resp].response;
			var roleTag = _L3_Responses[resp].role_id == 5 ? 'QC' : 'AU';				
			var infoString = '<div id="'+id+'_l3_comments" style="padding-bottom:5px;"><br><b>'+cDate+'</b><br>'+roleTag+': '+comments+'<br>L3: '+response+'</div>';			
			if($('#'+id).closest('.details-custcol-row-top').length == 1){
				$('#'+id).closest('.details-custcol-row-top').after(infoString);  			
				$('#'+id).closest('.details-custcol-row-top').css('background-color','aliceblue');				
			}else{
				$('#'+id).closest('.d-flex.flex-row').after(infoString);  			
				$('#'+id).closest('.d-flex.flex-row').css('background-color','aliceblue');
			}
			$('#'+id+'_l3_comments').css('background-color','aliceblue');
		}
	}
 }

 function npcLastReviewComments(){
    if(typeof(_lastIterationNpcComments) != 'undefined'){
      for(var i = 0; i < _lastIterationNpcComments.length; i++){
            var id = _lastIterationNpcComments[i].column_name;
          
            var comments = _lastIterationNpcComments[i].comments;
			      var roleTag = _lastIterationNpcComments[i].role_id == 3 ? 'L1 : ' : 'L2 : ';	
            var commentString = roleTag+comments;
            $('#'+id).parent().parent().before('<p style="color: red;">'+commentString+'</p>');	
      }
    }
  }
 
function updateLastSavedComments(savedObject){ 
	if(typeof(savedObject) == 'undefined' || savedObject == null || savedObject == '' || savedObject.length == 0) return false;
	var currRole = $('#roleId').val();
    for(var fld = 0; fld < savedObject.length; fld++){
		var fieldName = savedObject[fld].column_name;		
		if(currRole == 'L3'){
			var fieldValue = savedObject[fld].response;
		}else{
			var fieldValue = savedObject[fld].comments;
		}
		$("#"+fieldName).parent().addClass('display-none');		
        var buildHtml = '<p>'+
				fieldValue+
				' <a href="javascript:void(0);" class="editComments" data-field="'+fieldName+'" id="'+savedObject[fld].id+'">'+
					'Edit'+
				'</a>'+
			'</p>';
       $("#"+fieldName).parent().next().html(buildHtml).removeClass('display-none');                
	}	
	$.growl({message: 'Last '+savedObject.length+' saved comments retreived'},{type: 'success'});
    return false;
}

setTimeout(function(){setL3values();}, 1500);	
setTimeout(function(){npcLastReviewComments();}, 1500);	
setTimeout(function(){updateLastSavedComments(_savedComments);}, 3000);	

// To increase clickable area for green switch
$('.reviewComments').css('padding','30px;');
</script>

@if(isset($etb_cust_crf) && !empty($etb_cust_crf))
<script>
function etb_cust_crf_popup(){
  let etb_cust_crf = {!!json_encode($etb_cust_crf)!!}
  etb_cust_crf.forEach(function(e){
    $.growl({message: `Amendment request raised for the custormer ID - ${e[1]} is yet to processed. CRF - ${e[0]}`},{type: "warning",allow_dismiss:true,delay: 0});
  });
}
etb_cust_crf_popup()
</script>
@endif


@endpush
