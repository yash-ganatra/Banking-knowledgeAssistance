<div class="row">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="sub-title">Call center</h4>
            </div>
            <div class="col-lg-12" {{$readonly_etb_cc}}>
                <div class="radio-selection mb-2">
                    <div class="">
                        
                        
                        <label class="radio">
                            <input type="radio" name="initial_funding_type" class="AddFinancialinfoField" value="3" {{ ($initial_funding_type == 3) ? "checked" : 'checked' }} {{$disabled}}>
                            <span class="lbl padding-8">DCB Account</span>
                        </label>
                        
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        
            <div class="details-custcol-row col-md-4" id="etb_others_div">

                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center ">
                        <p class="lable-cus">Account Number</p>
                        <span class="{{$enable}}">
                            @if(isset($reviewDetails['account_number']))
                            <i class="fa fa-times"></i>
                            {{$reviewDetails['account_number']}}
                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                            @else
                            <i class="fa fa-check"></i>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        @if($is_review == 1)
                        <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" name="account_number" id="direct_account_number" value="{{$cc_etb_details['account_number']}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/gi, '').replace(/(\..*)\./g, '$1');">
                        @elseif($cc_etb_details['etb_cc'] == 'CC')
                         <input type="text" class="form-control AddFinancialinfoField" table="customer_ovd_details" min="1" name="account_number" id="direct_account_number" value="{{$cc_etb_details['account_number']}}" {{$readonly_etb_cc}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1'); ">

                        @endif
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>


            <div class="details-custcol-row col-md-4" id="avaliable_balance_div">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center">
                        <p class="lable-cus">Avaliable Balance</p>
                        <span class="{{$enable}}">
                            @if(isset($reviewDetails['avaliable_balance']))
                            <i class="fa fa-times"></i>
                            {{$reviewDetails['avaliable_balance']}}
                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                            @else
                            <i class="fa fa-check"></i>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        <input type="text" class="form-control direct_avaliable_balance" table="" min="1" name="avaliable_balance" id="avaliable_balance" value="{{$cc_etb_details['account_balance']}}" {{$readonly_etb_cc}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');">
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>


            
            <div class="details-custcol-row col-md-4" id="amount_div">
                <div class="details-custcol-row-top d-flex editColumnDiv">
                    <div class="detaisl-left d-flex align-content-center">
                        <p class="lable-cus">Amount</p>
                        <span class="{{$enable}}">
                            @if(isset($reviewDetails['amount']))
                            <i class="fa fa-times"></i>
                            {{$reviewDetails['amount']}}
                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                            @else
                            <i class="fa fa-check"></i>
                            @endif
                        </span>
                    </div>
                </div>
                <div class="details-custcol-row-bootm">
                    <div class="comments-blck">
                        <input type="text" class="form-control AddFinancialinfoField direct_amount" table="customer_ovd_details" min="1" name="amount" id="amount" value="{{$amount}}" {{$readonly}} oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/(\..*)\./g, '$1');" >
                        <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
</div>
</div>