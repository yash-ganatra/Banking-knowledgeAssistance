@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #oaoApplicationsTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;text-align: center;}
    .export-excel{float: right;margin-right: 1%;color:green;cursor: pointer;}
    .export{font-size: 13px;
    font-family: Arial;color:#364FCC}
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
                            @if(Session::get('role') == "18")
                                <i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="export-all-form-excel" data-placement="top" title="Export Data"><span class="export ml-2">Export Excel</span></i>
                            @endif
                        </div>
                    </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-2">
                        <input type="text" name="aofNumber" class="form-control" placeholder=" Aof Number " id="aofNumber">
                        </div>
                         <div class="col-md-2">
                        <input type="text" name="mobileNumber" class="form-control" placeholder="Mobile Number " id="mobileNumber">
                        </div>
                        <div class="col-md-2">
                            {!! Form::select('customer name',$customerNames,null,array('class'=>'form-control customerName',
                                  'id'=>'customerName','name'=>'customerName','placeholder'=>'Select Customer Name')) !!}
                        </div>
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="oaoApplicationsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>CUSTOMER </br> NAME</th>
                                            <th>PAN/ </br>UPI </br> MISMATCH</th>
                                            <th>AOF </br> NUMBER</th>
                                            <th>MOBILE </br> NUMBER</th>
                                            <th>Q-ID</th>
                                            <th>DEDUPE</th>
                                            <th>PAYMENT</th>
                                            <th>FUND </br>RECEIVED</th>
                                            <th>CUST ID</th>
                                            <th>ACCNT ID</th>
                                           <!--  <th>FREEZE1</th>
                                            <th>FREEZE2</th>
                                            <th>FREEZE3</th> -->
                                            <th>FTR</th>
                                            <th>VKYC </br> LINK</th>
                                            <th>VKYC </br> STATUS</th>
                                            <th>UPDATE</th>         
                                            <th>ACTION</th>             
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
<script  src="{{ asset('custom/js/dsa.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $(".cardhover").hover(function(){
            $("#nonedisplay").slideDown();
            },function(){
            $("#nonedisplay").slideUp(); 
        });
        
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
        addSelect2('aofnumber','aofNumber')
        addSelect2('customerName','Customer Name');
        // addSelect2('applicationStatus','Application Status');
        addSelect2('customerTypes','Customer Type');
        var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+260;
        
        $("body").on("change","#customerName, #customerType",function(){
            getOaoDataApplications('/channelid/oaoapplications','oaoApplicationsTable',tableRemainingHeight);
        });
        $("#aofNumber").on("keyup",function(){
            getOaoDataApplications('/channelid/oaoapplications','oaoApplicationsTable',tableRemainingHeight);
        });
         $("#mobileNumber").on("keyup",function(){
            getOaoDataApplications('/channelid/oaoapplications','oaoApplicationsTable',tableRemainingHeight);
        });
        getOaoDataApplications('/channelid/oaoapplications','oaoApplicationsTable',tableRemainingHeight);

       

          //fetch show entries count
          
            $('#export-all-form-excel').on("click",function(e){
   
                e.preventDefault();

                $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});

                $("#oaoApplicationsTable").DataTable().page.len( -1 ).draw();

                if($("#oaoApplicationsTable").DataTable().page.len() == -1){
                    setTimeout(function(){
                        $("#oaoApplicationsTable").DataTable().button('0').trigger();
                        $.growl({message: "Excel file Generated"},{type: "success"});
                        //$("#export-xls").removeClass("display-none");
                    },8000);
                }
                //to revert the show entries count
                // setTimeout(function(){
                //     $("#"+tableObject.data['table']).DataTable().page.len(datatableLength).draw();
                // },600);
            });

    });

    function showForm(aof_number){
        var formObject = [];
        formObject.data = {};
        formObject.url =  '/bank/formdetails';
        formObject.data['aof_tracking_no'] = aof_number;
        formObject.data['functionName'] = 'FormDetailsCallBack';

        crudAjaxCall(formObject);
        return false;
    }
    
</script>
@endpush