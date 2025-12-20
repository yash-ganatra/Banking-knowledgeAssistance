<div class="row">
                                                    <div class="col-md-3">
                                                        <div class="details-custcol-row col-md-12 {{$funding_source_class}}" id="td_amount_div">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Amount</p>
                                                                    <span class="{{$enable}}">
                                                                        @if(isset($reviewDetails['tenure_amount']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['tenure_amount']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control AddFinancialinfoField" table="risk_classification_details" name="tenure_amount" id="tenure_amount" value="{{$tenure_amount}}" {{$readonly}}>
                                                                     <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="details-custcol-row col-md-12 {{$funding_source_class}}" id="td_amount_div">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Tenure Years</p>
                                                                    <span class="{{$enable}}">
                                                                        @if(isset($reviewDetails['tenure_year']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['tenure_year']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control AddFinancialinfoField" table="risk_classification_details" name="tenure_year" id="tenure_year" value="{{$tenure_year}}" {{$readonly}}>
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="details-custcol-row col-md-12 {{$funding_source_class}}" id="td_amount_div">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Tenure Month</p>
                                                                    <span class="{{$enable}}">
                                                                        @if(isset($reviewDetails['tenure_month']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['tenure_month']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control AddFinancialinfoField" table="risk_classification_details" name="tenure_month" id="tenure_month" value="{{$tenure_month}}" {{$readonly}}>
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="details-custcol-row col-md-12 {{$funding_source_class}}" id="bank_name_div">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Frequency</p>
                                                                    <span class="{{$enable}}">
                                                                        @if(isset($reviewDetails['frequency']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['frequency']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    {!! Form::select('Frequency',$frequencyList,$frequency,array('class'=>'form-control frequency AddFinancialinfoField',
                                                                        'table'=>'customer_ovd_details','id'=>'frequency','name'=>'frequency','placeholder'=>'Select Frequency')) !!}
                                                                    <i title="save" class="fa fa-floppy-o display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>