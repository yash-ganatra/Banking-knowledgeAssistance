@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #batchApplicationsTable{width: 100%!important; }
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                    <div class="page-body page-body-top">
                        <div class="row">
                            <div class="col-md-12 filter-icon-main">
                                <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                                <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                            </div>
                        </div>
                        <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                            <div class="col-md-3">
                                <div class="with-icon">
                                    <input type="text" class="form-control" placeholder="Batch Number" name="batch_no" id="batch_no">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="with-icon">
                                    <input type="text" class="form-control" placeholder="Airway Bill Number" name="airway_bill_no" id="airway_bill_no">
                                </div>
                            </div>
                            <div class="col-md-3">
                                {!! Form::select('Courier List',$courierList,null,array('class'=>'form-control courier',
                                      'id'=>'courierList','name'=>'courierList','placeholder'=>'Select Courier')) !!}
                            </div>
                            <div class="col-md-3">
                                <div class="with-icon">
                                    <input type="text" class="form-control" placeholder="Sent Date To" id="sentDate">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="batchApplicationsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Batch No</th>
                                            <th>Airway bill no</th>
                                            <th>Courier Name</th>
                                            <th>Dispatched Date</th>                                           
                                            <th>No of Forms</th>
                                            <th>Action</th>                                           
                                        </tr>
                                    </thead>
                                </table>
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
        addSelect2('courier','Courier');
        // addSelect2('applicationStatus','Application Status');
        getBatchDataApplications();
        $('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
        }).bind('datepicker-change',function(event,obj){
            getBatchDataApplications();
        });
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