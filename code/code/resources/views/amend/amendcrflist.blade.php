
<style>
.crfhover:hover {
    cursor: pointer;
    opacity: 0.2;
    position: relative;
}
</style>
<div class="container-fluid">
    <div class="row">
        <table class='table'>
                <tr style="background:#364FCC;color:white;">
                    <th>ID</th>
                    <th>CREATED DATE</th>
                    <th>CRF NUMBER</th>
                    <th>CUSTOMER NAME</th>
                    <th>CRF STATUS</th>
                </tr>
            @for($crfls=0;count($getCrfList)>$crfls;$crfls++)
                @php
                    $getCrfList[$crfls]->crf_status =  Config::get('amend_status.CRF_STATUS.'.$getCrfList[$crfls]->crf_status);
                @endphp
                <tr>
                    <td>{{$getCrfList[$crfls]->id}}</td>
                    <td>{{$getCrfList[$crfls]->created_at}}</td>
                    <td><a id={{$getCrfList[$crfls]->crf_number}} class="crfhover" style="font-size:16px;color:#007bff;" onclick="trackingcrf({{$getCrfList[$crfls]->crf_number}});">{{$getCrfList[$crfls]->crf_number}}</a></td>
                    <td>{{$getCrfList[$crfls]->customer_name}}</td>
                    <td>{{$getCrfList[$crfls]->crf_status}}</td>
                </tr>
            @endfor
        </table>
    </div>
</div>
@push('scripts')
<script  src="{{ asset('custom/js/amendtracking.js') }}"></script>
@endpush