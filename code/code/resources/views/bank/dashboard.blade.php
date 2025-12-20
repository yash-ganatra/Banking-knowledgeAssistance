@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #userApplicationsTable{width: 100%!important; }
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
                    <div id="nonedisplay" style="display: none;">
                        <div class="row accountsgrid top-blcks">
                            <!-- order-card start -->                        
                            <div class="col-md-4 col-xl-4">
                                <div class="card">
                                    <div class="card-block bdr-l-bluec card-blue">
                                        <div class="card-block-inn d-flex align-items-center">
                                            <div class="card-block-img">
                                                <img src="{{ asset('assets/images/saving-acoount-icon.svg') }}">
                                            </div>  
                                            <div class="card-block-con">
                                                <h6 class="m-b-20">Saving Accounts</h6>
                                            </div>
                                            <div class="card-block-count">
                                                <h2 class="count">{{$accountsCount['savings']}}</h2>
                                            </div>
                                            <div class="circle-img">
                                                <img src="{{ asset('assets/images/circle-img.png') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-xl-4">
                                <div class="card">
                                    <div class="card-block bdr-l-green card-green">
                                        <div class="card-block-inn d-flex align-items-center">
                                            <div class="card-block-img">
                                                <img src="{{ asset('assets/images/term-deposits-icon.svg') }}">
                                            </div>  
                                            <div class="card-block-con">
                                                <h6 class="m-b-20">Term Deposits</h6>
                                            </div>
                                            <div class="card-block-count">
                                                <h2 class="count">{{$accountsCount['termDeposit']}}</h2>
                                            </div>
                                            <div class="circle-img">
                                                <img src="{{ asset('assets/images/circle-img.png') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-xl-4">
                                <div class="card">
                                    <div class="card-block bdr-l-orange card-orange">
                                        <div class="card-block-inn d-flex align-items-center">
                                            <div class="card-block-img">
                                                <img src="{{ asset('assets/images/current-acoount-icon.svg') }}">
                                            </div>  
                                            <div class="card-block-con">
                                                <h6 class="m-b-20">Current Accounts</h6>
                                            </div>
                                            <div class="card-block-count">
                                                <h2 class="count">{{$accountsCount['current']}}</h2>
                                            </div>
                                            <div class="circle-img">
                                                <img src="{{ asset('assets/images/circle-img.png') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- order-card end -->                                  
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 filter-icon-main">
                            <a class="filter-icon"><i class="fa fa-filter"></i> Filters</a>
                            <a class="filter-close" style="display: none;"><i class="fa fa-times"></i> Close Filters</a>
                            @if(Session::get('role') == "13")
                            <i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="export-all-form-excel" data-placement="top" title="Export Data"><span class="export ml-2">Export Excel</span></i>
                            @endif
                        </div>
                    </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-3">
                        <input type="text" name="aofNumber" class="form-control" placeholder=" Aof Number " id="aofNumber">
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="customerName" class="form-control" placeholder="Customer Name" id="customerName">
                        </div>
                        <div class="col-md-1">
                            <input type="text" name="customerId" class="form-control" placeholder="Customer ID" id="customerId">
                        </div>
                        <div class="col-md-3">
                            @php
                                $customerTypes = ['ETB','NTB','DELIGHT'];
                            @endphp
                            {!! Form::select('customer Types',$customerTypes,null,array('class'=>'form-control customerTypes',
                              'id'=>'customerType','name'=>'customerTypes','placeholder'=>'Select Customer Type')) !!}
                        </div>
                        <div class="col-md-3">
                            <div class="with-icon">
                                <input type="text" class="form-control date-input" placeholder="Sent Date To" id="sentDate">
                                <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="userApplicationsTable">
                                    <thead>
                                        <tr>
                                            <th>AOF Number</th>
                                            <th>Name</th>
                                            <th>TYPE</th>
                                            <th>Account Type</th>
                                            @if(Session::get('role') == "13")
                                            <th>Scheme Code</th> 
                                            @endif
                                            <th>Sent On</th>
                                            <th>Status</th>
                                            <th>Sourcing Staff</th>
                                            <th>HRMS NO</th>
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
<script  src="{{ asset('custom/js/bank.js') }}"></script>

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
        // addSelect2('customerName','Customer Name');
        // addSelect2('applicationStatus','Application Status');
        addSelect2('customerTypes','Customer Type');
        var tableRemainingHeight = $(".header-navbar").height()+$(".accountsgrid").height()/*+$(".filtergrid").height()*/+260;
        $('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
        }).bind('datepicker-change',function(event,obj){
            getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
        });
        // $("body").on("keyup","#customerName, #customerType",function(){
        //     getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
        // });
        $("#aofNumber").on("keyup",function(){
            getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
        });
        $("#customerName").on("keyup",function(){
            if($('#customerName').val().length >= 4){
            getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
            }
        });
        $("#customerId").on("keyup",function(){
            if($('#customerId').val().length >= 9){
        getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
            }
        });

        getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);

        $('body').on('click','#clear-dates',function () {
            $('.date-input').val('');
            getUserApplications('/bank/userapplications','userApplicationsTable',tableRemainingHeight);
        });

          //fetch show entries count
          
            $('#export-all-form-excel').on("click",function(e){
   
                e.preventDefault();

                $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});

                $("#userApplicationsTable").DataTable().page.len( -1 ).draw();

                if($("#userApplicationsTable").DataTable().page.len() == -1){
                    setTimeout(function(){
                        $("#userApplicationsTable").DataTable().button('0').trigger();
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

</script>
@if(Session::get('role') == '13')
    <script>
        setTimeout(function(){
            $.growl({message: "Last Three Month Records"},{type: "success"});
        },2000);
    </script>
@endif
@endpush