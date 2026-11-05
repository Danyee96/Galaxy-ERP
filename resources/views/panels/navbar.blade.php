{{-- navabar  --}}
<div class="header-navbar-shadow"></div>
<nav class="header-navbar main-header-navbar navbar-expand-lg navbar navbar-with-menu
@if(isset($configData['navbarType'])){{$configData['navbarClass']}} @endif"
data-bgcolor="@if(isset($configData['navbarBgColor'])){{$configData['navbarBgColor']}}@endif">
  <div class="navbar-wrapper">
    <div class="navbar-container content">
      <div class="navbar-collapse" id="navbar-mobile">
        <div class="mr-auto float-left brand-context">
          <a class="menu-toggle" href="#" role="button" aria-label="Toggle navigation"><i class="bx bx-menu" aria-hidden="true"></i></a>
          <span>@yield('title', 'Dashboard')</span>
        </div>
        <ul class="nav navbar-nav float-right align-items-center">
          <li class="brand-search">
            <input id="brand-page-search" class="form-control" type="search" placeholder="Search pages and modules…" aria-label="Search pages and modules" autocomplete="off" aria-controls="brand-search-results" aria-expanded="false">
            <div id="brand-search-results" class="brand-search-results" hidden></div>
          </li>
          <li class="nav-item d-none d-lg-block"><a class="nav-link nav-link-expand"><i class="ficon bx bx-fullscreen"></i></a></li>
          <li class="dropdown dropdown-user nav-item">
            <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown">
              <div class="user-nav d-sm-flex d-none">
                <span class="user-name">{{Auth::user()->name}}</span>
                <span class="user-status text-muted">Available</span>
              </div>
              <span class="brand-avatar">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right pb-0">
              <div class="dropdown-divider mb-0"></div>
              <a class="dropdown-item" href="{{route('userLogout')}}"><i class="bx bx-power-off mr-50"></i> Logout</a>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
