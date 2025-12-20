			
@for($j=0;$j<count($getValue);$j++)
	<br>
	<div class="container-fluid">
		
		<div class="row">
			<div class="col-md-12">
				<span>
					<h4 style="text-transform: uppercase;margin-left: 100px">{{$getValue[$j][0]->type3}} </h4><br>
				</span>
			</div>
		</div>
	</div>
    <div class="row" id="amenddetails-tab-{{$j}}"> 
        <div class="col-lg-12 mx-auto">

			@for($getType=0;$getType<count($getValue[$j]);$getType++)
				@php
					$checked = '';
					$disabled = '';

					if(isset($getValue[$j][$getType]->active) && $getValue[$j][$getType]->active == 'N'){
						$disabled = 'disabled';
					}
					//first is check dynamically checked or disabled if status is red

					if(isset($getValue[$j][$getType]->selected) && $getValue[$j][$getType]->selected == 1){
						$checked = 'checked';
						$disabled = 'disabled';																					
					}

					if($voltMatch != '' && count($currCustomerDetails)>0){
						if(in_array($getValue[$j][$getType]->id, [10,15,5,18,4])){
							$checked = 'checked';
							$disabled = 'disabled';
						}
					}
						
						
					if($getEycData  != ''  && count($currCustomerDetails)>0){
						if(in_array($getValue[$j][$getType]->id, [10,15,5,18,4])){
							$checked = 'checked';
							$disabled = 'disabled';
						}
					}


					if(in_array($getValue[$j][$getType]->id, [10,15,5,18]) && $getVkycStatus != 'Y' && count($currCustomerDetails)>0){
						$checked = 'checked';
						$disabled = 'disabled';
					}
				@endphp
			    <div class="custom-control custom-checkbox col-lg-3 form-check-inline" style="margin-left: 90px;vertical-align: top;">
			    <span>
	     			<input type="checkbox" class="custom-control-input customChecks" name="type" id="customCheck-{{$getValue[$j][$getType]->id}}" {{$checked}} {{$disabled}}> <label style="margin-left:25px">{{$getValue[$j][$getType]->description}}</label>
	     		</span>
	     			
	     		</div>
	     	
		    @endfor             
 			
       </div>
    </div>
	
@endfor


