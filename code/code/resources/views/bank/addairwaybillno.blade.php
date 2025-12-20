@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #airwayBillNumberTable{width: 100%!important; }
    #airListTable{width: 100%!important; }
 a{text-decoration: none!important;}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body">
                    <div class="card">                                            
                        <div class="card-block table-border-style">
                            <div class="table-responsive">
                                <table class="table table-custom" id="airwayBillNumberTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Batch No</th>
                                            <th>Airway bill no</th>
                                            <th>Courier Name</th>
                                            <th>Created Batch Date</th>
                                            <th>Print</th>                                           
                                            <th>Action</th>                                           
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
<div id="printThis">
    <div class="modal fade batch_modal" id="print-batch" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Print Batch</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <!-- <span aria-hidden="true">&times;</span> -->
                    </button>
                </div>
                <div class="modal-body" style="padding: 25px;">
                    <h1 style="font-size: 19px;">
                        Batch No - <span style="font-size: 20px;color: #9e9e9e;margin-bottom: 0px;" id="batch_Id"></span>
                    </h1>
                     <h1 style="font-size: 19px;">
                        Airwaybill No - <span style="font-size: 20px;color: #9e9e9e;margin-bottom: 0px;" id="airwaybill_number"></span>
                    </h1>
                    <h1 style="font-size: 19px;">
                        Courier Name - <span style="font-size: 20px;color: #9e9e9e;margin-bottom: 0px;" id="courier"></span>
                    </h1>
                    <div class="table-responsive">
                        <table class="table table-custom" id="airListTable">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default waves-effect" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary  waves-effect waves-light" id="modalPrint">Print</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade batch_modal" id="editairwaybillno" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="display:none;">
                <h4 class="modal-title">Create Batch</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="$('#myModal').modal({'backdrop': 'static'})">
                    <!-- <span aria-hidden="true">&times;</span> -->
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <input type="hidden" id="batchId" name="batchId">
                <h1 style="font-size: 19px;">
                    Modify Details & Submit-
                    <span style="font-size: 20px;color: #9e9e9e;margin-bottom: 0px;" id="batch_id"></span>
                </h1>
                <input type="text" class="form-control" id="airwaybill_no" name="airwaybill_no" placeholder="Airwaybill Number"><br>
                <select id="courierData" class="courierData form-control"></select>              
            </div>
            <div class="modal-footer modal-display">
                <button type="button" class="btn btn-primary pull-right waves-effect waves-light updatedairwaybill" id="save">Submit</button>
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
        var tableRemainingHeight = $(".header-navbar").height()+250;
        getUserApplications('/bank/batchlist','airwayBillNumberTable',tableRemainingHeight);
         // getUserApplications('/bank/createairbatchid','airListTable',tableRemainingHeight);

    });
</script>
<script type="text/javascript">
    document.getElementById("modalPrint").onclick = function () {
    printElement(document.getElementById("printThis"));
}

function printElement(elem) {
    var domClone = elem.cloneNode(true);
    
    var $printSection = document.getElementById("printSection");
    
    if (!$printSection) {
        var $printSection = document.createElement("div");
        $printSection.id = "printSection";
        document.body.appendChild($printSection);
    }
    
    $printSection.innerHTML = "";
    $printSection.appendChild(domClone);
    window.print();
}
</script>
@endpush