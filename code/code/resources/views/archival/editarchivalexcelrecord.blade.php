@extends('layouts.app')
@section('content')
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
					<div class = "row">
							<div class="col-md-12">
					      	<div class="card mt-2">
					        	<div class="card-body">
					        		<h5> Edit Archival Record</h5>
					        	@php
        							$getAofDatas = Arr::except($getAofData,['id','aof_number','created_at','created_by','updated_at','updated_by']);
                                    
					        	@endphp
                                        @foreach($getAofDatas as $column => $values)
					        		<div class="row">
                                        <div class="col-md-6 mt-2">
                                             <label class="col-sm-4 col-form-label">{{strtoupper($column)}}</label>
                                        </div>
                                        @if($column == 'archival_date')
                                            <div class="col-md-6 mt-2">
                                             <input type="text" class="form-control archivalrecorddata archivalrecorddatadate" id='{{$column}}' name='{{$column}}' value='{{$values}}' style="margin-left: -450px;">
                                        </div>

                                        @else
                                        <div class="col-md-6 mt-2">
                                             <input type="text" class="form-control archivalrecorddata"  id='{{$column}}' name='{{$column}}' value='{{$values}}' style="margin-left: -450px;">
                                        </div>
                                        @endif
                                     </div>
                                     	@endforeach
                                     <div class="form-group row mx-auto mt-3">
                                            <div class="col-sm-12">
                                                <button type="button" class="btn btn-primary m-b-0 saveaofcolumnData" id="{{$getAofData['aof_number']}}">Save</button>
                                                	<a class="btn btn-primary back-button ml-3" id="adduser" href="{{url('archival/'.$previousPage)}}">Back</a>
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
    </div>
</div>


@endsection
@push('scripts')
<script type="text/javascript">

$(".archivalrecorddatadate").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",            
        });


$("body").on("click",".saveaofcolumnData",function(){
        var columnDataObject = [];
        columnDataObject.data = {};
        columnDataObject['url'] = '/archival/savearchivalrecord';

        if($('#box_barcode').val() == '' || $('#file_barcode').val() == '' ){
           $.growl({message:'Field Empty'},{type: "warning"});
           return false;
        }

        $(".archivalrecorddata").each(function() {
            if($(this).val() !== '')
            {
                columnDataObject.data[$(this).attr('name')] = $(this).val();
            }
        });

        if($(this).attr("id") != '')
        {
            columnDataObject.data['aof_number'] = $(this).attr("id");
        }
        // columnDataObject.data['screen'] = window.location.href.substr(window.location.href.lastIndexOf('/') +1 );
        columnDataObject.data['functionName'] = 'SaveArchivalRecordFunction';

        //getting the data from here
        crudAjaxCall(columnDataObject);
    });

function SaveArchivalRecordDataCallbackFunction(response,object){
	var baseUrl = $('meta[name="base_url"]').attr('content');
	 if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message:response['msg']},{type: "warning"});
            return false;
        }
    setTimeout(function(){
        window.location = $('.back-button').attr('href');;
    },2000);
    return false;
}
</script>
@endpush