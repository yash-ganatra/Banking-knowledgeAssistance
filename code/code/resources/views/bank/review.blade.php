@extends('layouts.app')
@section('content')
@php
$image_mask_blur = "uploaded-img-ovd";
$def_blur_image = "";
if(isset($is_review)){
if($is_review==1){
    $def_blur_image = "style=filter:blur(30px);";
}
}
@endphp
<div class="dnone-ryt branch-review">
    <div class="pcoded-content1">
        <div class="pcoded-inner-content1">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page-body start -->
                    <div class="page-body">
                        <div class="card">
                            <div class="card-block">
                                <div class="row">
                                    <input type="hidden" id="formId" value="{{$formId}}">
                                    <div class="col-lg-12">
                                        <h4 class="sub-title">Customer onbording details 
                                            @if(isset($reviewDetails['customer_onboarding_details']))
                                                <a href="javascript:void(0)" class="btn btn-danger ml-4">Rejected</a>
                                            @else
                                                <a href="javascript:void(0)" class="btn btn-success ml-4">Accepted</a>
                                            @endif
                                        </h4>
                                        <!-- Row start -->
                                        <div class="proofs-blck">
                                            <div class="row">
                                                <div class="custom-col-review col-md-3">
                                                    <div class="form-group">
                                                        <div class="proof-of-identity" id="pf_type_card">
                                                            <h4>{{ucfirst($customerOvdDetails['pf_type'])}}</h4>
                                                            <div class="uploaded-img-ovd_ bg-white" id="pf_type_div">
                                                                <div class="upload-delete">
                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$customerOvdDetails['pf_type_image']) }}" class="img-fluid ovd_image">
                                                            </div>
                                                            </div>
                                                            <div class="add-document-btn display-none">
                                                                <div class="adb-btn-inn">
                                                                    <button type="button" id="upload_pan_card" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                                                    data-id="pf_type_card" data-class="AddPanDetailsField" data-name="pf_type_image"  data-document="PAN Card" data-target="#upload_proof">
                                                                        <span class="adb-icon">
                                                                            <i class="fa fa-plus-circle"></i>
                                                                        </span>
                                                                        Add PAN
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="custom-col-review proof-of-identity col-md-9">
                                                    <h4>Verify Details</h4>
                                                    <div class="details-custcol">
                                                        <div class="row">
                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                        <p class="lable-cus"> Type of account</p>
                                                                        <span>
                                                                            @if(isset($reviewDetails['account_type']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['account_type']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <input type="text" class="form-control" table="account_details" id="account_type" value="{{$accountDetails['account_type']}}" readonly>
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center">
                                                                        <p class="lable-cus">Number of account holders</p>
                                                                        <span>
                                                                            @if(isset($reviewDetails['no_of_account_holders']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['no_of_account_holders']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <input type="text" class="form-control" table="account_details" id="no_of_account_holders" value="{{$accountDetails['no_of_account_holders']}}" readonly>
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center">
                                                                        <p class="lable-cus">Mode of operation</p>
                                                                        <span>
                                                                            @if(isset($reviewDetails['mode_of_operation']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['mode_of_operation']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <!-- <input type="text" class="form-control" table="account_details" id="mode_of_operation" value="{{$accountDetails['mode_of_operation']}}" readonly> -->
                                                                        {!! Form::select('mode_of_operation',$modeOfOperations,$accountDetails['mode_of_operation'],array('class'=>'form-control mode_of_operation AddAccountDetailsField',
                                                                                'id'=>'mode_of_operation','table'=>'account_details','name'=>'mode_of_operation','placeholder'=>'','readonly')) !!}
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                        <p class="lable-cus">PAN Number</p> 
                                                                        <span>
                                                                            @if(isset($reviewDetails['pancard_no']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['pancard_no']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>                                                   
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="pancard_no" value="{{$customerOvdDetails['pancard_no']}}" readonly>
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                        <p class="lable-cus">DOB</p>
                                                                        <span>
                                                                            @if(isset($reviewDetails['dob']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['dob']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="dob" value="{{Carbon\Carbon::parse($customerOvdDetails['dob'])->format('d-m-Y')}}" readonly>
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(isset($reviewDetails['customer_onboarding_details']))
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="reason-of-rejection">
                                                                    <h6>Reason of Rejection</h6>
                                                                    <p>{{$reviewDetails['customer_onboarding_details']}}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <div class="cta-btns mt-3">
                                                        <a href="#" class="btn btn-danger mr-3" data-toggle="modal" data-target="#default-Modal">Reject</a>
                                                        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#default-Modal">Accept</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Row end -->
                                    </div>  
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <h4 class="sub-title">Proofs 
                                            @if(isset($reviewDetails['ovd_details']))
                                                <a href="javascript:void(0)" class="btn btn-danger ml-4">Rejected</a>
                                            @else
                                                <a href="javascript:void(0)" class="btn btn-success ml-4">Accepted</a>
                                            @endif
                                        </h4>
                                        <!-- Row start -->
                                        <div class="row">
                                            <div class="col-lg-12 col-xl-12">
                                                <!-- Nav tabs -->
                                                <ul class="nav nav-tabs md-tabs tabs-left b-none left-tabs" role="tablist" style="width: 15%; display: inline-block;">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-toggle="tab" href="#proof-of-identity" role="tab">Proof of Identity</a>
                                                        <div class="slide"></div>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-toggle="tab" href="#proof-of-permanent-address" role="tab">Proof of permanent address</a>
                                                        <div class="slide"></div>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-toggle="tab" href="#proof-of-current-address" role="tab">Proof of current address</a>
                                                        <div class="slide"></div>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-toggle="tab" href="#customer-details" role="tab">Customer Details</a>
                                                        <div class="slide"></div>
                                                    </li>
                                                </ul>
                                                <!-- Tab panes -->
                                                <div class="tab-content tabs-left-content card-block" style="width:84%; display: inline-block;">
                                                    <div class="tab-pane active" id="proof-of-identity" role="tabpanel">
                                                        <div class="proofs-blck">
                                                            <div class="row">
                                                                <div class="custom-col-review col-md-3">
                                                                    <div class="form-group">
                                                                        <div class="proof-of-identity" id="{{$customerOvdDetails['proof_of_identity']}}">
                                                                            <h4>{{$customerOvdDetails['proof_of_identity']}} Card</h4>
                                                                            <div class="uploaded-img-ovd_ bg_white" id="upload_id_proof_div">
                                                                                <div class="upload-delete">
                                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$customerOvdDetails['id_proof_image']) }}" class="img-fluid ovd_image">
                                                                            </div>
                                                                            </div>
                                                                            <div class="add-document-btn display-none"> 
                                                                                <div class="adb-btn-inn">
                                                                                    <button type="button" id="upload_aadhar" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                                                                                        data-id="{{$customerOvdDetails['proof_of_identity']}}" data-name="id_proof_image" data-document="aadhar Card" data-target="#upload_proof">
                                                                                        <span class="adb-icon">
                                                                                            <i class="fa fa-plus-circle"></i>
                                                                                        </span>
                                                                                        ADD Document
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="custom-col-review proof-of-identity col-md-9">
                                                                    <h4>Verify Details</h4>
                                                                    <div class="row">
                                                                        <div class="details-custcol-row col-md-6">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center">
                                                                                    <p class="lable-cus">Proof of Identity</p>
                                                                                    <span>
                                                                                        @if(isset($reviewDetails['proof_of_identity']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['proof_of_identity']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="proof_of_identity" value="{{$customerOvdDetails['proof_of_identity']}}" readonly>
                                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="details-custcol-row col-md-6">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center">
                                                                                    <p class="lable-cus">{{$customerOvdDetails['proof_of_identity']}} number</p>
                                                                                    <span>
                                                                                        @if(isset($reviewDetails['id_proof_card_number']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['id_proof_card_number']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="id_proof_card_number" value="{{$customerOvdDetails['id_proof_card_number']}}" readonly>
                                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="details-custcol-row col-md-6">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                                    <p class="lable-cus">Title</p>
                                                                                    <span>
                                                                                        @if(isset($reviewDetails['title']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['title']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <!-- <input type="text" class="form-control" table="customer_ovd_details" id="title" value="{{config('constants.TITLE.'.$customerOvdDetails['title'])}}" readonly> -->
                                                                                    {!! Form::select('title',array('1'=>'Mr.','2'=>'Ms.','3'=>'Mrs.'),$customerOvdDetails['title'],array('class'=>
                                                                                        'form-control title AddOvdDetailsField','table'=>'customer_ovd_details','id'=>'title','name'=>'title','placeholder'=>'')) !!}
                                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="details-custcol-row col-md-6">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                                    <p class="lable-cus">First Name </p>
                                                                                    <span>
                                                                                        @if(isset($reviewDetails['first_name']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['first_name']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="first_name" value="{{$customerOvdDetails['first_name']}}" readonly>
                                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="details-custcol-row col-md-6">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center">
                                                                                    <p class="lable-cus">Middle Name</p>
                                                                                    <span>
                                                                                        @if(isset($reviewDetails['middle_name']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['middle_name']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="middle_name" value="{{$customerOvdDetails['middle_name']}}" readonly>
                                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="details-custcol-row col-md-6">
                                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                                    <p class="lable-cus">Last Name</p>
                                                                                    <span>
                                                                                        @if(isset($reviewDetails['last_name']))
                                                                                            <i class="fa fa-times"></i>
                                                                                            {{$reviewDetails['last_name']}}
                                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                        @else
                                                                                            <i class="fa fa-check"></i>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row-bootm">
                                                                                <div class="comments-blck">
                                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="last_name" value="{{$customerOvdDetails['last_name']}}" readonly>
                                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" id="proof-of-permanent-address" role="tabpanel">
                                                        <div class="proofs-blck">
                                                            <div class="row">
                                                                <div class="custom-col-review col-md-3">
                                                                    <div class="form-group">
                                                                        <div class="proof-of-identity" id="{{strtolower(str_replace(' ','_',$customerOvdDetails['proof_of_address']))}}">
                                                                            <h4>{{$customerOvdDetails['proof_of_address']}} Card</h4>
                                                                            <div class="uploaded-img-ovd_ bg-white" id="ipload_address_proof_div">
                                                                                <div class="upload-delete">
                                                                                    <button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">
                                                                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                                                                    </button>
                                                                                </div>
                                                                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$customerOvdDetails['add_proof_image']) }}" class="img-fluid ovd_image">
                                                                            </div>
                                                                            </div>
                                                                            <div class="add-document-btn display-none"> 
                                                                                <div class="adb-btn-inn">
                                                                                    <button type="button" id="upload_aadhar" class="btn btn-outline-grey waves-effect upload_document" data-toggle="modal" 
                                                                                                        data-id="{{strtolower(str_replace(' ','_',$customerOvdDetails['proof_of_address']))}}" data-name="id_proof_image" data-document="aadhar Card" data-target="#upload_proof">
                                                                                        <span class="adb-icon">
                                                                                            <i class="fa fa-plus-circle"></i>
                                                                                        </span>
                                                                                        ADD Document
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="custom-col-review proof-of-identity col-md-9">
                                                                    <h4>Verify Details</h4>
                                                                        <div class="row">
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center">
                                                                                        <p class="lable-cus">Proof of Permanent Address</p>
                                                                                        <span>
                                                                                            @if(isset($reviewDetails['proof_of_address']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['proof_of_address']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="proof_of_address" value="{{$customerOvdDetails['proof_of_address']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center">
                                                                                        <p class="lable-cus">{{$customerOvdDetails['proof_of_address']}} number</p>
                                                                                        <span>
                                                                                            @if(isset($reviewDetails['add_proof_card_number']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['add_proof_card_number']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="add_proof_card_number" value="{{$customerOvdDetails['add_proof_card_number']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center">
                                                                                        <p class="lable-cus">Address line1</p>
                                                                                        <span>
                                                                                            @if(isset($reviewDetails['per_address_line1']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['per_address_line1']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="per_address_line1" value="{{$customerOvdDetails['per_address_line1']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">Address line2</p>
                                                                                        <span>
                                                                                            @if(isset($reviewDetails['per_address_line2']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['per_address_line2']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="per_address_line2" value="{{$customerOvdDetails['per_address_line2']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">State</p>
                                                                                        <span>
                                                                                            @if(isset($reviewDetails['per_state']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['per_state']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="per_state" value="{{$customerOvdDetails['per_state']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">City</p>
                                                                                        <span>
                                                                                            @if(isset($reviewDetails['per_city']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['per_city']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="per_city" value="{{$customerOvdDetails['per_city']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">Pincode</p>
                                                                                        <span>
                                                                                            @if(isset($reviewDetails['per_pincode']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['per_pincode']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="per_pincode" value="{{$customerOvdDetails['per_pincode']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>               
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="proof-of-current-address" role="tabpanel">
                                                            <div class="proofs-blck">
                                                                <div class="row">
                                                                    <div class="custom-col-review col-md-3">
                                                                        <div class="form-group">
                                                                            <div class="proof-of-identity">
                                                                                <h4>{{$customerOvdDetails['proof_of_current_address']}} Card</h4>
                                                                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                    <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$customerOvdDetails['current_add_proof_image']) }}" class="img-fluid ovd_image">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="custom-col-review proof-of-identity col-md-9">
                                                                        <h4>Verify Details</h4>
                                                                            <div class="row">
                                                                                <div class="details-custcol-row col-md-6">
                                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                        <div class="detaisl-left d-flex align-content-center">
                                                                                            <p class="lable-cus">Proof of Current Address</p>
                                                                                            <span>
                                                                                                @if(isset($reviewDetails['proof_of_current_address']))
                                                                                                    <i class="fa fa-times"></i>
                                                                                                    {{$reviewDetails['proof_of_current_address']}}
                                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                                @else
                                                                                                    <i class="fa fa-check"></i>
                                                                                                @endif
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="details-custcol-row-bootm">
                                                                                        <div class="comments-blck">
                                                                                            <input type="text" class="form-control" table="customer_ovd_details" id="proof_of_current_address" value="{{$customerOvdDetails['proof_of_current_address']}}" readonly>
                                                                                            <i class="fa fa-check display-none updateColumn"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row col-md-6">
                                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                        <div class="detaisl-left d-flex align-content-center">
                                                                                            <p class="lable-cus">{{$customerOvdDetails['proof_of_current_address']}} number</p>
                                                                                            <span>
                                                                                                @if(isset($reviewDetails['current_add_proof_card_number']))
                                                                                                    <i class="fa fa-times"></i>
                                                                                                    {{$reviewDetails['current_add_proof_card_number']}}
                                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                                @else
                                                                                                    <i class="fa fa-check"></i>
                                                                                                @endif
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="details-custcol-row-bootm">
                                                                                        <div class="comments-blck">
                                                                                            <input type="text" class="form-control" table="customer_ovd_details" id="current_add_proof_card_number" value="{{$customerOvdDetails['current_add_proof_card_number']}}" readonly>
                                                                                            <i class="fa fa-check display-none updateColumn"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row col-md-6">
                                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                                            <p class="lable-cus">Address line1</p>
                                                                                            <span>
                                                                                                @if(isset($reviewDetails['current_address_line1']))
                                                                                                    <i class="fa fa-times"></i>
                                                                                                    {{$reviewDetails['current_address_line1']}}
                                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                                @else
                                                                                                    <i class="fa fa-check"></i>
                                                                                                @endif
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="details-custcol-row-bootm">
                                                                                        <div class="comments-blck">
                                                                                            <input type="text" class="form-control" table="customer_ovd_details" id="current_address_line1" value="{{$customerOvdDetails['current_address_line1']}}" readonly>
                                                                                            <i class="fa fa-check display-none updateColumn"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row col-md-6">
                                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                                            <p class="lable-cus">Address line2</p>
                                                                                            <span>
                                                                                                @if(isset($reviewDetails['current_address_line2']))
                                                                                                    <i class="fa fa-times"></i>
                                                                                                    {{$reviewDetails['current_address_line2']}}
                                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                                @else
                                                                                                    <i class="fa fa-check"></i>
                                                                                                @endif
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="details-custcol-row-bootm">
                                                                                        <div class="comments-blck">
                                                                                            <input type="text" class="form-control" table="customer_ovd_details" id="current_address_line2" value="{{$customerOvdDetails['current_address_line2']}}" readonly>
                                                                                            <i class="fa fa-check display-none updateColumn"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row col-md-6">
                                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                                            <p class="lable-cus">State</p>
                                                                                            <span>
                                                                                                @if(isset($reviewDetails['current_state']))
                                                                                                    <i class="fa fa-times"></i>
                                                                                                    {{$reviewDetails['current_state']}}
                                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                                @else
                                                                                                    <i class="fa fa-check"></i>
                                                                                                @endif
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="details-custcol-row-bootm">
                                                                                        <div class="comments-blck">
                                                                                            <input type="text" class="form-control" table="customer_ovd_details" id="current_state" value="{{$customerOvdDetails['current_state']}}" readonly>
                                                                                            <i class="fa fa-check display-none updateColumn"></i>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row col-md-6">
                                                                                    <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                        <div class="detaisl-left d-flex align-content-center ">
                                                                                            <p class="lable-cus">City</p>
                                                                                            <span>
                                                                                                @if(isset($reviewDetails['current_city']))
                                                                                                    <i class="fa fa-times"></i>
                                                                                                    {{$reviewDetails['current_city']}}
                                                                                                    <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                                @else
                                                                                                    <i class="fa fa-check"></i>
                                                                                                @endif
                                                                                            </span>
                                                                                        </div>                                                 
                                                                                    </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="current_city" value="{{$customerOvdDetails['current_city']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="details-custcol-row col-md-6">
                                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                                        <p class="lable-cus">Pincode</p>
                                                                                        <span>
                                                                                            @if(isset($reviewDetails['current_pincode']))
                                                                                                <i class="fa fa-times"></i>
                                                                                                {{$reviewDetails['current_pincode']}}
                                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                                            @else
                                                                                                <i class="fa fa-check"></i>
                                                                                            @endif
                                                                                        </span>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="details-custcol-row-bootm">
                                                                                    <div class="comments-blck">
                                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="current_pincode" value="{{$customerOvdDetails['current_pincode']}}" readonly>
                                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>               
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="customer-details" role="tabpanel">
                                                            <div class="proofs-blck">
                                                                <div class="row">
                                                                    <div class="custom-col-review col-md-3">
                                                                        <div class="form-group">
                                                                            <div class="proof-of-identity">
                                                                                <h4>Customer Photo</h4>
                                                                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                    <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$customerOvdDetails['customer_image']) }}" class="img-fluid ovd_image">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="custom-col-review col-md-3">
                                                                        <div class="form-group">
                                                                            <div class="proof-of-identity">
                                                                                <h4>Cusdtomer Signature</h4>
                                                                                <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                                    <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$customerOvdDetails['customer_signature']) }}" class="img-fluid ovd_image">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>     
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(isset($reviewDetails['ovd_details']))
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="reason-of-rejection">
                                                                        <h6>Reason of Rejection</h6>
                                                                        <p>{{$reviewDetails['ovd_details']}}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 text-right">
                                                            <div class="cta-btns">
                                                                <a href="#" class="btn btn-danger mr-3" data-toggle="modal" data-target="#default-Modal">Reject</a>
                                                                <a href="#" class="btn btn-success" data-toggle="modal" data-target="#default-Modal">Accept</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Row end -->
                                        </div>  
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-block">
                                    <!-- Row start -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <h4 class="sub-title">Sources of Fund
                                                @if(isset($reviewDetails['intial_funding_details']))
                                                    <a href="javascript:void(0)" class="btn btn-danger ml-4">Rejected</a>
                                                @else
                                                    <a href="javascript:void(0)" class="btn btn-success ml-4">Accepted</a>
                                                @endif
                                            </h4>
                                            <div class="row">
                                                <div class="custom-col-review col-md-3">
                                                    <div class="form-group">
                                                        <div class="proof-of-identity">
                                                            <h4>Cheque</h4>
                                                            <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$customerOvdDetails['cheque_image']) }}" class="img-fluid ovd_image">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="custom-col-review proof-of-identity col-md-9">
                                                    <h4>Verify Details</h4>
                                                    <div class="row">
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center">
                                                                    <p class="lable-cus">Initial Funding Type</p>
                                                                    <span>
                                                                        @if(isset($reviewDetails['initial_funding_type']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['initial_funding_type']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span> 
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="initial_funding_type" value="{{$customerOvdDetails['initial_funding_type']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center">
                                                                    <p class="lable-cus">Date</p>
                                                                    <span>
                                                                        @if(isset($reviewDetails['initial_funding_date']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['initial_funding_date']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="initial_funding_date" value="{{$customerOvdDetails['initial_funding_date']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center">
                                                                    <p class="lable-cus">Amount</p>
                                                                    <span>
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
                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="amount" value="{{$customerOvdDetails['amount']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center">
                                                                    <p class="lable-cus">Cheque/Reference</p>
                                                                    <span>
                                                                        @if(isset($reviewDetails['reference']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['reference']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="reference" value="{{$customerOvdDetails['reference']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Bank Name</p>
                                                                    <span>
                                                                        @if(isset($reviewDetails['bank_name']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['bank_name']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="bank_name" value="{{$customerOvdDetails['bank_name']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">IFSC Code</p>
                                                                    <span>
                                                                        @if(isset($reviewDetails['ifsc_code']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['ifsc_code']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="ifsc_code" value="{{$customerOvdDetails['ifsc_code']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Account</p>
                                                                    <span>
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
                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="account_number" value="{{$customerOvdDetails['account_number']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    <p class="lable-cus">Account Name</p>
                                                                    <span>
                                                                        @if(isset($reviewDetails['account_name']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['account_name']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="customer_ovd_details" id="account_name" value="{{$customerOvdDetails['account_name']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($customerOvdDetails['initial_funding_type'] == 1)
                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                        <p class="lable-cus"> Self/Third party :</p>
                                                                        <span>
                                                                            @if(isset($reviewDetails['self_thirdparty']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['self_thirdparty']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="self_thirdparty" value="{{$customerOvdDetails['self_thirdparty']}}" readonly>
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div> 
                                                        @if($customerOvdDetails['initial_funding_type'] == 4)
                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                        <p class="lable-cus">Relationship</p>
                                                                        <span>
                                                                            @if(isset($reviewDetails['relationship']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['relationship']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <input type="text" class="form-control" table="customer_ovd_details" id="relationship" value="{{$customerOvdDetails['relationship']}}" readonly>
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div> 
                                                @if(isset($reviewDetails['intial_funding_details']))
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="reason-of-rejection">
                                                                <h6>Reason of Rejection</h6>
                                                                <p>{{$reviewDetails['intial_funding_details']}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif              
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <div class="cta-btns mt-3">
                                                        <a href="#" class="btn btn-danger mr-3" data-toggle="modal" data-target="#default-Modal">Reject</a>
                                                        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#default-Modal">Accept</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row end -->
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-block">
                                    <!-- Row start -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <h4 class="sub-title">CIDD
                                                @if(isset($reviewDetails['risk_classification_details']))
                                                    <a href="javascript:void(0)" class="btn btn-danger ml-4">Rejected</a>
                                                @else
                                                    <a href="javascript:void(0)" class="btn btn-success ml-4">Accepted</a>
                                                @endif
                                            </h4>
                                            <div class="row">
                                                <div class="custom-col-review proof-of-identity col-md-12">
                                                    <h4>Verify Details</h4>
                                                    <div class="row">
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Expected Annual Turnover (₹) : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['annual_turnover']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['annual_turnover']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <!-- <select class="form-control">
                                                                        <option>Upto ₹ 1 Lakh</option>
                                                                        <option>Upto ₹ 10 Lakh</option>
                                                                    </select> -->
                                                                    <!-- <input type="text" class="form-control" table="risk_classification_details" id="annual_turnover" value="{{$riskDetails['annual_turnover']}}" readonly> -->
                                                                    {!! Form::select('annual_turnover',$annualTurnover,$riskDetails['annual_turnover'],array('class'=>'form-control annual_turnover AddAccountDetailsField',
                                                                                'id'=>'annual_turnover','table'=>'risk_classification_details','name'=>'annual_turnover','placeholder'=>'','readonly')) !!}
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Basis of Categorisation : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['basis_categorisation']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['basis_categorisation']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <!-- <input type="text" class="form-control" table="risk_classification_details" id="basis_categorisation" value="{{$riskDetails['basis_categorisation']}}" readonly> -->
                                                                    {!! Form::select('basis_categorisation',$basisCategorisation,$riskDetails['basis_categorisation'],array('class'=>'form-control basis_categorisation AddAccountDetailsField',
                                                                                'id'=>'basis_categorisation','table'=>'risk_classification_details','name'=>'basis_categorisation','placeholder'=>'','readonly')) !!}
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($riskDetails['basis_categorisation'] == 6)
                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                        Categorisation Comments : 
                                                                        <span>
                                                                            @if(isset($reviewDetails['categorisation_others_comments']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['categorisation_others_comments']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <input type="text" class="form-control" table="risk_classification_details" id="categorisation_others_comments" value="{{$riskDetails['categorisation_others_comments']}}" readonly>
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Customer Type : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['customer_type']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['customer_type']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <!-- <input type="text" class="form-control" table="risk_classification_details" id="customer_type" value="{{$riskDetails['customer_type']}}" readonly> -->
                                                                    {!! Form::select('customer_type',$customerTypes,$riskDetails['customer_type'],array('class'=>'form-control customer_type',
                                                                                'id'=>'customer_type','table'=>'risk_classification_details','name'=>'customer_type','placeholder'=>'','readonly')) !!}
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Country Name : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['country_name']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['country_name']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <!-- <input type="text" class="form-control" table="risk_classification_details" id="country_name" value="{{$riskDetails['country_name']}}" readonly> -->
                                                                    {!! Form::select('country_name',$countries,$riskDetails['country_name'],array('class'=>'form-control country_name',
                                                                                'id'=>'country_name','table'=>'risk_classification_details','name'=>'country_name','placeholder'=>'','readonly')) !!}
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Occupation : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['occupation']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['occupation']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <!-- <input type="text" class="form-control" table="risk_classification_details" id="occupation" value="{{$riskDetails['occupation']}}" readonly> -->
                                                                    {!! Form::select('occupation',$occupations,$riskDetails['occupation'],array('class'=>'form-control occupation',
                                                                                'id'=>'occupation','table'=>'risk_classification_details','name'=>'occupation','placeholder'=>'','readonly')) !!}
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Source of Funds : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['source_of_funds']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['source_of_funds']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <!-- <input type="text" class="form-control" table="risk_classification_details" id="source_of_funds" value="{{$riskDetails['source_of_funds']}}" readonly> -->
                                                                    {!! Form::select('source_of_funds',$sourceOfFunds,$riskDetails['source_of_funds'],array('class'=>'form-control source_of_funds',
                                                                                'id'=>'source_of_funds','table'=>'risk_classification_details','name'=>'source_of_funds','placeholder'=>'','readonly')) !!}
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($riskDetails['source_of_funds'] == 5)
                                                            <div class="details-custcol-row col-md-6">
                                                                <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                    <div class="detaisl-left d-flex align-content-center ">
                                                                        Other Comments : 
                                                                        <span>
                                                                            @if(isset($reviewDetails['source_others_comments']))
                                                                                <i class="fa fa-times"></i>
                                                                                {{$reviewDetails['source_others_comments']}}
                                                                                <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                            @else
                                                                                <i class="fa fa-check"></i>
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div class="details-custcol-row-bootm">
                                                                    <div class="comments-blck">
                                                                        <input type="text" class="form-control" table="risk_classification_details" id="source_others_comments" value="{{$riskDetails['source_others_comments']}}" readonly>
                                                                        <i class="fa fa-check display-none updateColumn"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Risk Classification : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['risk_classification_rating']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['risk_classification_rating']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="risk_classification_details" id="risk_classification_rating" value="{{$riskDetails['risk_classification_rating']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if(isset($reviewDetails['risk_classification_details']))
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="reason-of-rejection">
                                                                <h6>Reason of Rejection</h6>
                                                                <p>{{$reviewDetails['risk_classification_details']}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <div class="cta-btns mt-3">
                                                        <a href="#" class="btn btn-danger mr-3" data-toggle="modal" data-target="#default-Modal">Reject</a>
                                                        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#default-Modal">Accept</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row end -->
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-block">
                                    <!-- Row start -->
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <h4 class="sub-title">Nomination Details
                                                @if(isset($reviewDetails['nominee_details']))
                                                    <a href="javascript:void(0)" class="btn btn-danger ml-4">Rejected</a>
                                                @else
                                                    <a href="javascript:void(0)" class="btn btn-success ml-4">Accepted</a>
                                                @endif
                                            </h4>
                                            <div class="row">
                                                <div class="custom-col-review proof-of-identity col-md-12">
                                                    <h4>Verify Details</h4>
                                                    <div class="row">
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Nominee Name : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['nominee_name']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['nominee_name']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="nominee_details" id="nominee_name" value="{{$nomineeDetails['nominee_name']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Address : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['nominee_address']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['nominee_address']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="nominee_details" id="nominee_address" value="{{$nomineeDetails['nominee_address']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Relationship with Nominee 
                                                                    <span>
                                                                        @if(isset($reviewDetails['relatinship_applicant']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['relatinship_applicant']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="nominee_details" id="relatinship_applicant" value="{{$nomineeDetails['relatinship_applicant']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Date of Birth : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['nominee_dob']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['nominee_dob']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="nominee_details" id="nominee_dob" value="{{$nomineeDetails['nominee_dob']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Age : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['nominee_age']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['nominee_age']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="nominee_details" id="nominee_age" value="{{$nomineeDetails['nominee_age']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Guaridan Name : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['guardian_name']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['guardian_name']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="nominee_details" id="guardian_name" value="{{$nomineeDetails['guardian_name']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="details-custcol-row col-md-6">
                                                            <div class="details-custcol-row-top d-flex editColumnDiv">
                                                                <div class="detaisl-left d-flex align-content-center ">
                                                                    Guaridan Address : 
                                                                    <span>
                                                                        @if(isset($reviewDetails['guardian_address']))
                                                                            <i class="fa fa-times"></i>
                                                                            {{$reviewDetails['guardian_address']}}
                                                                            <a href="javascript:void(0)" class="text-link editColumn">Edit</a></span>
                                                                        @else
                                                                            <i class="fa fa-check"></i>
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="details-custcol-row-bootm">
                                                                <div class="comments-blck">
                                                                    <input type="text" class="form-control" table="nominee_details" id="guardian_address" value="{{$nomineeDetails['guardian_address']}}" readonly>
                                                                    <i class="fa fa-check display-none updateColumn"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="custom-col-review col-md-3">
                                                            <div class="form-group">
                                                                <div class="proof-of-identity">
                                                                    <h4>Witness Signature1</h4>
                                                                   <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                        <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$nomineeDetails['witness1_signature_image']) }}" class="img-fluid ovd_image">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="custom-col-review col-md-3">
                                                            <div class="form-group">
                                                                <div class="proof-of-identity">
                                                                    <h4>Witness Signature2</h4>
                                                                    <div class="{{$image_mask_blur}}" {{$def_blur_image}}>
                                                                        <img src="{{ asset('uploads/markedattachments/'.$formId.'/'.$nomineeDetails['witness2_signature_image']) }}" class="img-fluid ovd_image">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>                                                        
                                                    </div>
                                                </div> 
                                                @if(isset($reviewDetails['nominee_details']))
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="reason-of-rejection">
                                                                <h6>Reason of Rejection</h6>
                                                                <p>{{$reviewDetails['nominee_details']}}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif              
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <div class="cta-btns mt-3">
                                                        <a href="#" class="btn btn-danger mr-3" data-toggle="modal" data-target="#default-Modal">Reject</a>
                                                        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#default-Modal">Accept</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Row end -->
                                </div>
                            </div>                              
                            <div class="row">
                                <div class="col-md-12 text-center mt-3 mb-3">
                                <a href="#" class="btn btn-primary">Save and Continue</a>
                            </div>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal large-->
<div class="modal fade custom-popup" id="upload_proof" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close position-absolute top-0 end-0 px-2" data-bs-dismiss="modal" aria-label="Close">
                <!-- <span aria-hidden="true">&times;</span> -->
            </button>
            <div class="modal-body mt-4">
                <div class="custom-popup-heading document_name">
                    <h1>Upload Image</h1>
                </div>
                <div class="upload-blck">
                    
                <input type="file" class="" id="inputImage" name="file" accept="image/*">
                    <div class="upload-blck-inn d-flex justify-content-center align-items-center">
                        <div class="upload-icon">
                            <img src="{{ asset('assets/images/browse-icon.svg') }}">
                        </div>
                        <div class="upload-con">                            
                            <h5>Drag & Drop or <span>Browse</span></h5>  
                        </div>
                    </div>
                </div>                
                <div class="container img-crop-blck image_preview">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-6">
                            <div class="img-container" >
                                <img id="image" class="preview_image" src="" alt="Crop Picture">
                            </div>
                            <div class="row">
                                <div class="col-md-12 docs-buttons button-page d-flex justify-content-center align-items-center">
                                    <div class="btn-group">
                                        <button type="button" class="rotate-icons" data-method="rotate" data-option="-45" title="Rotate Left">
                                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, -45)">
                                                <span class="fa fa-rotate-left"></span>
                                            </span>
                                        </button>
                                        <button type="button" class="rotate-icons" data-method="rotate" data-option="45" title="Rotate Right">
                                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, 45)">
                                                <span class="fa fa-rotate-right"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <button class="image_crop btn btn-green"> crop </button>
                                </div>      
                            </div>
                        </div>
                        <div class="col-md-6 display-none" id="img-preview-div">
                            <div class="docs-preview clearfix">
                                <img class="crop_image_preview" src="">
                                <!-- <div class="img-preview preview-lg"></div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 text-center mt-3">
                    <button type="button" id="uploadImage" class="btn btn-primary">Save document</button>
                </div>
            </div>              
        </div>
    </div>
</div>                      
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/bank.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        addSelect2('mode_of_operation','Mode of Operation',true);
        addSelect2('title','title',true);
        addSelect2('annual_turnover','Annual Turnover',true);
        addSelect2('basis_categorisation','Basis of Categorisation',true);
        addSelect2('customer_type','Customer Type',true);
        addSelect2('country_name','Country Name',true);
        addSelect2('occupation','occupation',true);
        addSelect2('source_of_funds','source_of_funds',true);
        $(".ovd_image").cropper({
        aspectRatio: 640 / 320,
        autoCropArea: 0.6,
        autoCrop:false,
        dragCrop: false,
        resizable: false,
        built: function () {
                $(this).cropper("setDragMode", 'move');
                $(this).cropper("clear");
            }
        });
        $("#dob").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy"
        });
        /*imageCropper('pf_type_image_id');
        imageCropper('pf_type_image_id');*/
        /*addSelect2('account_type','Account Type');
        addSelect2('mode_of_operation','Mode of Operation');
        addSelect2('residential_status','Residential Status');
        addSelect2('scheme_code','Scheme Code');
        addSelect2('education','Education');
        addSelect2('gross_income','Gross Income');
        $("#date_of_birth").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy"
        });
        $('#qty_input').prop('disabled', true);
        $('#plus-btn').click(function(){
            $('#qty_input').val(parseInt($('#qty_input').val()) + 1 );
        });
        $('#minus-btn').click(function(){
            $('#qty_input').val(parseInt($('#qty_input').val()) - 1 );
            if ($('#qty_input').val() == 0) {
                $('#qty_input').val(1);
            }
        });*/
    });
</script>
@endpush