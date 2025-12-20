<style type="text/css">
  /*body{overflow-y: hidden;}*/
  
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
@php
if(isset($employeeDetails) && count($employeeDetails) > 0){
            $normal_flag = $employeeDetails['normal_flag'];
            $priority_flag = $employeeDetails['priority_flag'];
            $nr_flag = $employeeDetails['nr_flag'];
}
@endphp

<div class="row">
  <div class="col-md-7">
    <h4>EDIT USER</h4>
    <form id="main" method="post" action="/" novalidate>
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Employee ID</label>
        <div class="col-sm-4">
          <input type="text" class="form-control" name="hrmsno" id="emp_id" 
                                                                          value="{{$employeeDetails['hrmsno']}}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Full Name</label>
        <div class="col-sm-7">
          <input type="text" class="form-control" name="emp_name" id="emp_name" 
            value="{{$employeeDetails['emp_first_name'].' '.$employeeDetails['emp_middle_name'].' '.$employeeDetails['emp_last_name']}}" readonly>
          <span class="messages"></span>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Business Unit</label>
        <div class="col-sm-7">
          <input type="text" class="form-control" name="empbusinessunit" id="emp_businessunit" 
                                                                          value="{{$employeeDetails['empbusinessunit']}}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Mobile</label>
        <div class="col-sm-4">
          <input type="text" class="form-control" name="empmobileno" id="mobile" 
                                                                          value="{{$employeeDetails['empmobileno']}}" readonly> 
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Email ID</label>
        <div class="col-sm-7">
          <input type="text" class="form-control" name="empemailid" id="email" 
                                                                          value="{{$employeeDetails['empemailid']}}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">User ID</label>
        <div class="col-sm-4">
          <input type="text" class="form-control" name="empldapuserid" id="emp_user_id" 
                                                                          value="{{$employeeDetails['empldapuserid']}}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">SOL ID</label>
        <div class="col-sm-4">
          <input type="text" class="form-control" name="empsol" id="emp_sol" 
                                                                          value="{{$employeeDetails['empsol']}}" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Branch Name</label>
        <div class="col-sm-7">
          <input type="text" class="form-control" name="empbranch" id="branch_name" 
                                                                          value="{{$employeeDetails['empbranch']}}" readonly>
        </div>
      </div>      
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Location</label>
        <div class="col-sm-7">
          <input type="text" class="form-control" name="emplocation" id="emp_location" 
                                                                          value="{{$employeeDetails['emplocation']}}" readonly>
        </div>
      </div>      
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">RM Code</label>
        <div class="col-sm-7">
        <input type="text" class="form-control" name="rm_code" id="rm_code" maxlength="25" value="{{$employeeDetails['rm_code']}}" >
        </div>
      </div>    
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Role</label>
        <div class="col-sm-7">
          {!! Form::select('role',$roles,$employeeDetails['role'], array('class'=>'select userDetailsEditField userRole',
                        'id'=>'role','name'=>'role','placeholder'=>'Select Role')) !!}
        </div>
      </div>  
      <div class="uam-flag" id="uam-flag" style="display:none;">
      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Normal Falg</label>
        <div class="col-sm-7">
        <input type="checkbox" class="form-cotrol check data" name="NORMAL_FLAG" id="normal_flag" value="{{$employeeDetails['normal_flag']}}" {{($normal_flag == 'Y')? "checked" : ""}} >
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-3 col-form-label">Priority Falg</label>
        <div class="col-sm-7">
        <input type="checkbox" class="form-cotrol check" name="PRIORITY_FLAG" id="priority_flag" value="{{$employeeDetails['priority_flag']}}" {{($priority_flag == 'Y')? "checked" : ""}}>
        </div>
      </div>

      <div class="form-group row">
        <label class="col-sm-3 col-form-label">NR Falg</label>
        <div class="col-sm-7">
        <input type="checkbox" class="form-cotrol check" name="NR_FLAG" id="nr_flag" value="{{$employeeDetails['nr_flag']}}" {{($nr_flag == 'Y')? "checked" : ""}}>
        </div>
      </div>
      </div>


      @if($roles == 14)
         <div class="form-group row role_type_list">
      @else
         <div class="form-group row role_type_list display-none">
      @endif
      <div class="form-group row role_type_list">
        <label class="col-sm-3 col-form-label">Role Type</label>
        <div class="col-sm-7">
          {!! Form::select('filter_type',$role_types,$employeeDetails['filter_type'], array('class'=>'select-css userDetailsEditField filter_type',
                                        'id'=>'filter_type','name'=>'filter_type','placeholder'=>'Select Role Type')) !!}
        </div>
      </div> 
      
      @if($employeeDetails['filter_type'] == 1)
       <div class="form-group row regional_list">
      @else
       <div class="form-group row regional_list display-none">
      @endif
        <label class="col-sm-3 col-form-label">Region</label>
        <div class="col-sm-7">
          {!! Form::select('regionals',$regionals,$employeeDetails['filter_ids'], array('class'=>'select-css userDetailsEditField regionals',
                                        'id'=>'filter_ids','name'=>'filter_ids','placeholder'=>'Select Regional')) !!}
        </div>
      </div>

      @if($employeeDetails['filter_type'] == 2)
       <div class="form-group row zone_list">
      @else
       <div class="form-group row zone_list display-none">
      @endif
        <label class="col-sm-3 col-form-label">Zone</label>
        <div class="col-sm-7">
          {!! Form::select('zones',$zones,$employeeDetails['filter_ids'], array('class'=>'select-css userDetailsEditField zones',
                                        'id'=>'filter_ids','name'=>'filter_ids','placeholder'=>'Select Zone')) !!}
        </div>
      </div>

      @if($employeeDetails['filter_type'] == 3)
       <div class="form-group row cluster_list">
      @else
       <div class="form-group row cluster_list display-none">
      @endif
        <label class="col-sm-3 col-form-label">Cluster</label>
        <div class="col-sm-7">
          {!! Form::select('clusters',$clusters,$employeeDetails['filter_ids'], array('class'=>'select-css userDetailsEditField clusters',
                                        'id'=>'filter_ids','name'=>'filter_ids','placeholder'=>'Select Cluster')) !!}
        </div>
      </div>
    </div>

    </form>
  </div>
  <div class="col-md-5 view-users">
    <h4 style="text-transform: uppercase;"> HR Details</h4>
    @if(count($employeeApiDetails) > 0)
      <div class="row">
        <div class="col-sm-12">
         <label class="col-form-label">Employee ID :</label>
          <span class="value userDetailsEditField" id="hrmsno">{{$employeeApiDetails['HRMSNO']}}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <label class="col-form-label">Full Name :</label>
          <span class="value userDetailsEditField" id="emp_name">{{$employeeApiDetails['EMP_FIRST_NAME'].' '.$employeeApiDetails['EMP_MIDDLE_NAME'].' '.$employeeApiDetails['EMP_LAST_NAME']}}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <label class="col-form-label">Business Unit :</label>
          <span class="value userDetailsEditField" id="empbusinessunit">{{$employeeApiDetails['EMPBUSINESSUNIT']}}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <label class="col-form-label">Mobile :</label>
          <span class="value userDetailsEditField" id="empmobileno">{{$employeeApiDetails['EMPMOBILENO']}}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <label class="col-form-label">Email ID :</label>
          <span class="value userDetailsEditField" id="empemailid">{{$employeeApiDetails['EMPEMAILID']}}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <label class="col-form-label">User ID :</label>
          <span class="value userDetailsEditField" id="empldapuserid">{{$employeeApiDetails['EMPLDAPUSERID']}}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <label class="col-form-label">SOL ID :</label>
          <span class="value userDetailsEditField" id="empsol">{{$employeeApiDetails['EMPSOL']}}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <label class="col-form-label">Branch Name :</label>
          <span class="value userDetailsEditField" id="empbranch">{{$employeeApiDetails['EMPBRANCH']}}</span>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <label class="col-form-label">Location :</label>
          <span class="value userDetailsEditField" id="emplocation">{{$employeeApiDetails['EMPLOCATION']}}</span>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-2"></label>
        <div class="col-sm-10">
          <button type="button" class="btn btn-primary m-b-0 btn-shadow" id="saveUser" userid="{{$employeeDetails['id']}}">Sync</button>
        </div>
      </div>
    @else
       <div class="card w-100">
        <div class="card-body">
          <p class="card-text"><i class="fa fa-exclamation-circle" aria-hidden="true" style="color: red"></i>&nbsp;
            Unable to fetch HRMS data. Please contact API Admin.
          </p>
        </div>
      </div>
    @endif
  </div>
</div>

@push('scripts')
        <script  src="{{ asset('custom/js/uam.js') }}"></script>
        <script type="text/javascript">
            $(document).ready(function(){
              
                var data=$('#role').val();  
                    
                if(data==3 ||data==4){
                    $('#uam-flag').css('display', 'block')
                }
                else{
                    $('#uam-flag').css('display', 'none')
                }
                
            });
        </script>
    @endpush