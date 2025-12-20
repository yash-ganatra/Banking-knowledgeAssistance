@extends('layouts.app')
@section('content')

<style>
.table td, .table th{
    font-size: 13px;
}
</style>

<div class="main-body">
<div class="page-wrapper">
        <div class="card1">
            <div class="card-block">
                <div class="row">
                    <div class="col-md-6" style="
    padding-right: 0px;
">
                        <div class="card" style="
    border-radius: 5px 0px 0px 0px;
    margin-bottom: 0px;
">
                            <div class="card-block" style="
">
                                    <div class="row filter mb-4 mt-0">
                                        <div class="col-md-12 d-flex align-items-center">
                                            <h5>Customer Journey</h5>
                                        </div>
                                    </div>
                                <canvas id="equilzr" width="550" height="300" style="display:inline"></canvas>
                            </div>
                        </div>
                    </div> 

                    <div class="col-md-3" style="
    padding-left: 0px;
    padding-right: 0px;
">
                        <div class="card" style="
    border-radius: 0px;
    margin-bottom: 0px;
">
                            <div class="card-block">
                            <div class="row filter mb-4 mt-0">
                                        <div class="col-md-12 d-flex align-items-center">
                                            <h5>Average TAT</h5>
                                        </div>
                                    </div>
                                <canvas id="avgTat" width="250" height="300" style="display:inline"></canvas>
                            </div>
                        </div>
                    </div> 

                    <div class="col-md-3" style="
    padding-left: 0px;
">
                        <div class="card" style="
    border-radius: 0px;
    border-radius: 0px 5px 0px 0px;
    margin-bottom: 0px;
">
                            <div class="card-block">
                            <div class="row filter mb-4 mt-0">
                                        <div class="col-md-12 d-flex align-items-center">
                                            <h5>Error Rate</h5>
                                        </div>
                                    </div>
                            <canvas id="errRate" width="250" height="300" style="display:inline"></canvas>
                            </div>
                        </div>
                    </div> 

                </div>    
            </div>
        </div>

        <div class="row">
                <div class="col-md-6" style="
    padding-right: 0px;
">
                    <div class="card" style="
    border-radius: 0px 0px 0px 5px;
">
                    
                        <div class="card-block table-border-style">
            
                            <div class="row filter mb-4 mt-0">
                                <div class="col-md-3 d-flex align-items-center">
                                    <h5>TAT View</h5>
                                </div>

                                <div class="col-md-9 text-right d-flex align-items-center">
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control">
                                                    <option>Branch</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control">
                                                    <option>Region</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            <input type="text" class="form-control" placeholder="Date Range">
                                            </div>
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
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" style="
    padding-left: 0px;
">
                    <div class="card" style="
    border-radius: 0px 0px 0px 0px;
">
                    
                        <div class="card-block table-border-style">
            
                            <div class="row filter mb-4 mt-0">
                                <div class="col-md-3 d-flex align-items-center">
                                    <h5>FTR</h5>
                                </div>

                                <div class="col-md-9 text-right d-flex align-items-center">
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control">
                                                    <option>Branch</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <select class="form-control">
                                                    <option>Region</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                            <input type="text" class="form-control" placeholder="Date Range">
                                            </div>
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
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>
                                        <tr>
                                            <td>1-Aug</td>	
                                            <td>99</td>	
                                            <td>90</td>	
                                            <td>92</td>
                                            <td>95</td>	
                                            <td>80</td>	
                                            <td>70</td>	
                                            <td>90</td>	
                                            <td>98</td>	
                                            <td>98</td>
                                        </tr>


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

    </div>   
</div>

@endsection

@push('scripts')
<script>
  
var ctx = document.getElementById('equilzr').getContext('2d');
var varBarThickness = 30;
var equiChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['AOF', 'L1', 'L2', 'AC.Open', 'Dispatch', 'Courier', 'Inward', 'QC', 'Audit'],
        datasets: [{
            label: 'TAT Compliance',
            data: [12, 19, 3, 5, 4, 6, 5, 8, 9],
            backgroundColor: pattern.draw('line', '#8AD879'),
			barThickness: varBarThickness,
			maxBarThickness: 50,
			hoverBackgroundColor: pattern.draw('line', '#BAD879'),
			//hoverBorderColor: "rgba(211, 164, 36,1)",
        },
		{
            label: 'Border Cases',
            data: [6, 9, 6, 5, 5, 9, 7, 6, 3],
            backgroundColor: pattern.draw('line', '#FA9F42'),
			barThickness: varBarThickness,
			maxBarThickness: 50,
        },
		{
            label: 'Danger Zone',
            data: [6, 5, 6, 5, 9, 11, 3, 5, 2],
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

/// Average TAT Radar!

var tat = document.getElementById('avgTat').getContext('2d');
var avgTatChart = new Chart(tat, {
    type: 'radar',
    data: {
        labels: ['AOF', 'L1', 'L2', 'AC.Open', 'Dispatch', 'Courier', 'Inward', 'QC', 'Audit'],
        datasets: [{
            label: 'Average TAT',
            data: [12, 10, 3, 15, 4, 6, 5, 18, 9],
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
		
		
// Error Rate


var err = document.getElementById('errRate').getContext('2d');
var avgTatChart = new Chart(err, {
    type: 'radar',
    data: {
        labels: ['AOF', 'L1', 'L2', 'AC.Open', 'Dispatch'],
        datasets: [{
            label: 'Error Rate',
            data: [8, 6, 8, 10, 9],
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

</script>

@endpush

