@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    .form-control:disabled:focus, .form-control[readonly]:focus{  border: 1px solid #ccc!important; }
    #masterTableDiv{width: 100%}
    .card{height: 650px;overflow-y: auto;}
</style>
@php
    $value = '';
    $rowId = '';
    $disabledField = '';
    if(count($rowDetails) > 0)
    {   
        if(isset($rowDetails['id'])){

           $rowId = $rowDetails['id'];
        }else{

            $rowId = $rowDetails['branch_id'];
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
                                    <div class="row">
                                        @if(count($columns) > 0)
                                            @php
                                                $i = 0;
                                            @endphp
                                            @foreach($columns as $column)
                                                @if($i % 2 == 0)
                                                    <div class="col-sm-6">
                                                @endif
                                                        <div class="form-group row">
                                                            <label class="col-sm-4 col-form-label">{{$column}}</label>
                                                            <div class="col-sm-6">
                                                                @if(count($rowDetails) > 0)
                                                                    @php
                                                                        $value = $rowDetails[strtolower($column)];
                                                                        if(count($ro_columns) > 0 && in_array($column,$ro_columns)){
                                                                            $disabledField = 'disabled';
                                                                        }else{
                                                                            $disabledField = '';
                                                                        }
                                                                    @endphp
                                                                @endif
                                                                   @if($column == 'IS_ACTIVE')
                                                                       @php
                                                                        $is_active_status = ['0'=> 'NO' , '1'=>'YES'];
                                                                       @endphp
                                                                        {!! Form::select($column,$is_active_status,$value,array('class'=>'form-control ColumnEditField','table'=>'master_table','id'=>$column,'name'=>$column)) !!}
                                                                    @else

                                                                        <?php 
                                                                            $toUpper = "";
                                                                            if($column == 'UTR_NUMBER'){
                                                                                $toUpper ="oninput="."this.value=this.value.toUpperCase()";
                                                                            }
                                                                        ?>
                                                                        <input type="text" class="form-control ColumnEditField" id='{{$column}}' name='{{$column}}' {{$toUpper}} value='{{$value}}'  {{$disabledField}}>
                                                                    @endif
                                                            </div>
                                                        </div>
                                                @if($i % 2 == 1)
                                                    </div>
                                                @endif
                                                @php
                                                    $i++;
                                                @endphp
                                            @endforeach
                                        @endif
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
