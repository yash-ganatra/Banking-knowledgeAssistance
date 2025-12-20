@php
    $add_proofs = '';
    $id_number = '';
    if($additionalData != ''){
    	// if($additionalData->proofIdData != ''){
	    //     $add_proofs = $additionalData->proof_id;
	    //     $id_number = $additionalData->id_code;
    	// }

    	if($additionalData->comuproofAddData != ''){
    		$add_proofs = $additionalData->comuproofAddData->addproof_id;
	        $id_number = $additionalData->comuproofAddData->addproof_no;
    	}
    }
@endphp

<div class="container" id="amendComuAddProof" style="font-family:Franklin Gothic Book,arial, sans-serif; font-size:14px;padding: 50px;">
        <div class="row">
            <div class="col-md-12">
            <h4>Additional details Communication Address</h4><br>
        </div>
        </div>
		<div class="row">
			<div class="col-md-4">
				<label>Proof of Communication Address</label>

					{!!	Form::select('commuaddid',$addComuProofList,$add_proofs,array('class'=>'form-control',
                    'id'=>'commuaddid','name'=>'proof_of_address','placeholder'=>'Select ID proof','proof_type'=>'id'))!!}
			</div>
			<div class="col-md-4">
				<label>Enter number</label>
				<input class="form-control" type="text" id="commuaddnumber" onkeypress="return /[a-zA-Z0-9]/i.test(event.key)" value="{{$id_number}}" placeholder="Enter select number" name='' />
			</div>
		</div>
</div>
@push('scripts')

@endpush