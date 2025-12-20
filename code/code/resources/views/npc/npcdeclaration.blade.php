@php
  $npcdeclarations = (array) $npcdeclarations;

  $blade_applicant = $npcdeclarations['blade_id'].'-'.$npcdeclarations['applicant_sequence'];
  $blade_applicant_proof = $npcdeclarations['blade_id'].'_proof-'.$npcdeclarations['applicant_sequence'];
  $npcdeclarationExtraInfo = json_decode($npcdeclarations['dyna_text']);
  if(isset($npcdeclarations['attachment'])){
           $imagePath = $npcdeclarations['attachment'];

        }else{
                $imagePath = "";
        }
   $accountHolders = $accountDetails['no_of_account_holders'];

    if($npcdeclarations['blade_id'] == 'acknowledgement_receipt' || $npcdeclarations['blade_id'] == 'delight_kit_photograph') {
		$class1 = 'col-sm-6';
		$class2 = 'col-sm-12 display-none';
		$class3 = 'custom-col-review col-sm-12';
	}else{
		$class1 = 'row';
		$class2 = 'col-md-4';
		$class3 = 'custom-col-review col-md-4';
	}

    $image_mask_blur = "uploaded-img-ovd";
    $def_blur_image = "style=filter:blur(30px);";
@endphp

@php
	$is_huf = false;
	if($accountDetails['constitution'] == 'NON_IND_HUF'){
		$is_huf = true;
	}     
@endphp

@if(isset($npcdeclarations['declaration_type']))
	@if(($is_review == 1) && !isset($reviewDetails[$blade_applicant]))
		@php
			$checked = "checked";
			$display = "display-none";
			$disabled = 'disabled';
		@endphp
	@else
		@php
			$checked = "";
			$display = "";
			$disabled = '';
		@endphp
	@endif
		<div class="{{$class1}}">

			<div class="{{$class2}} ">
			<div class="details-custcol-row">
				<div class="details-custcol-row-top">
					<div class="detaisl-left d-flex align-items-center">
						{{$npcdeclarations['declaration']}} Documents
						@if(isset($npcdeclarations['declaration_type']))
							<span>YES</span>
						@else
							<span>NO</span>
						@endif
                        <span>
                            @if(isset($qcReviewDetails[$blade_applicant]))
                                <span class="review-comment">
      								<i class="fa fa-exclamation review-exclamation"></i>
                                 {{$qcReviewDetails[$blade_applicant]}}
								</span>
                            @else
                                <i class="fa fa-check"></i>
                            @endif
                        </span>
					</div>
					<span class="lbl padding-8">{{App\Helpers\CommonFunctions::getdynatextforNpcdeclaration($npcdeclarations['declaration_type'],$npcdeclarationExtraInfo)}}</span>

					<!-- div class="detaisl-right">
						<div class=" d-flex flex-row">
							<div class="switch-blck">
								<div class="toggleWrapper">
									<input type="checkbox" name="{{$npcdeclarations['declaration_type']}}_declaration_toggle" class="mobileToggle reviewComments" id="{{$npcdeclarations['declaration_type']}}_declaration_toggle" {{$checked}} {{$disabled}}>
									<label for="zero_approval_declaration_toggle"></label>
								</div>
							</div>
							<div class="comments-blck {{$display}}">
								<input type="text" class="form-control commentsField" id="{{$blade_applicant}}">
								<i title="save" class="fa fa-floppy-o saveComments"></i>
							</div>
							<div class="details-custcol-row-bootm d-flex align-items-center"></div>
						</div>
					</div> -->
				</div>
			</div>
		</div>
		<div class="{{$class3}}">
			@if(isset($npcdeclarations['declaration_type']))
				@if(($is_review == 1) && (!isset($reviewDetails[$blade_applicant_proof])))
					@php
						$checked = "checked";
						$display = "display-none";
						$disabled = 'disabled';
					@endphp
				@else
					@php
						$checked = "";
						$display = "";
						$disabled = '';
					@endphp
				@endif
				<div class="form-group">
					<div class="declaration-div">
						<div class="row" style="margin-bottom: 8px; flex-wrap: nowrap;">
							
							{{-- huf declaration name typo its work for normal/huf both --}}
								@php
									$declaration = $npcdeclarations['declaration'];
									$applicantSequence = $npcdeclarations['applicant_sequence'];
								@endphp

								<h4>
									@if($npcdeclarations['type'] == 'CUSTOMER' && $accountHolders != 1)
										@if($is_huf)
											@if($applicantSequence == 1)
												{{$declaration}} (Karta/Manager)
											@elseif($applicantSequence == 2)
												{{$declaration}} (HUF)
											@endif
										@else
											{{$declaration}} (Applicant - {{$applicantSequence}})
										@endif
									@else
										{{$declaration}}
									@endif
								</h4>

							{{-- end --}}

                    		<button id="rotate" class="rotate col-sm-1" style="margin-left: 356px;width: 30px;height: 26px;"><i class="fa fa-rotate-right"></i></button>
                    	</div>

						<div class="uploaded-img-ovd">
							@if(substr(strtolower($imagePath),-3) == 'pdf')
								<i class="fa fa-file-pdf-o" style="font-size:48px;color:red"></i>
								<a  href="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$imagePath) }}" target="_blank" style="font-size:14px;text-decoration:none;margin-top:14px;">{{$imagePath}}</a>
							@else
							<div class="{{$image_mask_blur}}" {{$def_blur_image}}>
							<img src="{{ asset('/imagesmarkedattachments/'.$formId.'/'.$imagePath) }}" class="img-fluid ovd_image rotate_image">
							</div>
							@endif
						</div>
						<div class="detaisl-right" style="margin-top:15px; width:100%;">
							<div class="detaisl-left d-flex align-items-center">
                                <span>
                                    @if(isset($qcReviewDetails[$blade_applicant_proof]))
                                        <span class="review-comment">
      										<i class="fa fa-exclamation review-exclamation"></i>
                                         {{$qcReviewDetails[$blade_applicant_proof]}}
									</span>
                                    @else
                                        <i class="fa fa-check"></i>
                                    @endif
                                </span>
                            </div>
							<div class=" d-flex flex-row">
								<div class="switch-blck" style="margin-right: 20px;">
									<div class="toggleWrapper">
										<input type="checkbox" name="{{$blade_applicant}}_proof_toggle" class="mobileToggle reviewComments" id="{{$blade_applicant}}_proof_toggle" {{$checked}} {{$disabled}}>
										<label for="{{$blade_applicant}}_proof_toggle"></label>
									</div>
								</div>
								<div class="comments-blck {{$display}}" style="width:100%;">
									<input type="text" class="form-control commentsField" id="{{$blade_applicant_proof}}" name="{{$blade_applicant_proof}}">
									<i title="save" class="fa fa-floppy-o saveComments"></i>
								</div>
								<div class="details-custcol-row-bootm d-flex align-items-center"></div>
							</div>
						</div>
					</div>
				</div>
			@endif
		</div>
	</div>
@endif
