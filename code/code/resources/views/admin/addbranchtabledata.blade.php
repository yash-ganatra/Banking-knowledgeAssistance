    @extends('layouts.app')
    @section('content')
    <style type="text/css">
        body{overflow-y: hidden;}
        .form-control:disabled:focus, .form-control[readonly]:focus{  border: 1px solid #ccc!important; }
        #masterTableDiv{width: 100%}
    </style>
    @php
        $branchId = '';
        $branchName = '';
        $clusterId = '';
        $zoneId = '';
        $regionId = '';
        $segmentCode = '';
        $is_active = 0;

        $value = '';
        $rowId = '';
        $disableupdate = '';

        if(count($rowDetails) > 0)
        {   
            $disableupdate = 'disabled';
            if(isset($rowDetails['id'])){

               $rowId = $rowDetails['id'];
            }else{

                $rowId = $rowDetails['branch_id'];
            }
            $branchId = $rowDetails['branch_id'];
            $branchName = $rowDetails['branch_name'];
            $clusterId = $rowDetails['cluster_id'];
            $zoneId = $rowDetails['zone_id'];
            $regionId = $rowDetails['region_id'];
            $is_active = $rowDetails['is_active'];
            $segmentCode = $rowDetails['seg_code'];
        }

        $checked = "unchecked";
        if($is_active != 0){
            $checked = "checked";
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
                                        <h4>Add Table Details</h4>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Table</label>
                                                    <div class="col-sm-6">
                                                        <span id="table_name">{{$table}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Branch ID</label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control ColumnEditField " id='branch_id' name='branch_id' value={{$branchId}} {{$disableupdate}}>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Branch Name</label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control ColumnEditField" id='branch_name' name='branch_name' value={{$branchName}}>
                                                    </div>
                                                </div>
                                                <div class="form-group row ">
                                                    <label class="col-sm-4 col-form-label">Cluster</label>
                                                    <div class="col-sm-6">
                                                        <div class="cluster-selector">
                                                              {!! Form::select('clusters name',$clusterlists,$clusterId,array('class'=>'form-control ColumnEditField clusterlist sub-groups','id'=>'cluster_id','name'=>'cluster_id','placeholder'=>'Select Cluster Name')) !!}
                                                        </div>  
                                                    </div>  
                                                </div>
                                                <div class="form-group row ">
                                                    <label class="col-sm-4 col-form-label">Zone</label>
                                                    <div class="col-sm-6">
                                                        <div class="zone-selector">
                                                              {!! Form::select('zones name',$zonelists,$zoneId,array('class'=>'form-control ColumnEditField zonelist sub-groups','id'=>'zone_id','name'=>'zone_id','placeholder'=>'Select Zone Name')) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row ">
                                                    <label class="col-sm-4 col-form-label">Region</label>
                                                    <div class="col-sm-6">
                                                        <div class="region-selector">
                                                              {!! Form::select('regions name',$regionlists,$regionId,array('class'=>'form-control ColumnEditField regionlist sub-groups','id'=>'region_id','name'=>'region_id','placeholder'=>'Select Region Name')) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row ">
                                                    <label class="col-sm-4 col-form-label">Segment Code</label>
                                                    <div class="col-sm-6">
                                                        <div class="region-selector">
                                                              <input type="text" class="form-control ColumnEditField" id='segment_code' name='seg_code' value='{{$segmentCode}}' maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group row ">
                                                    <label class="col-sm-4 col-form-label">Is Active</label>
                                                    <div class="col-sm-6">
                                                        <div class="col-sm-1">
                                                        <input class="app-checkbox ColumnEditField" type="checkbox" name="is_active" id="is_active" {{ $checked }}>
                                            </div>
                                        </div>
                                                </div>

                                            </div>
                                        </div>
                                       
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <button type="button" class="btn btn-primary m-b-0 saveColumnData" id="{{$rowId}}">Save</button>
                                                <a class="btn btn-primary back-button ml-3" id="adduser" href="{{url('/admin/mastertables')}}">Back</a>
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
        <script  src="{{ asset('custom/js/mastertable.js') }}"></script>
    @endpush
