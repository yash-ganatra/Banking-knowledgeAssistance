@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('custom/css/maker-style.css') }}">
    <div class="maker-indent-section mx-4 my-4">
	    <table class="table table-striped table-hover">
	        <thead class="indent-table-head">
	            <tr>
                    <th scope="col">SR</th>
                    <th scope="col">SCHEME</th>
                    <th scope="col">BATCH</th>
                    <th scope="col">TOTAL INDENTED</th>
                    <th scope="col">RECEIVED</th>
	                <th scope="col">DAMAGED</th>
	                <th scope="col">DESTROYED</th>
	                <th scope="col">MISSING</th>
	                <th scope="col">UTILIZED</th>
	                <th scope="col">AVAILABLE SALES</th>
	                <th scope="col">AVAILABLE BRANCH</th>
	                <th scope="col">TOTAL AVAILABLE</th>
	            </tr>
	        </thead>
	    <tbody>
            @php
                $i = 1;
            @endphp
		        @foreach($delightKitInventoryDetails as $schemeCode => $kitInventory)
		            <tr>
		                <td>{{$i}}</td>
		                <td>
		      	            <a id="{{$schemeCode}}" class="kitDetails" href="javascript:void(0)">{{$schemeCode}}</a>
                        </td>
		                <td>
                            {{$kitInventory['Batch']}}
                        </td>
		                <td>
                            <a id="{{$schemeCode}}" status="DISPATCHED" class="kitDetails" href="javascript:void(0)">{{$kitInventory['Indent']}}</a>
                        </td>
		                <td>
                            <a id="{{$schemeCode}}" status="RECEIVED" class="kitDetails" href="javascript:void(0)">{{$kitInventory['Received']}}</a>
                        </td>
		                <td>
                            <a id="{{$schemeCode}}" status="DAMAGED" class="kitDetails" href="javascript:void(0)">{{$kitInventory['Damaged']}}</a>
                        </td>
		                <td>
                            <a id="{{$schemeCode}}" status="DESTROYED" class="kitDetails" href="javascript:void(0)">{{$kitInventory['Destroyed']}}</a>
                        </td>
		                <td>
                            <a id="{{$schemeCode}}" status="MISSING" class="kitDetails" href="javascript:void(0)">{{$kitInventory['Missing']}}</a>
                        </td>
                        <td>
                            <a id="{{$schemeCode}}" status="UTILIZED" class="kitDetails" href="javascript:void(0)">{{$kitInventory['Utilized']}}</a>
                        </td>
		                <td>
                            <a id="{{$schemeCode}}" status="ALLOCATED" class="kitDetails" href="javascript:void(0)">{{$kitInventory['Allocated']}}</a>
                        </td>
		                <td>
                            <a id="{{$schemeCode}}" status="RECEIVED" class="kitDetails" href="javascript:void(0)">{{$kitInventory['branchAvailable']}}</a>
                        </td>
		                @if($kitInventory['totalAvailable'] <= 10)
		      	            <td id="available-{{$schemeCode}}" class="available" style="color: darkred;">{{$kitInventory['totalAvailable']}}</td>
		                @elseif($kitInventory['totalAvailable'] < 25)
		      	            <td id="available-{{$schemeCode}}" class="available" style="color: darkorange;">{{$kitInventory['totalAvailable']}}</td>
		                @else
		      	            <td id="available-{{$schemeCode}}" class="available">{{$kitInventory['totalAvailable']}}</td>
		                @endif
		            </tr>
                    @php
                      $i++;
                    @endphp
	            @endforeach
	        </tbody>
	    </table>
    </div>
@endsection
@push('scripts')
    <script  src="{{ asset('custom/js/maker.js') }}"></script>
@endpush
