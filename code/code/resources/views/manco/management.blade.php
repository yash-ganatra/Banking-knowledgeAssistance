@php 
$branchlist = '';
$regionlist = '';

  if(isset($aofcountdetails)){
     $aofdetails = $aofcountdetails;
  }else{
     $aofdetails = array();
  }  
@endphp 

@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('custom/css/management.css') }}">
<style>
.table{
  text-align: center;
}
.table td, .table th{
    font-size: 13px;
  text-align: center;

}

.switch {
  position: relative;
  display: inline-block;
  width: 90px;
  height: 34px;
}

.switch input {display:none;}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #2ab934;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #e28500b0;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2ab934;
}

input:checked + .slider:before {
  -webkit-transform: translateX(55px);
  -ms-transform: translateX(55px);
  transform: translateX(55px);
}

/*------ ADDED CSS ---------*/
.on
{
  display: none;
}

.on, .off
{
  color: white;
  position: absolute;
  transform: translate(-50%,-50%);
  top: 50%;
  left: 50%;
  font-size: 10px;
  font-family: Verdana, sans-serif;
}

input:checked+ .slider .on
{display: block;}

input:checked + .slider .off
{display: none;}

/*--------- END --------*/

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;}
</style>



<div class="main-body">
    <div class="page-wrapper">
    <div class="card1">
        <div class="row filter mb-3 drop-down-top filtergrid">
            <div class="col-md-3">
                  {!! Form::select('groups name',$groups,null,array('class'=>'form-control group','id'=>'groups','name'=>'groups','placeholder'=>'Select group Name')) !!}
            </div>
            <div class="col-md-3 zone-selector display-none">
                  {!! Form::select('zones name',$zonelists,null,array('class'=>'form-control zonelist sub-groups','id'=>'zonelists','name'=>'zonelists','placeholder'=>'Select Zone Name')) !!}
            </div>
            <div class="col-md-3 region-selector display-none">
                  {!! Form::select('regions name',$regionlists,null,array('class'=>'form-control regionlist sub-groups','id'=>'regionlists','name'=>'regionlists','placeholder'=>'Select Region Name')) !!}
            </div>
            <div class="col-md-3 cluster-selector display-none">
                  {!! Form::select('clusters name',$clusterlists,null,array('class'=>'form-control clusterlist sub-groups','id'=>'clusterlists','name'=>'clusterlists','placeholder'=>'Select Cluster Name')) !!}
            </div>
            <div class="col-md-3 ml-auto">
                <div class="with-icon">
                    <input type="text" class="form-control date-input" placeholder="Select Date Range" id="sentDate" autocomplete="off">
                    <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="process-wrap manco-active-step active-step1">
                    <!-- <div class="process-main">
                        
                        <div class="col-4">
                            <div class="process-step-cont manco-breadcrumb" id="health-breadcrumb">
                                <div class="process-step  step-1"></div>
                                <span class="process-label">HEALTH</span>
                            </div>
                        </div> 
                         <div class="col-4">
                            <div class="process-step-cont manco-breadcrumb" id="discrepency-breadcrumb">
                                <div class="process-step  step-2"></div>
                                <span class="process-label">DISCRIPANCY</span>
                            </div>
                        </div>                                
                        
                        <div class="col-4">
                            <div class="process-step-cont manco-breadcrumb" id="next-breadcrumb">
                                <div class="process-step  step-3"></div>
                                <span class="process-label">NEXT</span>
                            </div>
                        </div>
                    </div> -->
                    <div class="row">
                        <label class="mr-2 mt-2">HEALTH</label>
                        <label class="switch">
                         <input type="checkbox" id="togBtn">
                         <div class="slider round">
                          <!--ADDED HTML -->
                          <span class="on" value="0"></span>
                          <span class="off" value="1"></span>
                          <!--END-->
                         </div>
                        </label>
                        <label class="ml-2 mt-2">DIAGNOSIS</label>
                    </div>
                </div>
            </div>
        </div>

            <div class="card-block" id="health_section">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                    <div class="row filter mb-4 mt-0">
                                        <div class="col-md-12 d-flex align-items-center">
                                            <h5>Customer Journey (TAT)</h5>
                                        </div>
                                    </div>
                                    <canvas id="equilzr" width="550" height="300" style="display:inline"></canvas>
                            </div>
                        </div>
                    </div> 

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-block">
                                    <div class="row filter mb-4 mt-0">
                                        <div class="col-md-12 d-flex align-items-center">
                                            <h5>API Responses (TAT)</h5>
                                        </div>
                                    </div>
                                <canvas id="apiequilzr" width="550" height="300" style="display:inline"></canvas>
                            </div>
                        </div>
                    </div> 
					
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-block">
                            <div class="row filter mb-4 mt-0">
                                        <div class="col-md-12 d-flex align-items-center">
                                            <h5>Average TAT</h5>
                                        </div>
                                    </div>
                                <canvas id="avgTat" width="250" height="300" style="display:inline" ></canvas>
                            </div>
                        </div>
                    </div> 

                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-block">
                            <div class="row filter mb-4 mt-0">
                                        <div class="col-md-12 d-flex align-items-center">
                                            <h5>Error Rate (Date Range)</h5>
                                        </div>
                                    </div>
                            <canvas id="errRate" width="250" height="300" style="display:inline" ></canvas>
                            </div>
                        </div>
                    </div> 
					
					<div class="col-md-6">
                    <div class="card">
                    
                        <div class="card-block table-border-style card-block-padding">
            
                            <div class="row filter mt-0 " style="padding:15px;">
                                <div class="col-md-5 d-flex align-items-center">
                                    <h5>FTR (7 Day Window)</h5>
                                </div>

                                <div class="col-md-9 text-right d-flex align-items-center" style='display:none !important;'> 
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                                <select class="form-control">
                                                    <option>Branch</option>
                                                </select>
                                        </div>
                                        <div class="col-md-4">
                                                <select class="form-control">
                                                    <option>Region</option>
                                                </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="Date Range">
                                        </div> 
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-custom">
                                    <thead >
										<tr style='line-height:1em !important; padding:0px !important;'>
                                            <th></th>										
                                            <th colspan='4' style='line-height:1em !important; padding-bottom:3px !important; border-bottom:1px solid;'>Forms Cleared</th>                                            
                                        </tr>
                                        <tr style='line-height:1em !important; padding:0px !important;'>
                                            <th style='line-height:1em !important; padding:2px !important;'>Date</th>
										<!--	<th style='line-height:1em !important; padding:2px !important;'>Total <br>Events</th> -->  
                                            <th style='line-height:1em !important; padding:2px !important;'>Branch<br>(%)</th>
                                            <th style='line-height:1em !important; padding:2px !important;'>L1<br>(%)</th>
                                            <th style='line-height:1em !important; padding:2px !important;'>L2<br>(%)</th>
                                            <th style='line-height:1em !important; padding:2px !important;'>QC<br>(%)</th>
                                        </tr>
                                    </thead>
                                    <tbody id='ftrTable'>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
				
				

                </div>    
            </div>
        <!-- </div> -->

        <!-- <div class="row">-->
                <div class="col-md-6" style='display:none;'>
                    <div class="card">
                    
                        <div class="card-block table-border-style card-block-padding">
            
                            <div class="row filter mt-0" style="padding:15px;">
                                <div class="col-md-2 d-flex align-items-center">
                                    <h5>TAT View</h5>
                                </div>

                                <div class="col-md-10 text-right d-flex align-items-center">
                                    
                                    <!-- <div class="row">
                                        <div class="col-md-4">
                                                <select class="form-control">
                                                    <option>Branch</option>
                                                </select>
                                        </div>
                                        <div class="col-md-4">
                                                <select class="form-control">
                                                    <option>Region</option>
                                                </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="Date Range">
                                        </div> 
                                    </div> -->

                                    <div class="row">
                                        <div class="col-md-5">
                                        <div class="comments-blck">
                                               {!! Form::select('beyond_tat Type',array('1'=>'Within TAT','2'=>'Beyond TAT'),null, array('class'=>'select-css2 col-sm-12 beyond_tat','id'=>'beyond_tat','name'=>'beyond_tat','placeholder'=>'Select')) !!}
                                            </div>
                                        </div>

                                       <div class="col-md-5">
                                        <div class="comments-blck">
                                                {!! Form::select('branch_list',$branchlists,$branchlist,array('class'=>'form-control branch_list',
                                                    'id'=>'branch_list','name'=>'branch_list','placeholder'=>'')) !!}
                                            </div>
                                        </div>
                                        <!-- <div class="col-md-3">
                                            <div class="comments-blck">
                                            {!! Form::select('region_list',$regionlists,$regionlist,array('class'=>'form-control region_list',
                                                    'id'=>'region_list','name'=>'region_list','placeholder'=>'')) !!}
                                            </div>
                                        </div> -->
                                        <div class="col-md-2">
                                            <input type="text" class="form-control date-input" placeholder="Select Date" id="sentDate">
                             <!--    <i class="fa fa-times date-clear" aria-hidden="true" id="clear-dates"></i> -->
                                        </div> 
                                    </div>

                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-custom">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>AOF </th>
                                            <th>L1</th>
                                            <th>L2</th>
                                            <th>AC.Open</th>
                                            <th>Dispatch</th>
                                            <th>Courier</th>
                                            <th>Inward</th>
                                            <th>QC</th>
                                            <th>Audit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                         @foreach($aofdetails as $details)
                                           <tr>
                                                <td>{{$details->submission_date}}</td>
                                                <td>{{$details->branch_submission}}</td>
                                                <td>{{$details->l1}}</td>
                                                <td>{{$details->l2}}</td>
                                                <td>{{$details->account_opened}}</td>
                                                <td>{{$details->dispatch}}</td>
                                                <td>{{$details->courier}}</td>
                                                <td>{{$details->inward}}</td>
                                                <td>{{$details->qc}}</td>
                                                <td>{{$details->auditing}}</td>
                                            </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" style='display:none;'>
                    <div class="card">
                    
                        <div class="card-block table-border-style card-block-padding">
            
                            <div class="row filter mt-0 " style="padding:15px;">
                                <div class="col-md-3 d-flex align-items-center">
                                    <h5>FTR</h5>
                                </div>

                                <div class="col-md-9 text-right d-flex align-items-center">
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                                <select class="form-control">
                                                    <option>Branch</option>
                                                </select>
                                        </div>
                                        <div class="col-md-4">
                                                <select class="form-control">
                                                    <option>Region</option>
                                                </select>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" placeholder="Date Range">
                                        </div> 
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-custom">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
											<th>Total</th>
                                            <th>Branch</th>
                                            <th>L1</th>
                                            <th>L2</th>
                                            <th>QC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{Carbon\Carbon::now()->format('d-M')}}</td>
                                            <td>248</td>	
											<td>50</td>	
                                            <td>40</td>	
                                            <td>30</td>
                                            <td>25</td>	
                                        </tr>
                                        <tr>
                                            <td>{{Carbon\Carbon::now()->subDays(1)->format('d-M')}}</td>
                                            <td>109</td>	
											<td>70</td>	
                                            <td>65</td>	
                                            <td>60</td>
                                            <td>55</td>	
                                        </tr>
                                        <tr>
                                            <td>{{Carbon\Carbon::now()->subDays(2)->format('d-M')}}</td>	
                                            <td>250</td>	
											<td>60</td>	
                                            <td>58</td>	
                                            <td>53</td>
                                            <td>49</td>	
                                        </tr>
                                        <tr>
                                            <td>{{Carbon\Carbon::now()->subDays(3)->format('d-M')}}</td>
                                            <td>204</td>	
											<td>70</td>	
                                            <td>67</td>	
                                            <td>63</td>
                                            <td>58</td>	
                                        </tr>
                                        <tr>
                                            <td>{{Carbon\Carbon::now()->subDays(4)->format('d-M')}}</td>
                                            <td>448</td>	
											<td>80</td>	
                                            <td>75</td>	
                                            <td>70</td>
                                            <td>64</td>	
                                        </tr>
                                        <tr>
                                            <td>{{Carbon\Carbon::now()->subDays(5)->format('d-M')}}</td>
                                            <td>280</td>	
											<td>90</td> 
                                            <td>85</td> 
                                            <td>82</td>
                                            <td>70</td> 
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
		
			<!-- L1, L2, L3, L4 Discrepencies -->
		    <div class="issues_section display-none" id="issues_section">
		     @include('manco.issues')  
            </div>
			

    </div>    
</div>

@endsection

@push('scripts')

<script src="{{ asset('/components/chartjs/Chart.bundle.js') }}"></script>
<script src="{{ asset('/components/chartjs/Chart.js') }}"></script>
<script src="{{ asset('/components/chartjs/patternomaly.js') }}"></script>
<script src="{{ asset('/components/chartjs/Chart.css') }}"></script>

<script>

var _rtData = JSON.parse('<?php echo json_encode($rtData); ?>');
var _rtApiData = JSON.parse('<?php echo json_encode($rtApiData); ?>');

var _rtL1Desc = JSON.parse('<?php echo json_encode($rtL1Data); ?>');
var _rtL2Desc = JSON.parse('<?php echo json_encode($rtL2Data); ?>');


var _rtQCDesc = JSON.parse('<?php echo json_encode($rtQCData); ?>');
var _rtAUDesc = JSON.parse('<?php echo json_encode($rtAUData); ?>');


var _rtFtrData = JSON.parse('<?php echo json_encode($rtFtrData); ?>');

var _rtAvgTat = JSON.parse('<?php echo json_encode($rtAvgTat); ?>');
var _rtErrDetection = JSON.parse('<?php echo json_encode($rtErrDetection); ?>'); 
  


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
    
    addSelect2('clusterlist','Cluster');
    addSelect2('regionlist','Region');
    addSelect2('zonelist','Zone');
    addSelect2('branch_list','Branch');
    addSelect2('group','Group');
    addSelect2('region_list','Region');
    addSelect2('beyond_tat','TAT');
  $("#beyond_tat").val('2').trigger('change');

    $('#sentDate').dateRangePicker({
        startOfWeek: 'monday',
        separator : ' to ',
        format: 'DD-MM-YYYY',
        autoClose: true,
        endDate: new Date(),         
    }).bind('datepicker-change',function(event,obj){
        getchartdata();
       //getUserActivityLogs('/admin/activitylogs','useractivitylogs',tableRemainingHeight);
    });
  //$('#sentDate').datepicker('setDate', '2003-06-03 to 2003-06-05');
  var endDate = new Date();
  var startDate = new Date();
  var day = startDate.getDate() - 6;
  startDate = new Date(startDate.setDate(day))
  //var startDate = new Date(endDate.setDate(7));

  $('#sentDate').data('dateRangePicker').setDateRange(startDate,endDate);
   $('body').on('click','#clear-dates',function () {
        $('.date-input').val('');
        getchartdata();
    });

    $("#togBtn").on('change', function() {
        if ($(this).is(':checked')) {
            switchStatus = $(this).is(':checked');
            $('#issues_section').removeClass('display-none');
            $('#health_section').addClass('display-none');



        }
        else {
           switchStatus = $(this).is(':checked');
                $('#health_section').removeClass('display-none');
                $('#issues_section').addClass('display-none');
        }
    });

    $("body").on("change",'#groups',function(){

        if ($(this).val() == 1) {
            $('.region-selector').removeClass('display-none');
            $('.zone-selector').addClass('display-none');
            $('.cluster-selector').addClass('display-none');
            $('#'+'regionlists').val('').trigger('change.select2');
            
        }else if($(this).val() == 2){
            $('.zone-selector').removeClass('display-none');
            $('.cluster-selector').addClass('display-none');
            $('.region-selector').addClass('display-none');
            $('#'+'zonelists').val('').trigger('change.select2');

        }else{
            $('.cluster-selector').removeClass('display-none');
            $('.zone-selector').addClass('display-none');
            $('.region-selector').addClass('display-none');
            $('#'+'clusterlists').val('').trigger('change.select2');
        }

    });

    $("body").on("change",'.sub-groups',function(){
      getchartdata();
        // var selectedSubGroup = $('#'+selectorId).val();
        // if (selectedSubGroup == '') {
        //   return false;
        // }
        // var selectedGroup = $("#groups").val();

        // var filterObject = [];
        // filterObject.data = {};
        // filterObject.url = '/management/getfilterchartdata';
        // filterObject.data['selectedSubGroup'] = selectedSubGroup;
        // filterObject.data['selectedGroup'] = selectedGroup;
       
        // filterObject.data['functionName'] = 'getfilterchartdataCallBack';

        // crudAjaxCall(filterObject);
        // return false;
    });

    $("body").on("click",'.manco-breadcrumb',function(){
        var selectedPage = $(this).attr('id');
        if (selectedPage == 'discrepency-breadcrumb') {
            $('#issues_section').removeClass('display-none');
            $('#health_section').addClass('display-none');

            $('.manco-active-step').addClass('active-step2');
            $('.manco-active-step').removeClass('active-step1');
            
        }else if(selectedPage == 'health-breadcrumb'){
            $('#issues_section').addClass('display-none');
            $('#health_section').removeClass('display-none');

            $('.manco-active-step').addClass('active-step1');
            $('.manco-active-step').removeClass('active-step2');
        }else{

        }
    });

});
</script>

<script>


/// ---------------DESCRIPENCY--------------------------------------///////

var descChart = [];

function updateDescChart(inputData, outputElem, colorCode){

	var maxItemsToShow = 10;
	var dataArray = [];
	var labelArray = [];
	var x=0;
	for (var label in inputData) {
		x++;
		labelArray.push(label);
		dataArray.push(inputData[label]);		
		if(x==maxItemsToShow) break;
	}
  
    
	var chartObj = document.getElementById(outputElem).getContext('2d');
	var varBarThickness = 10;
 
	var elem=0;
	switch(outputElem){
		case 'l1_desc':
			elem=1; break;
		case 'l2_desc':
			elem=2; break;
		case 'qc_desc':
			elem=3; break;
		case 'au_desc':		
			elem=4; break;
	}
	
	if (typeof(descChart[elem]) != 'undefined' && typeof(descChart[elem])!= 'undefined') {
		descChart[elem].destroy(); 
	}
	
	descChart[elem] = new Chart(chartObj, {
    type: 'horizontalBar',
    data: {
        labels: labelArray,
		indexAxis: 'y',
        datasets: [{
            label: 'Forms',
            data: dataArray,
            backgroundColor: colorCode,
			barThickness: varBarThickness,
			maxBarThickness: 10,
        }]
    },
    options: {
		scales: {
            xAxes: [{
                stacked: true,
				min: 0,
				max: 50, 
				suggestedMax: 50,
				  ticks: {
						min: 0, 
						max: 50,
						beginAtZero: true,
						//stepSize: 5,
						callback: function(value, index, values) {
							if (value % 5 === 0) {
								return value;
							}
						}
					},				
			}],
            yAxes: [{
                stacked: true,
            }]
        }, 
		responsive: false,
		maintainAspectRatio: false,		
		animation: {
              tension: {
                  duration: 1000,  
                  easing: 'linear',
                  from: 1,
                  to: 0,
                  loop: true
              }
        },
    }
});
 
}

/// ---------------EQUALIZERS--------------------------------------///////

function updateEquilizer(rtData='', apirtData=''){  
   // $("#equilzrSpan").html('');
   // $("#equilzrSpan").html('<canvas id="equilzr" width="550" height="300" style="display:inline"></canvas>');
    
  var ctx = document.getElementById('equilzr').getContext('2d');
  var apictx = document.getElementById('apiequilzr').getContext('2d');
  var varBarThickness = 30;


  if(rtData == ''){
     //rtData = [[12, 19, 3, 5, 4, 6, 5, 8, 9], [6, 9, 6, 5, 5, 9, 7, 6, 3], [6, 5, 6, 5, 9, 11, 3, 5, 2]];
     //rtData = [[5, 5, 5, 5, 5, 5, 5, 5, 5], [3, 3, 3, 3, 3, 3, 3, 3, 3], [2, 2, 2, 2, 2, 2, 2, 2, 2]];
  }

  //rtData[0][8] = 4;

  if(apirtData == ''){
     //apirtData = [[2, 1, 2, 3, 1, 2, 1], [1, 1, 1, 2, 2, 2, 1], [1, 1, 0, 1, 0, 0, 0]];
  }

  if (typeof(equiChart)!= 'undefined') {
      equiChart.destroy();
  }

    equiChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['AOF', 'L1', 'L2', 'QC', 'Audit'],
            datasets: [{
                label: 'Complied (0-30m)',
                data: rtData[0],
                backgroundColor: pattern.draw('line', '#8AD879'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
    			hoverBackgroundColor: pattern.draw('line', '#BAD879'),
    		
            },
    		{
                label: 'Border Cases (31-120m)',
                data: rtData[1],
                backgroundColor: pattern.draw('line', '#FA9F42'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
            },
    		{
                label: 'Danger Zone (120m+)',
                data: rtData[2],
                backgroundColor: pattern.draw('line', '#F3533A'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
            }
    		]
        },
        options: {
            scales: {
    			xAxes: [{ stacked: true, 
    					  barPercentage: 1,
    					  gridLines: {
    						display: false
                          }
    					  }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    },
    				gridLines: {
    					display: false
                    },
    				stacked: true,
                }]
            },
    		responsive: false,
    		maintainAspectRatio: false,		
    		animation: {
                  tension: {
                      duration: 1000,
                      easing: 'linear',
                      from: 1,
                      to: 0,
                      loop: true
                  }
            },
        }
    });

    if(typeof(apiequiChart)!= 'undefined') {
      apiequiChart.destroy();
    }
    apiequiChart = new Chart(apictx, {
        type: 'bar',
        data: {
            labels: [ ['PAN','NSDL'], ['PAN','Finacle'], ['ETB','Query'], 'eKYC', ['DEDUPE','QID'], 'CUST_ID', 'ACCT_NO', ['Trans.', 'Processing']],
            datasets: [{
                label: 'Complied (0-2s)',
                data: apirtData[0],
                backgroundColor: pattern.draw('line', '#8AD879'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
    			hoverBackgroundColor: pattern.draw('line', '#BAD879')			
            },
    		{
                label: 'Border Cases (2-5s)',
                data: apirtData[1],
                backgroundColor: pattern.draw('line', '#FA9F42'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
            },
    		{
                label: 'Danger Zone (5s+)',
                data: apirtData[2],
                backgroundColor: pattern.draw('line', '#F3533A'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
            }
    		]
        },
        options: {
    		scales: {
    			xAxes: [{ stacked: true, 
    					  barPercentage: 1,
    					  gridLines: {
    						display: false
                          },
    					  ticks: {
    						fontSize: 10,
    						autoSkip: false,
    						maxRotation: 0,
    						minRotation: 0
    					  },
    					  }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    },
    				gridLines: {
    					display: false
                    },
    				stacked: true,
                }]
            },
    		responsive: false,
    		maintainAspectRatio: false,		
    		animation: {
                  tension: {
                      duration: 1000,
                      easing: 'linear',
                      from: 1,
                      to: 0,
                      loop: true
                  }
            },
        }
    });

}

function  updateFtrTable(_rtFtrData){
		
  $('#ftrTable').html('<span></span>'); 
	for (const [key, value] of Object.entries(_rtFtrData)) {
		//console.log(`${key}: ${value}`);
		var branchToolTip = value['branch_total'] > 0 ? value['branch_fc']+' of '+value['branch_total'] : '';
		var l1ToolTip = value['l1_total'] > 0 ? value['l1_fc']+' of '+value['l1_total'] : '';
		var l2ToolTip = value['l2_total'] > 0 ? value['l2_fc']+' of '+value['l2_total'] : '';
		var qcTip = value['qc_total'] > 0 ? value['qc_fc']+' of '+value['qc_total'] : '';
		var rowHtml = '<tr>'
		    +'<td>'+key+'</td>'
			//+'<td>'+value['count']+'</td>'
			+'<td title="'+branchToolTip+'">'+parseInt(value['branch'])+'</td>'
			+'<td  title="'+l1ToolTip+'">'+parseInt(value['l1'])+'</td>'
			+'<td title="'+l2ToolTip+'">'+parseInt(value['l2'])+'</td>'
			+'<td title="'+qcTip+'">'+parseInt(value['qc'])+'</td>'
			+'</tr>';
		
		$('#ftrTable').append(rowHtml);	
	}
										
}

updateEquilizer();
updateDescChart(_rtL1Desc, 'l1_desc', '#f0d762'); 
updateDescChart(_rtL2Desc, 'l2_desc', '#f9b105'); 
updateDescChart(_rtQCDesc, 'qc_desc', '#e1840e'); 
updateDescChart(_rtAUDesc, 'au_desc', '#b31d15'); 


setTimeout(function(){
	updateEquilizer(_rtData, _rtApiData);
	updateFtrTable(_rtFtrData); 
}, 2500); 

/// Average TAT Radar!
updateAvgTatRadar(_rtAvgTat);

function updateAvgTatRadar(rtAvgTat) {
    var tat = document.getElementById('avgTat').getContext('2d');
    if (typeof(avgTatChart)!= 'undefined') {
      avgTatChart.destroy();
    }
    avgTatChart = new Chart(tat, {
        type: 'radar',
        data: {
            labels: ['Branch', 'L1', 'L2', 'AC.Open', 'QC', 'Audit'],
            datasets: [{
                label: '(Minutes)', 
                data: rtAvgTat,
            }]
    		},
    	options: {
    			responsive: false,
    			maintainAspectRatio: true,		
    			scale: {
    			gridLines: {
    					color: ['green', 'green', 'green', 'orange', 'orange', 'red', ]
    				}
    			},
    		}	
    	
    });
}
		
		
// Error Rate
updateErrorRate(_rtErrDetection);

function updateErrorRate(rtErrDetection) {
    var err = document.getElementById('errRate').getContext('2d');
    if (typeof(erroChart)!= 'undefined') {
      erroChart.destroy();
    }
    erroChart = new Chart(err, {
        type: 'radar',
        data: {
            labels: ['Branch', 'L1', 'L2', 'QC'],
            datasets: [{
                label: '(Percentage)',
                data: rtErrDetection,
    			backgroundColor: 'rgba(65, 1, 1, 0.25)',
            }]
    		},
    	options: {
    			responsive: false,
    			maintainAspectRatio: true,		
    			scale: {
    			gridLines: {
    					color: ['green', 'green', 'green', 'orange', 'orange', 'red', ]
    				},
    			ticks: {
    				suggestedMin: 5,
    				suggestedMax: 10
    			   }
    			},
    		}	
    	
    });
}


function getchartdata() {
    var Group = $("#groups").val();
    if (Group != '') {
      switch (Group) {
        case '1':
          subGroupSlectorId = 'regionlists';
          $('#'+'zonelists').val('').trigger('change.select2');
          $('#'+'clusterlists').val('').trigger('change.select2');
        break;
        case '2':
          subGroupSlectorId = 'zonelists';
          $('#'+'regionlists').val('').trigger('change.select2');
          $('#'+'clusterlists').val('').trigger('change.select2');
        break;
        case '3':
          subGroupSlectorId = 'clusterlists';
          $('#'+'regionlists').val('').trigger('change.select2');
          $('#'+'zonelists').val('').trigger('change.select2');
        break;
        default:
        //$masterTable = 'CLUSTER_ID';
        break;
      }
      //var selectedGroup = $("#groups").val();
      var subGroup = $('#'+subGroupSlectorId).val();
      if (subGroup != '') {
        var selectedGroup = Group;
        var selectedSubGroup = subGroup;
      }
    }

    if(typeof($('#sentDate').val()) != 'undefined'){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
    }
    var filterObject = [];
    filterObject.data = {};
    if(typeof($('#sentDate').val()) != 'undefined'){
        filterObject.data['startDate'] = sentDates[0];
        filterObject.data['endDate'] = sentDates[1];
    }
    filterObject.url = '/management/getfilterchartdata';
    filterObject.data['selectedSubGroup'] = selectedSubGroup;
    filterObject.data['selectedGroup'] = selectedGroup;
   
    filterObject.data['functionName'] = 'getfilterchartdataCallBack';

    crudAjaxCall(filterObject);
    return false;
}
    
</script>
@endpush

