<!-- Sidebar chat start -->
<div id="sidebar" class="users p-chat-user showChat">
    <div class="had-container">
        <div class="card card_main p-fixed users-main">
            <div class="user-box">
                <div class="chat-search-box">
                    <a class="back_friendlist">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                    <div class="right-icon-control">
                        <input type="text" class="form-control search-text" placeholder="Search Friend" id="searchuser" name="searchuser">
                        <div class="form-icon searchUserIcon">
                            <i class="fa fa-search"></i>
                        </div>
                        <i class="fa fa-close seach-close-icon" id="closeSearchUserIcon" style="display: none;"></i>
                    </div>
                </div>
                <div class="main-friend-list chat-slim-scroll">
                    @if(count($users) > 0)
                        <?php $i=0;?>
                        @foreach($users as $user)
                            <?php $user = (array) $user;?>
                            @if ($i%3 == 0)
                                <?php $class = "user-box-color-3"; ?>
                            @elseif ($i%3 == 1)
                                <?php $class = "user-box-color-2"; ?>
                            @elseif ($i%3 == 2)
                                <?php $class = "user-box-color-1"; ?>
                            @endif
                            <div class="media userlist-box userchat" id="{{$user['id']}}" data-id="{{$user['id']}}" data-status="online" data-username="{{$user['name']}}" data-toggle="tooltip" data-placement="left" 
                            title="{{$user['name']}}">
                                <a class="media-left" href="javascript:void(0);">
                                    <div class="user-name-box {{$class}}">{{strtoupper($user['name'][0])}}</div>
                                    @if(Cache::get($user['id'].'_is_logged_in'))
                                        <div class="live-status bg-success"></div>
                                    @endif
                                </a>
                                <div class="media-body chat-underline">
                                    <div class="f-13 chat-header">
                                        {{ucwords(strtolower($user['name']))}}
                                        @if(isset($user['msgcount']))
                                            <span class="badge bg-pinterest" id="{{$user['id']}}_count">{{$user['msgcount']}}</span>                                            
                                        @endif
                                    </div>
                                    <div class="chat-branch-name">
                                        {{$user['role']}}
                                        @if($user['empmobileno'] != '')
                                            ({{$user['empmobileno']}})
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <?php $i++;?>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>