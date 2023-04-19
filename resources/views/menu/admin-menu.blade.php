@if(Auth::user())
<div class="leftmenu fixedpos">
    <div class="currenuser mt-3">
        <div class="avatar">
            <img src="{{ asset('/img/user_avatar.png') }}">
        </div>
        <div>
            <h3>{{Auth::user()->name}}</h3>
            <p>
                <a class="text-light" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    {{ __('Log Out') }}
                </a>
            </p>
        </div>
    </div>

    <ul class="mainmenu">
        <li>
            <a href="{{ url('/admin/dashboard') }}" data-name="DASHBOARD">
                <img src="{{ asset('/img/admin_03.png') }}">
                Dashboard
            </a>
        </li>
        <li style="position: relative;">
            <a href="{{ url('/admin/orders') }}" data-name="MANAGE ORDERS">
                <img style="display: inline" src="{{ asset('/img/admin_09.png') }}">
                Orders
                @if ($refundReqCount !== 0)
                    <div style="display: flex; justify-content: center; align-items: center; min-height: 35px; position: absolute; right: 30px">
                        <span style="display: inline-block; min-width: 30px; height: auto; padding: 5px; border-radius: 50%; text-align: center; background: red; color: white; font-size: 14px;">{{ $refundReqCount }}</span>
                    </div>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ url('/admin/merchants') }}" data-name="MANAGE MERCHANTS">
                <img src="{{ asset('/img/admin_07.png') }}">
                Merchants
            </a>
        </li>
        <li>
            <a href="{{ url('/admin/users') }}" data-name="USERS">
                <img src="{{ asset('/img/admin_11.png') }}">
                Users
            </a>
        </li>
        <li>
            <a href="{{ url('/admin/add_user') }}" data-name="ADD USER">
                <img src="{{ asset('/img/admin_13.png') }}">
                Add User
            </a>
        </li>
        <li>
            <a href="{{ url('/admin/profile') }}" data-name="PROFILE">
                <img src="{{ asset('/img/admin_12.png') }}">
                Profile
            </a>
        </li>
    </ul>
</div>
@endif
