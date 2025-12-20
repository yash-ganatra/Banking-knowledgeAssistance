<!--Delight Start-->
                    <div class="card delight">
                        <div id="tab3" class="tab-content-cust">
                            <span class="visibility_check" id="visibility_check"></span>
                            <div class="card-block card-block-sign">
                                <h4 class="sub-title">Details As Per Delight Kit</h4>
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4 sub-title">
                                            <div class="detaisl-left d-flex align-content-center">
                                                <p class="lable-cus">Kit Sequence Number</p>
                                            </div>
                                            <div class="details-custcol-row-bootm">
                                                <div class="comments-blck">
                                                    {!! Form::select('Kit Number',$delightKits,$kit_number,array('class'=>'form-control kit_number AddDelightField','table'=>'account_details','id'=>'kit_number','name'=>'kit_number','placeholder'=>''))!!}
                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 sub-title">
                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                <div class="detaisl-left d-flex align-content-center ">
                                                    <p class="lable-cus">Customer ID</p>
                                                </div>
                                            </div>
                                            <div class="details-custcol-row-bootm">
                                                <div class="comments-blck">
                                                    <input type="text" class="form-control AddDelightField" table="customer_ovd_details" id="customer_id" name="customer_id"  value="{{$customer_id}}" oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value = this.value.toUpperCase();" maxlength="9" {{$disabled}}>
                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 sub-title">
                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                <div class="detaisl-left d-flex align-content-center ">
                                                    <p class="lable-cus">Account Number</p>
                                                </div>
                                            </div>
                                            <div class="details-custcol-row-bootm">
                                                <div class="comments-blck">
                                                    <input type="text" class="form-control AddDelightField" table="customer_ovd_details" id="account_number" name="account_number" value="{{$account_number}}"  oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value = this.value.toUpperCase();" {{$disabled}}>
                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                    @if((count($checkdeclaration) !== 0) && (!empty($checkdeclaration)))
                                            @foreach($checkdeclaration as $declaration)
                                                @if($declaration[0]->blade_id == 'acknowledgement_receipt' || $declaration[0]->blade_id == 'delight_kit_photograph')
                                                    @include('bank.schemedeclaration')
                                                @else
                                                @endif
                                        @endforeach
                                    @endif
                                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a href="{{route('addnomineedetails')}}" class="btn btn-outline-grey mr-3">Back</a>
                            <a href="javascript:void(0)" class="btn btn-primary applyDigiSign" id="{{$formId}}">Save and Continue</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>