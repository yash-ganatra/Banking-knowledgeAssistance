@extends('layouts.app')
@section('content')
<div style="padding: 60px;">
    <div class="parent-block d-flex align-items-center">
            <div class="block line-top">
                <div class="block-lable"><p>Branch</p></div>
                <div class="form-count"><p>2677</p></div>
                <div class="block-graph">
                    <div class="green-pattern block-innp" style="width:70%;"></div>
                    <div class="orenge-pattern block-innp" style="width:10%;"></div>
                    <div class="red-pattern block-innp" style="width:20%;"></div>
                </div>
            </div>
    
            <div class="branch block-inn3 vertical-inn">
                <div class="block">
                    <div class="block-lable"><p>Funding</p></div>
                    <div class="form-count"><p>1145</p></div>
                    <div class="block-graph">
                        <div class="green-pattern block-innp" style="width:50%;"></div>
                        <div class="orenge-pattern block-innp" style="width:20%;"></div>
                        <div class="red-pattern block-innp" style="width:30%;"></div>
                    </div>
                </div>
        
                <div class="block">
                    <div class="block-lable"><p>L1</p></div>
                    <div class="form-count"><p>1315</p></div>
                    <div class="block-graph">
                        <div class="green-pattern block-innp" style="width:60%;"></div>
                        <div class="orenge-pattern block-innp" style="width:30%;"></div>
                        <div class="red-pattern block-innp" style="width:10%;"></div>
                    </div>
                </div>
        
                <div class="block">
                    <div class="block-lable"><p>Dispatch</p></div>
                    <div class="form-count"><p>1475</p></div>
                    <div class="block-graph">
                        <div class="green-pattern block-innp" style="width:90%;"></div>
                        <div class="orenge-pattern block-innp" style="width:5%;"></div>
                        <div class="red-pattern block-innp" style="width:5%;"></div>
                    </div>
                </div>
            </div>

            <div class="branch block-inn1">
                
                <div class="block vertical-line-bottom">
                    <div class="block-lable"><p>L2</p></div>
                    <div class="form-count"><p>1210</p></div>
                    <div class="block-graph">
                        <div class="green-pattern block-innp" style="width:30%;"></div>
                        <div class="orenge-pattern block-innp" style="width:40%;"></div>
                        <div class="red-pattern block-innp" style="width:30%;"></div>
                    </div>
                </div>
            </div>

            <div class="branch block-inn1">
                
                <div class="block block-top-inn">
                    <div class="block-lable"><p>AC.Open</p></div>
                    <div class="form-count"><p>2677</p></div>
                    <div class="block-graph">
                        <div class="green-pattern block-innp" style="width:40%;"></div>
                        <div class="orenge-pattern block-innp" style="width:20%;"></div>
                        <div class="red-pattern block-innp" style="width:40%;"></div>
                    </div>
                </div>
            </div>

            <div class="branch-last vertical-inn vertical-line3">
                <div class="block">
                    <div class="block-lable"><p>QC</p></div>
                    <div class="form-count"><p>780</p></div>
                    <div class="block-graph">
                        <div class="green-pattern block-innp" style="width:70%;"></div>
                        <div class="orenge-pattern block-innp" style="width:10%;"></div>
                        <div class="red-pattern block-innp" style="width:20%;"></div>
                    </div>
                </div>
        
                <div class="block">
                    <div class="block-lable"><p>Audit</p></div>
                    <div class="form-count"><p>565</p></div>
                    <div class="block-graph">
                        <div class="green-pattern block-innp" style="width:10%;"></div>
                        <div class="orenge-pattern block-innp" style="width:30%;"></div>
                        <div class="red-pattern block-innp" style="width:60%;"></div>
                    </div>
                </div>
        
                <div class="block">
                    <div class="block-lable"><p>Archival</p></div>
                    <div class="form-count"><p>760</p></div>
                    <div class="block-graph">
                        <div class="green-pattern block-innp" style="width:50%;"></div>
                        <div class="orenge-pattern block-innp" style="width:20%;"></div>
                        <div class="red-pattern block-innp" style="width:30%;"></div>
                    </div>
                </div>
            </div>

    </div>
</div>
@endsection