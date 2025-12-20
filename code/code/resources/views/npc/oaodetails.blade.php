@extends('layouts.app')
@section('content')
@php
       // echo "<pre>";print_r($formdetails);exit;
		$count = 0; 

@endphp
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body">                
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-block table-border-style">
                                    <div class="row mb-3">
                                        <div class="col-md-12">                                            
											<h5>DSA Update 
													&nbsp;&nbsp;&nbsp;
													<span id='aof_number_for_copy'></span>
													&nbsp;
													<img id="copy_to_clip_img" target="_blank" src="" style="width: 16px !important; margin-bottom:9px; display:none;" 
													title='Copy to clipboard'>
											</h5>
                                        </div>
                                    </div>
                                    <div class="row filter mb-4 mt-0">
                                       
                                    </div>
                                    <div class=" "></div>
										<div class="timeline-item-heading row">
										    <div class="lable-heading content-blck-1 col-sm">FIELD</div>
										    <div class="lable-heading content-blck-2 col-sm">VALUE</div>
										    <div class="lable-heading content-blck-3 col-sm">VALUE TO UPDATE</div>
										    <div class="lable-heading content-blck-4 col-sm">COMMENT</div>
										    <div class="lable-heading content-blck-5 col-sm" style="text-align: center;">ACTION</div>
										</div>
										<span class="display-none">{{$form_id}}</span>
										@forEach($accountDetails as $field => $fieldArray)
											@if(isset($fieldArray['VALUE']))
											@php
	       										//echo "<pre>";print_r($fieldArray);exit;
												$count++;
	       										$fieldToShow =  strtoupper($field);
	       										$fieldToShow = preg_replace('/_/i', ' ', $fieldToShow)
											@endphp
											    <div class="timeline timeline-5 accountdetails">
											        <!--begin::Item-->
											        <div class="timeline-item align-items-start text-muted">
											            <!--begin::Badge-->
											            <div class="timeline-badge step-{{$count}}">
											                <i class="fa fa-genderless numeric text-grey icon-xl"></i>
											            </div>
											            <!--end::Badge-->
											            <!--begin::Content-->
											            <div class="timeline-content ">
											                <!--begin::Text-->
											                <div class="font-weight-mormal font-size-lg timeline-content row">
											                    <div class="content-blck-1  content-blck-tl col-sm ml-4">
											                        {{$fieldToShow}}
											                    </div>
											                    <div class="content-blck-2  content-blck-tl col-sm">
											                    	{{$fieldArray['VALUE']}}
											                    </div>
											                    <div class="content-blck-4  content-blck-tl col-sm">
											                        <input type="text" name="{{$field}}-value" class="value" maxlength="{{$fieldArray['MAX']}}" id="{{$field}}-value" value="">
											                    </div>
											                    <div class="content-blck-5  content-blck-tl col-sm">
											                        <textarea type="text" name="{{$field}}-comment" row='2' class="comment " id="{{$field}}-comment"  value=""></textarea>
											                    </div>
											                    <div class="content-blck-6  comments_blck_width col-sm content-blck-tl text-center font-weight-bold">
											                            <!-- <button type="button" class="btn btn-outline-success disabled_button_hover">Done</button> -->
											                            <button class="btn {{$field}}-update updateDsaField" id="{{$field}}-{{$form_id}}">Update</button>
											                    </div>
											                </div>
											                <!--end::Text-->
											            </div>
											            <!--end::Content-->
											        </div>
											        <!--end::Item-->
											    </div>
											@endif
										@endforEach
										</div>
									</div>
                            </div>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">

$("body").on("click",".updateDsaField",function(){
        var field = $(this).attr('id').split('-')[0];
        var formId = $(this).attr('id').split('-')[1];
        var value = $('#'+field+'-value').val();
        var comment = $('#'+field+'-comment').val();

        if (value == '' || comment == '') {
            $.growl({message: 'Please enter value and comment as mandatory fields'}, {type: 'warning'});
            return false;
        }

        var oaoUpdateObject = [];
        oaoUpdateObject.data = {};
        oaoUpdateObject.url =  '/channelid/updateOaoDetails';
        oaoUpdateObject.data['form_id'] = formId;
        oaoUpdateObject.data['field'] = field;
        oaoUpdateObject.data['value'] = value;
        oaoUpdateObject.data['comment'] = comment;

        oaoUpdateObject.data['functionName'] = 'updateOaoDetailsCallBack';

        crudAjaxCall(oaoUpdateObject);
        return false;
    });

function updateOaoDetailsCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    
    setTimeout(function(){
        window.location.reload();
    },1000);
    return false;
}

</script>
@endpush
