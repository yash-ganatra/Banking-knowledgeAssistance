@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #userApplicationsTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;text-align: center;}
    
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
                                                <h6 class="m-b-20">Term Deposit</h6>
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
                        </div>
                    </div>

                    <div class="row filter mb-3 drop-down-top filtergrid" style="display: none;">
                        <div class="col-md-3">
                            <input type="text" name="aofNumber" class="form-control" placeholder="Aof Number" id="aofNumber">
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('customer name',$customerNames,null,array('class'=>'form-control customerName',
                                  'id'=>'customerName','name'=>'customerName','placeholder'=>'Select Customer Name')) !!}
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
                    <input type="hidden" id="activetab" value="{{$activeTab}}">
                    <div class="card table-top">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="userApplicationsTable">
                                    <thead>
                                        <tr>
                                            <th>AOF Update Date</th>
                                            <th>AOF Number</th>
                                            @if(Session::get('role') == 5 || Session::get('role') == 6)
                                                <th>Customer ID</th>
                                                <th>Account No</th>
                                            @endif
                                            <th>Name</th>
                                            <th>TYPE</th>
                                            <th>Account Type</th>
                                        <!--     <th>AOF Date</th> -->
                                            <th>Status</th>
                                            <th>Sourcing Staff</th>
                                            <th>Dedupe Status</th>
                                            <th>&nbsp;</th>
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

<!-- Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reviewModalLabel">Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <!-- <span aria-hidden="true">&times;</span> -->
        </button>
      </div>
      <div class="modal-body">
        Form is already in review.<!--  Do you want to continue? -->
      </div>
      <input type="hidden" name="formId" id="reviewModalFormId">
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary okToReview" style="display: none;" data-bs-dismiss="modal">Continue</button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>


@endsection
@push('scripts')
<script  src="{{ asset('custom/js/npc.js') }}"></script>
<script type="text/javascript">
    var _checkflow=JSON.parse('<?php echo json_encode($activeTab);?>');
    var _checkNormal = "<?php echo Session::get('normal_flag');?>";
    var _checkPriority = "<?php echo Session::get('priority_flag');?>";
    var _checkNr = "<?php echo Session::get('nr_flag');?>";
	var globalchecktable = '';
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
        addSelect2('aofNumber','aofNumber')
        addSelect2('customerName','Customer Name');
        addSelect2('customerTypes','Customer Type');

        _role = "<?php echo Session::get('role'); ?>";

        $('#sentDate').dateRangePicker({
            startOfWeek: 'monday',
            separator : ' to ',
            format: 'DD-MM-YYYY',
            autoClose: true,
        }).bind('datepicker-change',function(event,obj){
            getUserDataApplications();
        });
        $("#aofNumber").on("keyup",function(){
            if($('#aofNumber').val().length >= 5){
            getUserDataApplications();
            }
        });
        $("body").on("change",".customerName, #customerType",function(){
            getUserDataApplications();
        });

        var tabType1 = 'default';
        if(_checkNormal != 'Y'){
            if(_checkPriority == 'Y'){
                tabType1 = 'PR';
            }else{
                tabType1 = 'NR';
            }
        }
        // getUserDataApplications();
        getnewtabDataApplications(tabType1);
        
        $('body').on('click','#clear-dates',function () {
            $('.date-input').val('');
            getUserDataApplications();
        });
    });

    function newTab(tabType){
        globalchecktable = tabType;
        getnewtabDataApplications(tabType);
        $('#aofNumber').val('');

    }
    
</script>
@endpush