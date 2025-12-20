
@extends('layouts.app')
@section('content')
<link href="{{ asset('components/chartjs/Chart.css') }}" rel="stylesheet">



<div class="main-body">


    <div class="page-wrapper">
    	<div class="card1">

<div id="nonedisplay" style="overflow: hidden; height: 73.1593px; padding-top: 0px; margin-top: 0px; padding-bottom: 0px; margin-bottom: 10px; ">
		<div class="row accountsgrid top-blcks">
			<!-- order-card start -->                        
			<div class="col-md-3 col-xl-3">
				<div class="card">
					<div class="card-block bdr-l-bluec card-blue">
						<div class="card-block-inn d-flex align-items-center">
							<div class="card-block-img">
								<img src="{{ asset('assets/images/report-blue.svg') }}">							</div>  
							<div class="card-block-con">
								<h5 class="m-b-5">New AOF</h5>
							</div>
							<div class="card-block-count">
								<h2 class="count" id='new_aof_pending_count'></h2>
							</div>
							<div class="circle-img">
								<img src="{{ asset('assets/images/circle-img.png') }}">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-xl-3">
				<div class="card">
					<div class="card-block bdr-l-bluec card-blue">
						<div class="card-block-inn d-flex align-items-center">
							<div class="card-block-img">
								<img src="{{ asset('assets/images/report-black.svg') }}">						
							</div>  
							<div class="card-block-con">
								<h5 class="m-b-5">In-Discrepancy</h5>
							</div>
							<div class="card-block-count">
								<h2 class="count" id='desc_aof_pending_count'></h2>
							</div>
							<div class="circle-img">
								<img src="{{ asset('assets/images/circle-img.png') }}">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-3 col-xl-3">
				<div class="card">
					<div class="card-block bdr-l-green card-green">
						<div class="card-block-inn d-flex align-items-center">
							<div class="card-block-img">
								<img src="{{ asset('assets/images/check-darkgreen.svg') }}">						
							</div>  
							<div class="card-block-con">
								<h6 class="m-b-5">L1</h6>
							</div>
							<div class="card-block-count">
								<h2 class="count" id='l1_pending_count'></h2>
							</div>
							<div class="circle-img">
								<img src="{{ asset('assets/images/circle-img.png') }}">
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-xl-3">
				<div class="card">
					<div class="card-block bdr-l-green card-green">
						<div class="card-block-inn d-flex align-items-center">
							<div class="card-block-img">
								<img src="{{ asset('assets/images/doublecheck-darkgreen.svg') }}">						
							</div>  
							<div class="card-block-con">
								<h6 class="m-b-5">L2</h6>
							</div>
							<div class="card-block-count">
								<h2 class="count" id='l2_pending_count'></h2>
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

			<div class="card-block" id="health_section">
		                <div class="row">
		                    <div class="col-md-6">
		                        <div class="card">
		                            <div class="card-block">
		                                    <div class="row filter mb-4 mt-0">
		                                        <div class="col-md-12 d-flex align-items-center">
		                                            <h5>Processed Today</h5>
		                                        </div>
		                                    </div>
		                                    <canvas id="todays_completion" width="550" height="300" style="display:inline"></canvas>
		                            </div>
		                        </div>
		                    </div> 
		                    <div class="col-md-6">
		                        <div class="card">
		                            <div class="card-block">
		                                    <div class="row filter mb-4 mt-0">
		                                        <div class="col-md-12 d-flex align-items-center">
		                                            <h5>Pending as of Today</h5>
													
		                                        </div>
		                                    </div>
		                                <canvas id="todays_pending" width="550" height="300" style="display:inline"></canvas>
		                            </div>
		                        </div>
		                    </div> 
                   		</div>
              		 </div>
           		</div>
       		</div>
   		</div>
					


@endsection
@push('scripts')

<script src="{{ asset('components/chartjs/Chart.js') }}"></script>
<script src="{{ asset('components/chartjs/Chart.bundle.js') }}"></script>
<script src="{{ asset('components/chartjs/patternomaly.js') }}"></script>


<script>

setTimeout(function(){
	 updateTodaysChart();	
	}, 500);
	  
function updateTodaysChart(){	  

	  var _completionData = JSON.parse('<?php echo json_encode($completionData); ?>');
	  var _pendingData = JSON.parse('<?php echo json_encode($pendingData); ?>');

	  var todayscompletion = document.getElementById('todays_completion').getContext('2d');
	  var todayspending = document.getElementById('todays_pending').getContext('2d');

	  var new_aof_count = document.getElementById('new_aof_pending_count');
	  var desc_aof_count = document.getElementById('desc_aof_pending_count');
	  var l1_count = document.getElementById('l1_pending_count');
	  var l2_count = document.getElementById('l2_pending_count');
	  	  
	  new_aof_count.innerHTML = parseInt(_pendingData[0][0]) + parseInt(_pendingData[0][1]) + parseInt(_pendingData[0][2]);
	  desc_aof_count.innerHTML = parseInt(_pendingData[1][0]) + parseInt(_pendingData[1][1]) + parseInt(_pendingData[1][2]);
	  l1_count.innerHTML = parseInt(_pendingData[2][0]) + parseInt(_pendingData[2][1]) + parseInt(_pendingData[2][2]);
	  l2_count.innerHTML = parseInt(_pendingData[3][0]) + parseInt(_pendingData[3][1]) + parseInt(_pendingData[3][2]);

	  var varBarThickness = 30;
	  
	  console.dir(_completionData);
	  //console.dir(_pendingData);
	
	  // COMPLETION
	  
	  var _greenDone = new Array(7);
	  var _orangeDone = new Array(7);
	  var _redDone = new Array(7);
	  
	  for(i=0; i < _completionData.length; i++){						
			  _greenDone[i]=_completionData[i][0];
			  _orangeDone[i]=_completionData[i][1];
			  _redDone[i]=_completionData[i][2];		  
	  }
	  
	  
		
	console.dir(_greenDone);	console.dir(_orangeDone);	
	
	equiChart = new Chart(todays_completion, {
        type: 'bar',
        data: {
            labels: ['BRANCH', 'L1', 'L2', 'QC', 'Audit'],
            datasets: [{
                label: 'Complied (0-30m)',
                data: _greenDone,
                backgroundColor: pattern.draw('line', '#8AD879'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
    			hoverBackgroundColor: pattern.draw('line', '#BAD879'),
    		
            },
    		{
                label: 'Border Cases (31-120m)',
                data: _orangeDone,
                backgroundColor: pattern.draw('line', '#FA9F42'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
            },
    		{
                label: 'Danger Zone (120m+)',
                data: _redDone,
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



	  // PENDING
	  
	  // $allFields = [ 'BRANCH_NEW', 'BRANCH_DISCREPENT', 'L1', 'L2', 'L3', 'QC', 'AUDIT'];
	  
	  var _greenPending = new Array(7);
	  var _orangePending = new Array(7);
	  var _redPending = new Array(7);
	  
	    for(i=0; i < _pendingData.length; i++){						
			  _greenPending[i]=_pendingData[i][0];
			  _orangePending[i]=_pendingData[i][1];
			  _redPending[i]=_pendingData[i][2];		  
	  }
	//console.dir(_completionData);	console.dir(_green);	console.dir(_orange);	
	
	equiChart = new Chart(todayspending, {
        type: 'bar',
        data: {
            labels: ['AOF_NEW', 'AOF_DESC', 'L1', 'L2', 'L3', 'QC', 'Audit'],
            datasets: [{
                label: 'Complied (0-30m)',
                data: _greenPending,
                backgroundColor: pattern.draw('line', '#8AD879'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
    			hoverBackgroundColor: pattern.draw('line', '#BAD879'),
    		
            },
    		{
                label: 'Border Cases (31-120m)',
                data: _orangePending,
                backgroundColor: pattern.draw('line', '#FA9F42'),
    			barThickness: varBarThickness,
    			maxBarThickness: 50,
            },
    		{
                label: 'Danger Zone (120m+)',
                data: _redPending,
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




}

</script>

