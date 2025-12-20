<?php
use App\Helpers\CommonFunctions;

?>
@php
    if (isset($solId) && $solId != '') {
        $solId = $solId;
    }else{
        $solId = '';
    }

    if (isset($kitSchema) && $kitSchema != '') {
        $kitSchema = $kitSchema;
    }else{
        $kitSchema = '';
    }

    if (isset($kitStatus) && $kitStatus != '') {
        $kitStatus = $kitStatus;
    }else{
        $kitStatus = '';
    }
@endphp

@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('public/custom/css/maker-style.css') }}">
<style type="text/css">
    body{overflow-y: hidden;}
    #kitDetailsTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
</style>
<div id="kit-detail-table-div">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                    <div class="row filter mb-3 drop-down-top filtergrid">
                        <div class="col-md-2">
                            {!! Form::select('delightSchemeCodes', $delightSchemeCodes, '', array('class'=>'form-control delightSchemeCodes',
                                    'id'=>'delightSchemeCode','name'=>'delightSchemeCode','placeholder'=>'Select Delight Scheme Codes')) !!}
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="kitNumber" class="form-control" placeholder="Kit No." id="kitNumber">
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="customerID" class="form-control" placeholder="Cust ID" id="customerID">
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="accountID" class="form-control" placeholder="Accnt No." id="accountID">
                        </div>
                        <div class="col-md-2">
                            {!! Form::select('delightKitStatus',$delightKitStatus,'',array('class'=>'form-control delightKitStatus',
                                        'id'=>'delightKitStatus','name'=>'delightKitStatus','placeholder'=>'Select Delight Kit Status')) !!}
                        </div>
                        <div class="col-md-2">
                            <div class="with-icon">
                                <input type="text" class="form-control date-input" placeholder="Date range" id="sentDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary kit-approval-button display-none" data-toggle="modal" data-target="#sendApproval" >approved</button>
                        </div>
                    </div>
                    <div class="card table-top">
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="kitDetailsTable">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>SOL ID</th>
                                            <th>Schema Code</th>
                                            <th>Kit Number</th>
                                            <th>Customer Id</th>
                                            <th>Account Number</th>
                                            <th>Status</th>
                                            <th>Created at</th>
                                            <th>Branch Comment</th>
                                            <th>Approval Comment</th>
                                            <th class="select_all_checkbox_th" data-orderable="false">
                                                <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" class="select_all_checkbox " name="select_all_checkbox">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                            </th>
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
    <!-- Send Modal -->
    <div class="modal fade" id="seekApproval" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="send-approval-modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Send Approval</h3>
                </div>
                <div class="modal-body send-approval-modal-body">
                    <form>
                        <div class="row">
                            <label class="modal-label-headings">Kit Number :</label>
                            <span class="modal-kit-number mb-2 ml-2"></span>
                            <div class="radio-selection">
                                <label class="radio">
                                    <input class="SendApprovalField send-approval" disabled="" type="radio" id="sendApprovalRadio-10" name="sendApprovalRadio" value="10" >
                                    <span class="lbl padding-8">Missing</span>
                                </label>
                                <label class="radio">
                                    <input class="SendApprovalField send-approval" disabled="" type="radio" id="sendApprovalRadio-6" name="sendApprovalRadio" value="12" >
                                    <span class="lbl padding-8">Damaged</span>
                                </label>
                                <label class="radio">
                                    <input class="SendApprovalField send-approval" disabled="" type="radio" id="sendApprovalRadio-9" name="sendApprovalRadio" value="9" >
                                    <span class="lbl padding-8">Destroyed</span>
                                </label>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="comment-box">
                                <label class="modal-label-headings">Comment by Branch :</label>
                                <p id="modal_request_comment"></p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="comment-box">
                                <label class="modal-label-headings">Comment :</label>
                                <textarea type="textarea" class="form-control input-capitalize approved-comment" table="" rows="2" id="approval_comment" name="" onkeyup="this.value = this.value.toUpperCase();" maxlength="100"></textarea>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer indent-modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary approved">Approved</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script  src="{{ asset('public/custom/js/maker.js') }}"></script>
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

            $(".filter-icon").click();
            addSelect2('delightSchemeCodes','Delight Scheme Code');
            addSelect2('delightKitStatus','Delight Kit Status');

            var tableRemainingHeight = $(".header-navbar").height()+$("#menu").height()+280;
            getDKitTable('/checker/checkerkitdetailstable','kitDetailsTable',tableRemainingHeight);
            $('#sentDate').dateRangePicker({
                startOfWeek: 'monday',
                separator : ' to ',
                format: 'DD-MM-YYYY',
                autoClose: true,
            }).bind('datepicker-change',function(event,obj){
                getDKitTable('/checker/checkerkitdetailstable','kitDetailsTable',tableRemainingHeight);
            });
            $("body").on("change",".delightSchemeCodes, .delightKitStatus",function(){
                getDKitTable('/checker/checkerkitdetailstable','kitDetailsTable',tableRemainingHeight);
            });

            $("body").on("keyup","#kitNumber, #customerID, #accountID",function(){
                getDKitTable('/checker/checkerkitdetailstable','kitDetailsTable',tableRemainingHeight);
            });

            $('body').on('click','#clear-dates',function () {
                $('.date-input').val('');
                getDKitTable('/checker/checkerkitdetailstable','kitDetailsTable',tableRemainingHeight);
            });


            $("body").on("click",".kit-checkbox",function(){
                if ($("input:checkbox:checked").length > 1) {
                    $('.kit-approval-button').removeClass('display-none');
                }else{
                    $('.kit-approval-button').addClass('display-none');

                }

            });

        });
    </script>
@endpush
