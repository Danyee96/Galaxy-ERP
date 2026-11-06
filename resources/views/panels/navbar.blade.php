<div class="header-navbar-shadow"></div>
<nav class="header-navbar main-header-navbar navbar-expand-lg navbar navbar-with-menu @if(isset($configData['navbarType'])){{ $configData['navbarClass'] }}@endif" aria-label="Workspace navigation">
  <div class="navbar-wrapper"><div class="navbar-container content"><div class="navbar-collapse erp-topbar" id="navbar-mobile">
    <div class="brand-context">
      <button class="menu-toggle erp-icon-button d-xl-none" type="button" aria-label="Toggle navigation" aria-controls="main-menu-navigation"><i class="bx bx-menu" aria-hidden="true"></i></button>
      <span class="erp-workspace-name">Galaxy ERP<span aria-hidden="true">/</span><strong>@yield('title', 'Dashboard')</strong></span>
    </div>
    <div class="brand-search">
      <i class="bx bx-search erp-search-icon" aria-hidden="true"></i>
      <input id="brand-page-search" class="form-control" type="search" placeholder="Search pages and modules" aria-label="Search pages and modules" autocomplete="off" aria-controls="brand-search-results" aria-expanded="false">
      <kbd aria-hidden="true">Ctrl K</kbd>
      <div id="brand-search-results" class="brand-search-results" role="region" aria-label="Matching pages" hidden></div>
    </div>
    <ul class="nav navbar-nav erp-topbar-actions">
      <li class="erp-mobile-search"><button class="erp-icon-button" type="button" data-erp-search-toggle aria-label="Open page search" aria-controls="brand-page-search" aria-expanded="false"><i class="bx bx-search" aria-hidden="true"></i></button></li>
      <li><button class="erp-icon-button" type="button" data-erp-theme-toggle aria-label="Switch color theme" aria-pressed="false"><i class="bx bx-moon" aria-hidden="true"></i></button></li>
      <li class="d-none d-lg-block"><button class="erp-icon-button nav-link-expand" type="button" aria-label="Toggle fullscreen"><i class="bx bx-fullscreen" aria-hidden="true"></i></button></li>
      <li class="dropdown dropdown-user nav-item">
        <a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown" aria-label="Account menu" aria-haspopup="true" aria-expanded="false">
          <span class="brand-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
          <div class="user-nav d-sm-flex d-none"><span class="user-name">{{ Auth::user()->name }}</span><span class="user-status text-muted">Signed in</span></div>
          <i class="bx bx-chevron-down" aria-hidden="true"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="{{ route('userLogout') }}"><i class="bx bx-log-out mr-50" aria-hidden="true"></i> Sign out</a></div>
      </li>
    </ul>
  </div></div></div>
</nav>
