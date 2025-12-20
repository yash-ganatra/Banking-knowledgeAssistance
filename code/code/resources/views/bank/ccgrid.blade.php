<!--- Model For DELIGHT Account Details  -->

<!-- Modal -->
@php 
  if(isset($gridDataDetails)){
     $details = $gridDataDetails;
  }else{
     $details = array();
  }  
    
  function isRedRequired($fieldName, $fieldArray){	  
	  if(in_array($fieldName, $fieldArray)){
		  return 'markRed';
	  }else{
		  return 'ok';
	  }
  }
  
@endphp 
<style> 
  .markRed { color: red; }
  .modal_scroll{height: 250px;overflow-y: auto;padding-left: 14px;
    padding-right: 14px;}
  thead tr:nth-child(1) th{background: white; position: sticky;top: 0;z-index: 10; background-color: #364FCC;color: #fff;border: none; 

  }
  .ccgrid_table td, .ccgrid_table th{
    vertical-align: middle !important;
  }
  .transparent_button{
    pointer-events: none;
  }
 a{text-decoration: none !important;}
</style>
<div class="modal fade" id="DelightModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="DelightModalLabel" aria-hidden="true">
  <div class="modal-dialog modal_dialog_delight" role="document">
    <div class="modal-content modal_content_delight">
      <div class="modal-header">
    <h5 class="modal-title" id="DelightModalLabel">Customer Enquiry View</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
          <!-- <span aria-hidden="true">&times;</span> -->
        </button>
      </div>
      <div class="modal-body modal_scroll">
        <!--Table Start -->
		@if(count($details) == 0)
		<div class="container RefreshRestrictMsg">
			<h4>No records found. Please check the Customer ID provided and retry.</h4>
		</div>
		@else
          <table class="table table-bordered table-hover ccgrid_table">
              <thead>
                <tr>
                  <th scope="col">Sr.</th>
                  <th scope="col">Account No</th>
                  <th scope="col">Type</th>
                  <th scope="col">Active</th>
                  <th scope="col">Clear Balance</th>
                  <th scope="col">PAN Card</th>
                  <th scope="col">Applicants</th>
                  <th scope="col">Const</th>
                  <th scope="col">Minor</th>
                  <th scope="col">KYC Status</th>
                  <th scope="col">FATCA</th>
                  <th scope="col">NRE</th>
                  <th scope="col">Freeze / Lien</th>
                  <th scope="col">MOP</th>
                  <th scope="col">Rules</th>
                  <th scope="col">Info</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody style='font-size:0.9em;'>			
					@for($i = 0; $i < count($details); $i++)
						<tr id="{{$i+1}}" style="height: 4em !important">
							<td id="id-{{$i+1}}" class="ccgrid_id">{{$i+1}}</td>
							<td id="actno-{{$i+1}}" class="ccgrid_actno">{{$details[$i]['acctNo']}}</td>
							<td id="satd-{{$i+1}}" class="ccgrid_satd {{isRedRequired('satd',$details[$i]['redFields'])}}">{{$details[$i]['satd']}}</td>
							<td id="active-{{$i+1}}" class="ccgrid_active {{isRedRequired('active',$details[$i]['redFields'])}}">{{$details[$i]['active']}}</td>
							<td id="bal-{{$i+1}}" class="ccgrid_bal {{isRedRequired('bal',$details[$i]['redFields'])}}">{{$details[$i]['bal'] > 0 ? $details[$i]['bal'] : '-'}}</td>
              <td id="pancard-{{$i+1}}" class="ccgrid_pancard {{isRedRequired('pancard',$details[$i]['redFields'])}}">{{$details[$i]['pancard']}}</td>
							
              <td id="applicants-{{$i+1}}" class="ccgrid_applicants {{isRedRequired('applicants',$details[$i]['redFields'])}}">{{$details[$i]['applicants'] > 0 ? $details[$i]['applicants'] : '-'}}</td>
              <td id="const-{{$i+1}}" class="ccgrid_const {{isRedRequired('const',$details[$i]['redFields'])}}">{{$details[$i]['const']}}</td>
							<td id="minor-{{$i+1}}" class="ccgrid_minor {{isRedRequired('minor',$details[$i]['redFields'])}}">{{$details[$i]['minor']}}</td>
							<td id="kyc-{{$i+1}}" class="ccgrid_kyc {{isRedRequired('kyc',$details[$i]['redFields'])}}">{{$details[$i]['kyc']}}</td>
							<td id="fatca-{{$i+1}}" class="ccgrid_fatca {{isRedRequired('fatca',$details[$i]['redFields'])}}">{{$details[$i]['fatca']}}</td>
							<td id="nre-{{$i+1}}" class="ccgrid_nre {{isRedRequired('nre',$details[$i]['redFields'])}}">{{$details[$i]['nre']}}</td>
							<td id="lienfreeze-{{$i+1}}" class="ccgrid_lienfreeze {{isRedRequired('lienfreeze',$details[$i]['redFields'])}}">{{$details[$i]['lienFreeze']}}</td>
							<td id="mop-{{$i+1}}" class="ccgrid_mop {{isRedRequired('mop',$details[$i]['redFields'])}}">{{$details[$i]['mop']}}</td>														
              <td id="rules-{{$i+1}}" class="ccgrid_mop {{isRedRequired('rules',$details[$i]['redFields'])}}">{{$details[$i]['rules']}}</td>                            
							@if($details[$i]['allowDisallow'] == 'N')
								<td id="info-{{$i+1}}" class="ccgrid_info"><i class="fa fa-info" aria-hidden="true" title="{{$details[$i]['comments']}}"></i></td>
								<td id="action-{{$i+1}}" class="ccgrid_action">
                  <button class="btn bg-transparent transparent_button">&nbsp;</button>        
                </td>
							@else
								<td id="info-{{$i+1}}" class="ccgrid_info"></td>
								<td id="action-{{$i+1}}" class="ccgrid_action">
									<button class="btn btn-primary saveetbcc" id="saveetbcc-{{$i+1}}">Select</button>
								</td>
							@endif
              <td id="custdetails-{{$i+1}}" class="ccgrid_custdetails" style='display:none;'>{{json_encode($details[$i])}}</td> 
              <td id="modeofoperation-{{$i+1}}" class="ccgrid_modeofoperation" style='display:none;'>{{$details[$i]['mode_of_operation']}}</td>
              
							<td id="redfields-{{$i+1}}" class="ccgrid_redfields" style='display:none;'>{{json_encode($details[$i]['redFields'])}}</td> 
              <input type="hidden" id="applicantId-{{$i+1}}">
						</tr> 
					@endfor				
              </tbody>
            </table>
        <!--Table End -->
		@endif	
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        @if(count($details) > 0)
			<button type="button" class="btn btn-primary">Save changes</button>
		@endif
      </div>
    </div>
  </div>
</div>