@extends('layouts.app')
@section('content')
@php
        $branchId = '';
        $branchName = '';
        $clusterId = '';
        $zoneId = '';
        $regionId = '';
        $segmentCode = '';


        $value = '';
        $disableupdate = '';
                                        if($table == 'BRANCH'){

         if($rowId != '')
        {   
            $disableupdate = 'disabled';
            $branchId = $columnDatas['branch_id'];
            $branchName = $columnDatas['branch_name'];
            $clusterId = $columnDatas['cluster_id'];
            $zoneId = $columnDatas['zone_id'];
            $regionId = $columnDatas['region_id'];

            $segmentCode = $columnDatas['seg_code'];
        }
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
                                        @if($table == 'BRANCH')
                                         <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group row">
                                                    <label class="col-sm-4 col-form-label">Branch ID</label>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control ColumnEditField " id='branch_id' name='branch_id' value={{$branchId}} >
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
                                            </div>
                                        </div>
                                        @else
                                    <div class="row">
                                        @foreach($columnDatas as $column => $values)
                                        @if($rowId == '')
                                        @php
                                        	$values = '';
                                        @endphp
                                        @endif
                                        <div class="col-md-5 mt-2">
                                                            <label class="col-sm-4 col-form-label">{{strtoupper($column)}}</label>
                                        </div>
                                        <div class="col-md-3 mt-2">
                             				<input type="text" class="form-control ColumnEditField" id='{{$column}}' name='{{$column}}' value='{{$values}}' style="margin-left: -450px;">
                             			</div>
                                     	@endforeach
                                     </div>
                                                        </div>
                                     </div>
                                        @endif

                                       
                                        <div class="form-group row mx-auto">
                                            <div class="col-sm-12">
                                                <button type="button" class="btn btn-primary m-b-0 saveOaoColumnData" id="{{$rowId}}">Save</button>
                                                <a class="btn btn-primary back-button ml-3" id="adduser" href="{{url('channelid/mastertable')}}">Back</a>
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
<script  src="{{ asset('custom/js/dsa.js') }}"></script>
@endpush