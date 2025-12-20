@php
    $customerOvd = (array) current($customerOvdDetails);

@endphp
<div class="card" id="delight_details">
    <div class="card-block">
        <!-- Row start -->
        <div class="row">
            <div class="col-lg-12">
                <h4 class="sub-title">Delight Details</h4>
                <div class="row">
                    	<div class=" col-sm-12 mb-3" style="border-bottom: 1px solid rgba(204,204,204,0.35);">
			                    <div class="details-custcol row mb-3">
			                        <div class=" col-md-4">
                                        <div class="details-custcol-row-top d-flex">
                                            <div class="detaisl-left d-flex align-items-center text-center" style="width: 100%; text-align: center;">
                                                Kit Sequence Number :
                                                <span >
                                                    {{$delightKit->kit_number}}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                   
                                    <div class=" col-sm-4">
                                        <div class="details-custcol-row-top d-flex">
                                            <div class="detaisl-left align-items-center text-center" style="width: 100%">
                                                Customer ID :
                                                <span>
                                                    {{$delightKit->customer_id}}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
			                        
			                        <div class="col-md-4">
			                            <div class="details-custcol-row-top d-flex">
			                                <div class="detaisl-left align-items-center text-center" style="width: 100%">
			                                    Account Number :
			                                    <span>
			                                        {{$delightKit->account_number}}
			                                    </span>
			                                </div>
			                            </div>
			                        </div>
			                </div>
			            </div>
                        @foreach($declarations as $npcdeclarations)
                        @if($npcdeclarations->blade_id == 'acknowledgement_receipt' || $npcdeclarations->blade_id == 'delight_kit_photograph')
                            @include('npc.npcdeclaration')
                        @else
                        @endif
                        @endforeach
                            
           </div>
        </div>
        <!-- Row end -->
    </div>
</div>
</div>
