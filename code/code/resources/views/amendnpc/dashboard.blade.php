@extends('layouts.app')
@section('content')
<style type="text/css">
	body{overflow-y: hidden;}
    #amendnpctable{width: 100%!important; }
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
								<table class="table table-custom" id="amendnpctable">
									<thead>
										<th>Created At</th>
										<th>CRF Number</th>
										<th>CustomerID</th>
										<th>Account Number</th>
										<th>Source</th>
										<th>CRF Status</th>
                                        <th>Action</th>             
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="amendreviewModal" tabindex="-1" role="dialog" aria-labelledby="amendreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <input type="hidden" name="formId" id="amendreviewModalFormId">
        <h5 class="modal-title" id="amendreviewModalLabel">Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <!-- <span aria-hidden="true">&times;</span> -->
        </button>
      </div>
      <div class="modal-body">
        Form is already in review.<!--  Do you want to continue? -->
      </div>
      <input type="hidden" name="formId" id="amendreviewCrf">
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary okToReview" style="display: none;" data-bs-dismiss="modal">Continue</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
@endsection
@push('scripts')


<script>
	var tableHieght = $(".header_navbar").height()+$(".filtergrid").height();
	$(document).ready(function(){
		getAmendNpcDashboard('/amendnpc/amendapplicant','amendnpctable',tableHieght);

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
		getAmendNpcDashboard('/amendnpc/amendapplicant','amendnpctable',tableHieght);
	});

	$("#customerId").on("keyup",function(){
		getAmendNpcDashboard('/amendnpc/amendapplicant','amendnpctable',tableHieght);
	});

	function getAmendNpcDashboard(url,table,tableRemainingHeight){
		var tableObject = [];
		tableObject.data = {};
		tableObject.data['table'] = table;
		tableObject.data['crfNumber'] = $('#crfNumber').val();
		tableObject.data['customerId'] = $('#customerId').val();
		tableObject.url = url;
		datatableAjaxCall(tableObject,tableRemainingHeight);
		return false;
	}

	function amendL1Review(crfNumber){
		$.growl({message:'Please wait ..'},{type:'warning'});
		redirectUrl(crfNumber,'/amendnpc/amendreview');
	}

        $("body").on("click",".amendNpcReview",function(){
            var alreadyreviewObject = [];
            alreadyreviewObject.data = {};
            alreadyreviewObject.url =  '/amendnpc/alreadyamendreview';
            alreadyreviewObject.data['crf_number'] = $(this).attr('id');
            alreadyreviewObject.data['functionName'] = 'AlreadyAmendreviewCallBack';
            crudAjaxCall(alreadyreviewObject);
            return false;
        });

    $("body").on("click",".okToReview",function(){
	    var crfNumber =  $('#amendreviewModalFormId').val();
	    redirectUrl(crfNumber,'/amendnpc/amendreview');
	    return false;
    });


</script>
@endpush