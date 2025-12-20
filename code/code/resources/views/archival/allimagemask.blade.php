@extends('layouts.app')
@push('meta')
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
@endpush
@section('content')
    <style type="text/css">
        body {
            overflow-y: hidden;
        }

        #userApplicationsTable {
            width: 100% !important;
        }

        .table td,
        .table th {
            padding: 10px 0px;
        }

        .pcoded-content1 {
            width: 100%;
            height: 500px;
            overflow-y: scroll;
            border: 1px solid #ccc;
        }
        .imgmask {
            width: 132px;
            height: 152px;
        }
    </style>

@section('content')
    <form action="{{ route('allimagemask') }}" method="POST">
        @csrf
        <div class="row filter mb-4 mt-5  d-flex justify-content-center">
            <div class="col-md-3">
                <input type="text" class="form-control" id="aof_number" name="aof_number" placeholder="AOF Number" required>
            </div>
            <div class="col-md-1">
                <button type="submit" id="aof_search" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

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
                                                    <th>Sr.</th>
                                                    <th>AOF Number</th>
                                                    <th>Applicant Sequence</th>
                                                    <th>Image Type</th>
                                                    <th>Side(F/B)</th>
                                                    <th>Masked</th>
                                                    <th>Image Thumbnail</th>
                                                    <th>Action</th>
                                                    
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($aadhaarDetails as $index => $detail)
                                                    @php
                                                        $detail = (array) $detail;
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $detail['aof_number'] }}</td>
                                                        <td>{{ $detail['applicant_sequence'] }}</td>
                                                        <td>{{ $detail['image_type'] }}</td>
                                                        <td>{{ strtoupper($detail['side']) }}</td>
                                                        <td>{{ $detail['flag'] }}</td>
                                                       
                                                        @if(str_contains($detail['image_type'], 'l3_update'))
                                                        <td>
                                                            <img src="{{ URL::to('/imageslevelthree/' . $detail['form_id'] . '/' . $detail['result1']) }}"
                                                               class="imgmask" alt="No Image">
                                                        </td>
                                                        @else
                                                        <td>
                                                            <img src="{{ URL::to('/imagesmarkedattachments/' . $detail['form_id'] . '/' . $detail['result1']) }}"
                                                               class="imgmask" alt="No Image">
                                                        </td>
                                                        @endif
                                                        <td>
                                                            <!-- <a href="{{ url('/archival/displayimage',[ $detail['id']]) }}"
                                                                id = "maskimag" value="{{ $detail['image_type']}}" class="btn btn-primary btn-sm">
                                                                Mask
                                                            </a> -->
                                                            <form action="{{ route('displayimage') }}" method="POST">
                                                                @csrf                                                                
                                                                <input type="text" id="dname" name="id" value="{{ $detail['id']}}" hidden>
                                                                 <button class="btn btn-primary btn-sm" >Mask</button>
                                                            </form>                                                         
                                                          
                                                        </td>                                                        
                                                    <tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endsection

    @push('scripts')
        <script type="text/javascript">
            $('#aof_number').keyup(function() {
                this.value = this.value.replace(/[^0-9\.]/g, '');
            });

            $("#aof_search").on("click", function() {

                if ($("#aof_number").val() == "") {
                    $.growl({
                        message: "Please Enter AOF Number"
                    }, {
                        type: "warning"
                    });
                    return false;
                }

                if ($("#aof_number").val().length < 11 || $("#aof_number").val().length > 11) {
                    $.growl({
                        message: "Please Enter Valid AOF Number"
                    }, {
                        type: "warning"
                    });
                    return false;
                }
            })

            document.addEventListener('DOMContentLoaded', function() {
                var scrollableElement = document.querySelector('.pcoded-content1');
                scrollableElement.scrollTop = scrollableElement.scrollHeight;

                scrollableElement.addEventListener('scroll', function() {
                    console.log('Scrolled to position:', scrollableElement.scrollTop);
                });
            });
        </script>


    <script>
    //  $('#maskimag').on('click',function(){

    //     var aadhaarimage = [];
    //     aadhaarimage.data = {};

    //     var imageType = $('#maskimag').val();
    //     // alert(imageType);
    //     aadhaarimage.data['id'] = imageType;
    //     aadhaarimage.url = '/archival/displayimage';
    //     aadhaarimage.data['functionName'] = 'updateAadhaarMaskingAppCallBack';

    //     crudAjaxCall(aadhaarimage);

    //  });

    //  function updateAadhaarMaskingCallBack(response, object){
    //     console.log('imagedatata',response);
    // //     var baseUrl = $('meta[name="base_url"]').attr('content');
    // // setTimeout(function(){
    // //     window.location = baseUrl+'/archival/aadhaarmasking';
    // // },2000);
    //         }
    
    // </script>
    @endpush
