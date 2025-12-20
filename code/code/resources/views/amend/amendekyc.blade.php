@php
    $id_proofs = '';
    $id_number = '';
    $id_expiryDate = '';
    $id_issuedateProofId = '';
  
    if($additionalData != ''){
        $id_proofs = $additionalData->proofIdData->proof_id;
        $id_number = $additionalData->proofIdData->id_code;
        $id_expiryDate = $additionalData->proofIdData->id_date;
        $id_issuedateProofId = $additionalData->proofIdData->issues_id_date;
    }
@endphp

<div class="container" id="additionekycdetails" style="font-family:Franklin Gothic Book,arial, sans-serif; font-size:14px;padding: 50px;">
        <div class="row">
            <div class="col-md-12">
            <h4>Additional details</h4><br>
        </div>
        </div>
		<div class="row">
			<div class="col-md-3">
				<label>Proof of Identity</label>

					{!!	Form::select('id_proof_list',$idProofList,$id_proofs,array('class'=>'form-control id_proof id_proof_list',
                    'id'=>'proof_of_identity','name'=>'proof_of_identity','placeholder'=>'Select ID proof','proof_type'=>'id'))!!}
			</div>
			<div class="col-md-3">
				<label>Enter number</label>
				<input class="number_of_Id form-control" type="text" id="number_of_indentity" value="{{$id_number}}" placeholder="Enter select number" name='id_proof_card_number' />
			</div>
            <div class="col-md-3 date_proof_id" style="display: none;">
				
					<label>Issues Date</label>
					<input class="form-control" type="text" id="issuedateProofId" value="{{$id_issuedateProofId}}" placeholder="" />
			
			</div>
			<div class="col-md-3 date_proof_id" style="display: none;">
				
					<label>Expiry Date</label>
					<input class="form-control" type="text" id="dateProofId" value="{{$id_expiryDate}}" placeholder="" />
			
				</div>

		</div>
</div>
@push('scripts')
    <script>
        $(document).ready(function(){

            $('#proof_of_identity').on('change',function(){
                var selectText = $('#proof_of_identity :selected').text();
                var selectId = $('#proof_of_identity :selected').val();
                //placeholder
                $('.number_of_Id').attr('placeholder','Enter '+selectText+' number');
                // $('.number_of_Id').attr({id:'number_of_indentity_'+selectId})
                //end placeholder
                $('#dateProofId').val('');
                $('#issuedateProofId').val('');
                $('.number_of_Id').val('');
                checkToSelected(selectId);
                
            });

        var selectId = $('#proof_of_identity :selected').val();
            checkToSelected(selectId);
            
        $('#dateProofId').datepicker({
            clearBtn :true,
            format : "dd-mm-yyyy",
            startDate : "today"
        }).on('change',function(){
            var curr = $(this);
            var idSequence = 1;
            if(curr[0].id != null){
                idSequence = curr[0].id.split('-')[1];
            }
            var dob_date_string = moment(this.value,"DD-MM-YYYY").format("YYYY-MM-DD");
        });

        $('#issuedateProofId').datepicker({
            clearBtn :true,
            format : "dd-mm-yyyy",
            endDate : "today"
        }).on('change',function(){
            var curr = $(this);
            var idSequence = 1;
            if(curr[0].id != null){
                idSequence = curr[0].id.split('-')[1];
            }
            var dob_date_string = moment(this.value,"DD-MM-YYYY").format("YYYY-MM-DD");
        });
    });

function checkToSelected(selectId){
    if(selectId == 1){
                    $('#number_of_indentity').inputmask('9999-9999-9999',{'clearIncomplete':true}).attr({maxlength:'14'},{minlength:'14'});
                    
                }else{

                    $('#number_of_indentity').inputmask('remove');
                }
                

                if(selectId == 2 || selectId == 3){

                    $('.date_proof_id').css('display','block');
                    $('#dateProofId').attr({id:'dateProofId'});
            $('#issuedateProofId').attr({id:'issuedateProofId'});
                  
                }else{

                    $('.date_proof_id').css('display','none');
                    $('#dateProofId').attr({id:'dateProofId'});
            $('#issuedateProofId').attr({id:'issuedateProofId'});

                }
}

var _global_is_review = '';
</script>
<script src="{{ asset('custom/js/ovd_details.js') }}"></script>

@endpush