@extends('layouts.app')
@section('content')
<style type="text/css">
    body{overflow-y: hidden;}
    #userApplicationsTable{width: 100%!important; }
    .table td, .table th{padding: 10px 0px;}
</style>
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                    <div class="row accountsgrid top-blcks">
                        <!-- order-card start -->                        
                    <div class="card table-top mt-2 px-0">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                                <table class="table table-custom" id="userApplicationsTable">
                                    <thead>
                                        <tr>
                                            <th>AOF Number</th>
                                            <th>Applicant ID</th>
                                            <th>Applicant Sequence</th>
                                            <th>ID_PROOF_NUMBER</th>
                                            <th>ADD_PROOF_NUMBER</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        @foreach($aadhaarMaskingDetails as $aadhaarMaskingDetail)
                                        @php
                                            $aadhaarMaskingDetail = (array) $aadhaarMaskingDetail;
                                        @endphp
                                            <tr>
                                                <td>{{$aadhaarMaskingDetail['aof_number']}}</td>
                                                <td>{{$aadhaarMaskingDetail['id']}}</td>
                                                <td>{{$aadhaarMaskingDetail['applicant_sequence']}}</td>
                                                <td>{{substr($aadhaarMaskingDetail['id_proof_card_number'],0,4)}}</td>
                                                <td>{{substr($aadhaarMaskingDetail['add_proof_card_number'],0,4)}}</td>
                                            <tr> 
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page-body end -->
            </div>  
        </div>                          
    </div>
</div>
@endsection
