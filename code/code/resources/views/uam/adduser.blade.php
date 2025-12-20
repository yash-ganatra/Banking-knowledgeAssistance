@extends('layouts.app')
@section('content')
<style type="text/css">
  /*body{overflow-y: hidden;}*/
  .form-control:disabled:focus, .form-control[readonly]:focus{  border: 1px solid #ccc!important; }
  input#normal_flag ,input#priority_flag,input#nr_flag
  {
    opacity: 1;
  }
</style>
@php
$NORMAL_FLAG='';
$PRIORITY_FLAG='';
$NR_FLAG='';

@endphp
<div class="pcoded-content">
  <div class="pcoded-inner-content">
    <!-- Main-body start -->
    <div class="main-body">
      <div class="page-wrapper">
        <!-- Page body start -->
        <div class="page-body">
          <div class="row">
            <div class="col-sm-12">
              <!-- Basic Inputs Validation start -->
              <div class="card">
                <div class="card-block container" id="viewuserdetails">
                  <h4 class="add-users">Add User</h4>
                  <form id="main" method="post" action="/" novalidate>
                    <div class="form-group row">
                      <label class="col-sm-2 col-form-label">Employee ID</label>
                      <div class="col-sm-3">
                        <input type="text" class="form-control userDetailsEditField" name="hrmsno" id="emp_id" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                      </div>
                    </div>
                    <div id="notEditableFields" class="display-none">
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Full Name</label>
                        <div class="col-sm-6">
                          <input type="text" class="form-control userDetailsEditField" name="emp_name" id="emp_name" readonly>
                          <span class="messages"></span>
                        </div>
                      </div>

                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Business Unit</label>
                        <div class="col-sm-6">
                          <input type="text" class="form-control userDetailsEditField" name="empbusinessunit" id="emp_businessunit" readonly>
                        </div>
                      </div>

                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Mobile</label>
                        <div class="col-sm-3">
                          <input type="text" class="form-control userDetailsEditField" name="empmobileno" id="mobile" readonly> 
                        </div>
                      </div>

                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Email ID</label>
                        <div class="col-sm-3">
                          <input type="text" class="form-control userDetailsEditField" name="empemailid" id="email" readonly>
                        </div>
                      </div>

                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">User ID</label>
                        <div class="col-sm-3">
                          <input type="text" class="form-control userDetailsEditField" name="empldapuserid" id="emp_user_id" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">SOL ID</label>
                        <div class="col-sm-3">
                          <input type="text" class="form-control userDetailsEditField" name="empsol" id="emp_sol" readonly>
                        </div>
                      </div>
                      
                         <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Branch Name</label>
                        <div class="col-sm-6">
                          <input type="text" class="form-control userDetailsEditField" name="empbranch" id="branch_name" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Location</label>
                        <div class="col-sm-6">
                          <input type="text" class="form-control userDetailsEditField" name="emplocation" id="emp_location" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Role</label>
                        <div class="col-sm-6">
                          {!! Form::select('role',$roles,null, array('class'=>'select-css userDetailsEditField userRole',
                                        'id'=>'role','name'=>'role','placeholder'=>'Select Role')) !!}
                        </div>
                      </div>
                      <div class="form-group row role_type_list display-none">
                        <label class="col-sm-2 col-form-label">Role Type</label>
                        <div class="col-sm-6">
                          {!! Form::select('filter_type',$role_types,null, array('class'=>'select-css userDetailsEditField filter_type',
                                        'id'=>'filter_type','name'=>'filter_type','placeholder'=>'Select Role Type')) !!}
                        </div>
                      </div>

                      <div class="form-group row cluster_list display-none">
                        <label class="col-sm-2 col-form-label">Cluster</label>
                        <div class="col-sm-6">
                          {!! Form::select('clusters',$clusters,null, array('class'=>'select-css userDetailsEditField clusters',
                                        'id'=>'filter_ids','name'=>'filter_ids','placeholder'=>'Select Cluster')) !!}
                        </div>
                      </div>
                      <div class="form-group row regional_list display-none">
                        <label class="col-sm-2 col-form-label">Region</label>
                        <div class="col-sm-6">
                          {!! Form::select('regionals',$regionals,null, array('class'=>'select-css userDetailsEditField regionals',
                                        'id'=>'filter_ids','name'=>'filter_ids','placeholder'=>'Select Regional')) !!}
                        </div>
                      </div>
                      <div class="form-group row zone_list display-none">
                        <label class="col-sm-2 col-form-label">Zone</label>
                        <div class="col-sm-6">
                          {!! Form::select('zones',$zones,null, array('class'=>'select-css userDetailsEditField zones',
                                        'id'=>'filter_ids','name'=>'filter_ids','placeholder'=>'Select Zone')) !!}
                        </div>
                      </div>
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">RM Code</label>
                        <div class="col-sm-6">
                          <input type="text" class="form-control" name="rm_code" id="rm_code" >
                        </div>
                      </div>
                      <div class="uam-flag" id="uam-flag" style="display:none;">
                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Normal Flag</label>
                        <div class="col-sm-2">
                        <input type="checkbox" class="form-cotrol check" name="NORMAL_FLAG" id="normal_flag" value='1' {{($NORMAL_FLAG == '1')? "checked" : ""}}  >
                        </div>
                      </div>

                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Priority Flag</label>
                        <div class="col-sm-2">
                        <input type="checkbox" class="form-cotrol check" name="PRIORITY_FLAG" id="priority_flag" {{($PRIORITY_FLAG == '1')? "checked" : ""}}>
                        </div>
                      </div>

                      <div class="form-group row">
                        <label class="col-sm-2 col-form-label">NR Flag</label>
                        <div class="col-sm-2">
                        <input type="checkbox" class="form-cotrol check" name="NR_FLAG" id="nr_flag" {{($NR_FLAG == '1')? "checked" : ""}}>
                        </div>
                      </div>
                      </div>
                    </div>                              
                    <div class="form-group row">
                      <label class="col-sm-2"></label>
                      <div class="col-sm-6">
                        <button type="button" class="btn btn-primary m-b-0 btn-shadow" id="userdetails" >Submit</button>
                        <a class="btn btn-primary back-button ml-3" id="adduser" href="{{url('uam/dashboard')}}">Back</a>
                      </div>
                    </div>                  
              </form>
        </div>
    </div>
<!-- Basic Inputs Validation end -->
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/uam.js') }}"></script>
<script type="text/javascript">
  addSelect2('role','Role',true);
</script>
@endpush