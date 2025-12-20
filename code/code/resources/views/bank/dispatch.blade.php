@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #dispatchApplicationsTable{width: 100%!important; }
     a{text-decoration: none !important;}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body">
                    <div class="card">                                            
                        <div class="card-block table-border-style filtergrid">
                            <div class="row">
                                <div class="col-md-12 filter-icon-main">
                                    <a class="filter-close"><i class="fa fa-refresh" aria-hidden="true"></i> Close Filters</a>
                                </div>
                            </div>
                            <div class="row filter mb-3 mt-0">
                                <div class="col-md-3">
                                    {!! Form::select('customer name',$customerNames,null,array('class'=>'form-control customerName',
                                          'id'=>'customerName','name'=>'customerName','placeholder'=>'Select Customer Name',
                                          'onchange'=>'getUserApplications("/bank/dispatchapplications","dispatchApplicationsTable");')) !!}
                                </div>
                                <div class="col-md-3">
                                    <div class="with-icon">
                                        <input type="text" class="form-control date-input" placeholder="Sent Date To" id="sentDate">
                                        <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="switch-toggle switch-3 switch-candy" id="customerType">
                                        <input id="on" name="customer_type" class="customer-type" type="radio" checked="" value="1">
                                        <label for="on" onclick="">ETB</label>
                                    
                                        <input id="na" name="customer_type" class="customer-type" type="radio" checked="checked" value="all">
                                        <label for="na" onclick="">ALL</label>
                                    
                                        <input id="off" name="customer_type" class="customer-type" type="radio" value="2">
                                        <label for="off" onclick="">NTB</label>
                                        <a></a>
                                    </div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <a href="javascript:void(0)" class="btn btn-primary createBatch" data-toggle="modal" data-target="#create-batch">Create Batch</a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-custom" id="dispatchApplicationsTable">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>AOF Number</th>
                                            <th>Name</th>
                                            <th>Account Type</th>
                                            <th>Sent On</th>
                                            <th>Status</th>
                                            <th>NTB / ETB</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>                    
            </div>
            <!-- Page-body end -->
        </div>
        
    </div>
</div>
<div class="modal fade batch_modal" id="create-batch" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display:none;">
                <h4 class="modal-title">Create Batch</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <h1 style="font-size: 19px;">
                    Successfully created batch - <span style="font-size: 20px;color: #9e9e9e;margin-bottom: 0px;" id="batchId"></span>
                </h1>
                <p style="font-size: 16px;color: #9e9e9e;margin-bottom: 0px;">
                    for Application Forms
                    <span class="display-none" id="AOF_numbers"></span>
                </p>
                <div class="table-responsive">
                    <table class="table table-custom" id="accountsListTable">
                        <thead>
                            <tr>
                                <th>AOF Number</th>
                                <th>Name</th>            
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>                
                <input type="text" class="form-control" id="airwaybill_number" name="airwaybill_number" placeholder="Airwaybill Number"><br>
                 
                <div class="form-group row">
                    <div class="col-sm-12">
                        {!! Form::select('courier',$courier,null,array('class'=>'col-sm-12 form-control',
                              'id'=>'courier','name'=>'courier','placeholder'=>'Select Courier Company Name')) !!}
                    </div>
                </div>              
            </div>
            <div class="modal-footer modal-display">
                <button type="button" class="btn btn-primary pull-right waves-effect waves-light saveBatch">Save</button>
                <button type="button" class="btn btn-default pull-right waves-effect" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/batch.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        addSelect2('customerName','Customer Name');
        var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+190;
        $('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
        }).bind('datepicker-change',function(event,obj){
            getUserApplications('/bank/dispatchapplications','dispatchApplicationsTable',tableRemainingHeight);
        });
        getUserApplications('/bank/dispatchapplications','dispatchApplicationsTable',tableRemainingHeight);
        $('body').on('click','#clear-dates',function () {
            $('.date-input').val('');
            getUserApplications('/bank/dispatchapplications','dispatchApplicationsTable',tableRemainingHeight);
        });
    });
</script>
@endpush