@extends('layouts.app')
@section('content')
<style type="text/css">
	body{overflow-y: hidden;}
    #amendApplication{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;text-align: center;}
    .export-excel{float: right;margin-right: 1%;color:green;cursor: pointer;}
    .export{font-size: 13px;
    font-family: Arial;color:#364FCC}
</style>

<div class="pcoded-content1">
	<div class="pcoded-inner-content1">
		<div class="main-body">
			<div class="page-wrapper">
				<div class="page-body page-body-top">
                <div class="row">
                        <div class="col-md-12 filter-icon-main">
                            <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                            <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                        </div>
                    </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-3">
                            <input type="text" name="crfNumber" class="form-control" placeholder="CRF Number" id="crfNumber">
                        </div>
						<div class="col-md-3">
                            <input type="text" name="customerId" class="form-control" placeholder="Customer ID" id="customerId">
                        </div>
                    </div>
                    <div class="card table-top">
						<div class="card-block table-border-style card-block-padding">
							<div class="table-responsive">
                                    <table class="table table-custom" id="amendApplication">
                                        <thead>
                                           
                                                <th>Created At</th>
                                                <th>Application Id</th>
                                                <th>CRF Number</th>
                                                <th>Customer Id</th>
                                                <th>Account No</th>
                                                <th>CRF Status</th>
                                                <th>Source</th>
                                                <th>Active</th>
                                                <th>Action</th>             
                                           
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
<script  src="{{ asset('custom/js/amend.js') }}"></script>
<script type="text/javascript">

    var tableRemainingHeight = $(".header-navbar").height()+$(".filtergrid").height()+140;
    $(document).ready(function(){
    	getAmendDataApplications('/bank/amendapplication','amendApplication',tableRemainingHeight);

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

    $("#crfNumber").on("keyup",function(){
    	getAmendDataApplications('/bank/amendapplication','amendApplication',tableRemainingHeight);

    });

    $("#customerId").on("keyup",function(){
    	getAmendDataApplications('/bank/amendapplication','amendApplication',tableRemainingHeight);

    });
  </script>
@endpush