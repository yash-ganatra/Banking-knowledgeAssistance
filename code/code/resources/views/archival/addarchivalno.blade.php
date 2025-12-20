@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #addArchivalTable{width: 100%!important; }
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
                            <input type="hidden" name="formId" id="formId">
                            <div class="table-responsive">
                                <table class="table table-custom" id="addArchivalTable">
                                    <thead>
                                        <tr>
                                            <th>AOF Number</th>
                                            <th>Account Number</th>
                                            <th>Customer ID</th>
                                            <th>Name</th>
                                            <th>Barcode Number</th>
                                            <th>Box Number</th>
                                            <th>Sent On</th>     
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
<div class="modal fade" id="addarchivalno" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Archival Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <label>Barcode Number</label>
                <input type="text" class="form-control" id="archival_ref_one" name="archival_ref_one" placeholder="Barcode Number">
                <br>
                <label>Box Number</label>
                <input type="text" class="form-control" id="archival_ref_two" name="archival_ref_two" placeholder="Box Number">
                <br>             
            </div>
            <div class="modal-footer modal-display">
                <button type="button" class="btn btn-primary pull-right waves-effect waves-light savearchival">
                    Save
                </button>
                <button type="button" class="btn btn-default pull-right waves-effect" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/archival.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        addSelect2('customerName','Customer Name');
        var tableRemainingHeight = $(".header-navbar").height()+250;
        getUserApplications('/archival/archivallist','addArchivalTable',tableRemainingHeight);
         // getUserApplications('/bank/createairbatchid','airListTable',tableRemainingHeight);

    });
</script>
<script type="text/javascript">
    /*document.getElementById("modalPrint").onclick = function () {
    printElement(document.getElementById("printThis"));
}*/

/*function printElement(elem) {
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
}*/
</script>
@endpush