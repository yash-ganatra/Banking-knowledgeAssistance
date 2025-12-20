@extends('layouts.app')
@php
	$schemas = [['1','SB102','B034','50','45','1','15','14','10','24','DCB SHUBH-LABH SB'],
				['2','SB106','B045','40','20','5','18','17','15','32','STAFF - SAVINGS BANK'],
				['3','SB111','B034','65','55','3','20','5','10','15','SB CLASSIC'],
				['4','SB115','B045','90','50','2','30','5','5','10','PRIVILEGE BANKING - HNI'],
				['5','SB118','B034','55','35','2','18','17','15','32','CORPORATE PAYROLL PLUS'],
				['6','SB121','B034','50','20','5','5','2','20','7','KISAN MITRA ACCOUNT'],
				];
@endphp
@section('content')
<div class="maker-indent-section mx-4 my-4">
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
                            <h6 class="m-b-20">Available Sales</h6>
                        </div>
                        <div class="card-block-count">
                            <h2 class="count"></h2>
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
                            <h6 class="m-b-20">Available Branch</h6>
                        </div>
                        <div class="card-block-count">
                            <h2 class="count">{{$kitCounts['availableBranch']}}</h2>
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
                            <h6 class="m-b-20">Utilized</h6>
                        </div>
                        <div class="card-block-count">
                            <h2 class="count">{{$kitCounts['utilized']}}</h2>
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
    <hr>
	<table class="table table-striped table-hover" >
	  <thead class="indent-table-head">
	  	<tr>
	  		<th scope="row" colspan="12">
	  			<span class="schema-name">HYDERABAD BRANCH (SOL ID: 104)</span>
	  			<span class="schema-date">12-JULY-2021</span>
	  		</th>
	  	</tr>
	    <tr>
	      <th scope="col">SR</th>
	      <th scope="col">SCHEME</th>
	      <th scope="col">SCHEME DECRIPTION</th>
	      <th scope="col">AVAILABLE SALES</th>
	      <th scope="col">AVAILABLE BRANCH</th>
	      <th scope="col">TOTAL AVAILABLE</th>
	      <th scope="col">ACTION</th>
	    </tr>
	  </thead>
	  <tbody>
		@foreach($schemas as $schema)
		    <tr>
		      <th scope="row">{{$schema[0]}}</th>

		      <td>
		      	<a href="{{route('makerdashboard')}}">
		      	{{$schema[1]}}
		       </a>
		      </td>
		      <td>{{$schema[10]}}</td>
		      <td>{{$schema[7]}}</td>
		      <td>{{$schema[8]}}</td>
		      @if($schema[9] <= 10)
		      	<td id="available-{{$schema[1]}}" class="available" style="color: darkred;">{{$schema[9]}}</td>
		      @elseif($schema[9] < 25)
		      	<td id="available-{{$schema[1]}}" class="available" style="color: darkorange;">{{$schema[9]}}</td>
		      @else
		      	<td id="available-{{$schema[1]}}" class="available">{{$schema[9]}}</td>
		      @endif
		      <td>
		      	@if($schema[9] < 25)
		      		<button type="button" class="btn btn-outline-success open-indent-modal" data-toggle="modal" data-target="#addIndentModal" id="{{$schema[1]}}">Indent</button>
		      	@else

		      	@endif
		      </td>
		    </tr>
	    @endforeach
	  </tbody>
	</table>
</div>
<!-- Modal -->
<div class="modal fade" id="addIndentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="indent-modal-header">
        <h5 class="modal-title" id="exampleModalLabel">NEW INDENT FOR SCHEME:
        	<span class="schema-code"></span>
        </h5>
      </div>
      <div class="modal-body indent-modal-body">
      	<form>
		  <div class="form-group row">
		    <label for="indent-available" class="indent-modal-label col-sm-6">Available <span class="indent-colon">:</span></label>
		    <div class="col-sm-6">
		      <span class="indent-available"></span>
		    </div>
		  </div>
		  <div class="form-group row">
		    <label for="newRequirement" class="indent-modal-label col-sm-6">New Required
		    	<span class="indent-colon">:</span>
		    </label>
		    <div class="col-sm-6">
		      <input type="number" id="newRequirement" class="form-control" name="newRequirement" min="0" max="200" step="5">
		    </div>
		  </div>
		</form>
      </div>
      <div class="modal-footer indent-modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveIndent">Indent</button>
      </div>
    </div>
  </div>
</div>
@endsection
<style type="text/css">
	#addIndentModal{
		top: 12.5em!important;
	}
	.maker-indent-section .schema-name{
		left: 2.7em;
		position: absolute;
	}
	.maker-indent-section .schema-date{
		right: 4em;
		position: absolute;
	}
	.maker-indent-section tr{
		height: 3.4em!important;
	}
	.indent-table-head th{
		background-color: #566ceb!important;
		color: white;
		border-bottom: 0px!important;
		border-top: 0px!important;
	}
	.maker-indent-section .available{
		font-weight: 800;
	}
	.maker-indent-section .table td, .maker-indent-section .table th{
		vertical-align: middle!important;
	}
	.maker-indent-section th, .maker-indent-section td{
		padding: 0.5em!important;
		text-align: center!important;

	}
	.indent-modal-header{
		background-color: #DCDCEE !important;
		color: black;
		text-align: center;
		padding: 0.5em;
	}
	.indent-modal-body{
		padding: 3em 4em!important;
	}
	.indent-modal-label{
		font-weight: 600;
    	font-size: 1.2em;
	}
	.indent-modal-footer{
		padding: 0.4em!important;
	}
	.btn.open-indent-modal{
		border-color: transparent!important;
		font-weight: 700;
	}
	input[type="number"] {
    -moz-appearance: textfield;
	}
	input[type="number"]:hover,
	input[type="number"]:focus {
	    -moz-appearance: number-input;
	}
	.indent-colon{
		float: right;
	}
</style>
@push('scripts')
<script  src="{{ asset('custom/js/maker.js') }}"></script>
<script type="text/javascript">
	$("body").on("click",".open-indent-modal",function(){
       var schema = $(this).attr('id');
	   $('.schema-code').text(schema);
	   var available = $('#available-'+schema).text();
	   $('.indent-available').text(available);
    });
</script>
@endpush
