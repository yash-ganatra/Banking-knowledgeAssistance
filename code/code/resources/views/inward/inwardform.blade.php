@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #batchFormApplicationsTable{width: 100%!important; }
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
                                <div class="col filter mb-3 mt-0 d-flex justify-content-center">
                                    <div class="col-md-2 text-right mx-auto" style="padding-top: 10px;">
                                        <label>AOF#</label>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="with-icon">
                                            <input type="text" class="form-control" placeholder="AOF Number" id="aof_number">
                                        </div>
                                    </div>
                                    <div class="col-md- 1text-left" style="padding-left:15px">
                                        <button type="button" class="btn btn-primary" id="updateinwardstatus">Inward</button>
                                    </div>
                                    <div class="col-md-2 text-right mx-auto" style="padding-top: 10px;">
                                        <label>Received Airway Bill No</label>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="with-icon">
                                            <input type="text" class="form-control" placeholder="Airway bill no" id="airway_bill_no">
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-left" style="padding-left:15px">
                                        <button type="button" class="btn btn-primary" id="saveairwaybillno">Save</button>
                                    </div>                                    
                                </div>
                                <div class="card table-top">
                                    <input type="hidden" name="batchId" id="batchId" value="{{$batchId}}">
                                    <div class="card-block table-border-style card-block-padding">
                                        <div class="table-responsive">
                                            <table class="table table-custom" id="batchFormApplicationsTable">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Batch No</th>
                                                        <th>Airway bill no</th>
                                                        <th>Received Airway bill no</th>
                                                        <th>Courier Name</th>
                                                        <th>AOF Number</th>
                                                        <th>Status</th>                                           
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <!-- Page-body end -->
            </div>  
        </div>                          
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/inward.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // addSelect2('customerName','Customer Name');
        // addSelect2('applicationStatus','Application Status');
        /*$('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
        }).bind('datepicker-change',function(event,obj){
            getUserDataApplications();
        });*/
        getBatchFormDataApplications();
    });
</script>

<script>
$(document).ready(function(){
  $(".filter-icon").click(function(){
    $(".filtergrid").show();
    $(".filter-icon").hide();
    $(".filter-close").show();
  });
  $(".filter-close").click(function(){
    $(".filtergrid").hide();
    $(".filter-close").hide();
    $(".filter-icon").show();
  });
});
</script>

@endpush