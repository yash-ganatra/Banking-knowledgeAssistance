@php
    if(isset($tableColumnsFilter)){
        $tableColumnsForfilter = $tableColumnsFilter;
    }
@endphp
@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    .form-control:disabled:focus, .form-control[readonly]:focus{  border: 1px solid #ccc!important; }
    #masterTableDiv{width: 100%!important;}
    .table{width: 100%!important;}
    #export-excel-div{position: absolute;top: 10px;width: 150px;left: 7px;}
    .export-excel{float: right;margin-right: 1%;color:green;cursor: pointer;}
    .btn{
        padding: 8.1px 19px !important;
    }
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
                                <div class="card-block">
                                    <div class="row" id="tableDiv">
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">
                                                    <h4>Select Master</h4>
                                                </label>
                                                <div class="col-sm-6">
                                                    {!! Form::select('Table',$tables,null, array('class'=>'select-css table_name','id'=>'table_name',
                                                            'name'=>'table_name','placeholder'=>'Select Table')) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 display-none" id="addColumnDataDiv">
                                            <div class="form-group row">
                                                <div class="col-sm-3">
                                                    <button type="button" class="btn btn-outline-grey waves-effect" id="addColumnData">Add Data</button>
                                                </div>

                                                <div class="col-sm-5 display-none " id="branch-id-filter" >
                                                   <input type="text" class="form-control" placeholder="Branch ID" name="branch_id" id="branch_id">
                                                </div>

                                                <div class="row col-sm-9" id="filterInputDiv">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row table-responsive" id="masterTableDiv">
                                    </div>
                                    <div class="row" id="export-excel-div">
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
    <script type="text/javascript">
        $(document).ready(function(){
            $("#table_name").select2({placeholder: "Select Table",allowClear: true});

            _tableColumnsForfilter = JSON.parse('<?php echo json_encode($tableColumnsForfilter); ?>');

            var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+260;

            $("body").on("keyup","#branch_id,.filterInputColumn",function(){
                var select = $("#table_name");
                mastertablebyColumn(select);
            });

            

            
        });
    </script>
@endpush
