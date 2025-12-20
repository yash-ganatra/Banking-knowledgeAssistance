    @extends('layouts.app')
    @section('content')
    <style type="text/css">
        body{overflow-y: hidden; 
        overflow: auto;}
        .form-control:disabled:focus, .form-control[readonly]:focus{  border: 1px solid #ccc!important; }
        #masterTableDiv{width: 100%}
        input#normal_flag ,input#priority_flag,input#nr_flag
        {
            opacity: 1;
        }
    </style>
    @php
        $NORMAL_FLAG='';
        $PRIORITY_FLAG='';
        $NR_FLAG='';
        $addOrUpdate = 'Add';
        $disable = '';

        $domain_id = '';
        $branchId = '';
        $first_name = '';
        $middle_name = '';
        $last_name = '';
        $roleId = '';
        $normal_flag='';
        $priority_flag='';
        $nr_flag='';
        
        $value = '';
        $rowId = '';
        if(isset($userData) && count($userData) > 0){
            $disable = 'disable';
            $addOrUpdate = 'Update';
            $domain_id = $userData['empldapuserid'];
            $branchId = $userData['empsol'];
            $first_name = $userData['emp_first_name'];
            $middle_name = $userData['emp_middle_name'];
            $last_name = $userData['emp_last_name'];
            $roleId = $userData['role'];
            $rowId = $userData['empldapuserid'];
            $normal_flag=$userData['normal_flag'];
            $priority_flag=$userData['priority_flag'];
            $nr_flag=$userData['nr_flag'];

        }
    @endphp
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
                                    <div class="card-block">
                                        <h4 class="mb-4">{{$addOrUpdate}} User Details (Without HRMS)</h4>
                                        
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Domain ID</label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control ColumnEditField" id='domain_id' name='empldapuserid' maxlength="20" value="{{$domain_id}}" {{$disable}} >
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Branch ID</label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control ColumnEditField" id='branch_id' name='empsol' maxlength="10"  value="{{$branchId}}">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">First Name</label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control ColumnEditField" id='first_name' name='emp_first_name' value="{{$first_name}}" maxlength="20" onkeyup="this.value = this.value.toUpperCase();">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Middle Name</label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control ColumnEditField" id='middle_name' name='emp_middle_name' value="{{$middle_name}}" maxlength="20" onkeyup="this.value = this.value.toUpperCase();">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Last Name</label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control ColumnEditField" id='last_name' name='emp_last_name' value="{{$last_name}}" maxlength="20" onkeyup="this.value = this.value.toUpperCase();">
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Role</label>
                                                    <div class="col-sm-6">
                                                       {!! Form::select('roles names',$rolelists,$roleId,array('class'=>'form-control ColumnEditField rolelist sub-groups','id'=>'rolelists','name'=>'role','placeholder'=>'Select Role ID')) !!}
                                                    </div>
                                                </div>
                                            <div class="uam-flag" id="uam-flag" style="display:none;">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Normal Flag</label>
                                                <div class="col-sm-6">
                                                <input type="checkbox" class="form-cotrol check " name="NORMAL_FLAG" id="normal_flag" value="{{$normal_flag}}" {{($normal_flag == 'Y')? "checked" : ""}} >
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Priority Flag</label>
                                                <div class="col-sm-6">
                                                <input type="checkbox" class="form-cotrol check" name="PRIORITY_FLAG" id="priority_flag"  value="{{$priority_flag}}" {{($priority_flag == 'Y')? "checked" : ""}}>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">NR Flag</label>
                                                <div class="col-sm-6">
                                                <input type="checkbox" class="form-cotrol check" name="NR_FLAG" id="nr_flag" value="{{$nr_flag}}" {{($nr_flag == 'Y')? "checked" : ""}}>
                                                </div>
                                            </div>                                                
                                            </div>
                                        </div>
                                      </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <button type="button" class="btn btn-primary m-b-0 saveColumnData" id={{$rowId}}>Save</button>
                                                <a class="btn btn-primary back-button ml-3" id="" href="{{url('/uam/dashboard')}}">Back</a>
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
        <script  src="{{ asset('custom/js/uam.js') }}"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                addSelect2('rolelist','Role',false);

                var hrms=$('#rolelists').val();              
                if(hrms==3 ||hrms==4){
                    $('#uam-flag').css('display', 'block')
                }
                else{
                    $('#uam-flag').css('display', 'none')
                }
                
            });
        </script>
    @endpush
