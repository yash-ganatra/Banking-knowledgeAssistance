@extends('layouts.app')
@section('content')
<style type="text/css">
  body{overflow-y: hidden;}
  .form-control:disabled:focus, .form-control[readonly]:focus{  border: 1px solid #ccc!important; }
</style>
<div class="">
  <div class="pcoded-inner-content">
    <!-- Main-body start -->
    <div class="main-body">
      <div class="">
        <!-- Page body start -->
        <div class="page-body">
          <div class="row">
            <div class="col-sm-12">
              <!-- Basic Inputs Validation start -->
              <div class="card">
                <div class="card-block emailalign" id="viewuserdetails">
                  <h4>Email/SMS Template</h4>
                  <form id="addtemplateDocumentForm" method="post" action="javascript:void(0);" novalidate>
                    <div class="row">
                      <div class="col-sm-6">
                            <div class="form-group row">
                      <label class="col-sm-4 col-form-label">Message Type</label>
                      <div class="col-sm-6">
                        {!! Form::select('Message Type',array('email'=>'Email','sms'=>'SMS'),$activityDetails['message_type'], array('class'=>'select-css templateDetailsAddField','id'=>'message_type','name'=>'message_type','placeholder'=>'Select Message Type')) !!}
                      </div>
                    </div>
                      </div>
                       <div class="col-sm-6">
                            <div class="form-group row">
                      <label class="col-sm-2 col-form-label">Role</label>
                      <div class="col-sm-6">
                        {!! Form::select('role',array('1'=>'Admin','2'=>'Auditor','3'=>'Auditee','4'=>'Management'),$activityDetails['role'], array('class'=>'select-css templateDetailsAddField','id'=>'role','name'=>'role',
                          'placeholder'=>'Select Role')) !!}
                      </div>
                    </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-sm-6">
                             <div class="form-group row">
                      <label class="col-sm-4 col-form-label">Activity </label>
                      <div class="col-sm-6">
                        {!! Form::select('activity',$activity,$activityDetails['activity'], array('class'=>'select-css templateDetailsAddField activity',
                                        'id'=>'activity','name'=>'activity','placeholder'=>'Select Activity')) !!}
                        <span class="messages"></span>
                        
                      </div>
                    </div>
                      </div>
                       <div class="col-sm-6">
                            <div class="form-group row">
                      <label class="col-sm-2 col-form-label">Code</label>
                      <div class="col-sm-6">
                        <input type="text" class="form-control templateDetailsAddField" name="activity_code" id="activity_code" 
                                                                                              value="{{$activityDetails['activity_code']}}">
                      </div>
                    </div>
                      </div>
                    </div>
                     <div class="row">
                      <div class="col-sm-6">
                           <div class="form-group row">
                      <label class="col-sm-4 col-form-label">Function Name</label>
                      <div class="col-sm-6">
                        <input type="text" class="form-control templateDetailsAddField" name="function_name" id="function_name" 
                                                                                                value="{{$activityDetails['function_name']}}">
                        <span class="messages"></span>
                      </div>
                    </div>
                      </div>
                       <div class="col-sm-6">
                           <div class="form-group row">
                      <label class="col-sm-2 col-form-label">Active</label>
                      <div class="col-sm-6">
                         {!! Form::select('is_active Type',array('1'=>'Yes','0'=>'No'),$activityDetails['is_active'], array('class'=>'select-css templateDetailsAddField','id'=>'is_active','name'=>'is_active','placeholder'=>'Select Template Active or Not')) !!}
                      </div>
                    </div>
                      </div>
                    </div>


                  @if($activityDetails['message_type'] == 'email')

                    <div class="form-group row  subject">
                  @else
                       <div class="form-group row d-none subject">
                  @endif
                      <label class="col-sm-2 col-form-label">Subject</label>
                      <div class="col-sm-8">
                        <input type="text" class="form-control templateDetailsAddField" name="subject" id="subject" 
                                                                                                value="{{$activityDetails['subject']}}">
                      </div>
                    </div>

                    <div class="form-group row">
                      <label class="col-sm-2 col-form-label">Message</label>
                      <div class="col-sm-8">
                        <textarea class="form-control-textarea-comments templateDetailsAddField" name="message" id="templatemessage">{{$activityDetails['message']}}</textarea>
                        <span class="messages"></span>
						<span>Use _NL_ to indicate a new line in the message template</span>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-sm-2"></label>
                      <div class="col-sm-10">
                        <button type="button" class="btn btn-primary m-b-0" id="saveTemplate" templateid="{{$activityDetails['id']}}">Update Template</button>
                        <a class="btn btn-primary back-button ml-3" href="{{url('admin/templates')}}">Cancel</a>
                      </div>
                    </div>
              </form>
        </div>
    </div>
<!-- Basic Inputs Validation end -->
@endsection
@push('scripts')
{{-- <script  src="{{ asset('custom/admin-dashboard.js') }}"></script> --}}
<script  src="{{ asset('custom/js/emailsms.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $("#message_type").select2({placeholder: "Select Message Type",allowClear: true});
    $("#is_active").select2({placeholder: "Select Template Active or Not",allowClear: true});
    $("#activity").select2({placeholder: "Select Template Activity",allowClear: true});
    $("#role").select2({placeholder: "Select Role",allowClear: true});
    $('#role').prop('disabled', true);
    //$('#activity').prop('disabled', true);
  });
</script>
@endpush