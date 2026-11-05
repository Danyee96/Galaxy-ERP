{{-- vertical-menu --}}
@if($configData['mainLayoutType'] == 'vertical-menu')
<div class="main-menu brand-sidebar menu-fixed @if($configData['theme'] === 'light') {{"menu-light"}} @else {{'menu-dark'}} @endif menu-accordion menu-shadow" data-scroll-to-active="true">
  <div class="navbar-header">
    <ul class="nav navbar-nav flex-row">
    <li class="nav-item mr-auto">
      <a class="navbar-brand" href="{{asset('/')}}">
      <div class="brand-logo">
        <i class="bx bx-power-off brand-mark" aria-hidden="true"></i>
      </div>
      <h2 class="brand-text mb-0" style="color: #1C88CC">
        AutoPower
      </h2>
      </a>
    </li>
        <li class="nav-item nav-toggle">
          <a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse" role="button" tabindex="0" aria-label="Toggle navigation">
            <i class="bx bx-x d-block d-xl-none font-medium-4 primary"></i>
            <i class="toggle-icon bx bx-disc font-medium-4 d-none d-xl-block primary" data-ticon="bx-disc"></i>
          </a>
        </li>
    </ul>
  </div>
  <div class="shadow-bottom"></div>
  <div class="main-menu-content" tabindex="0" aria-label="Main navigation">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation" data-icon-style="lines">
        <li class="nav-item {{ Route::currentRouteName() === 'index' ? 'active' : '' }}">
          <a href="{{ route('index') }}"><i class="bx bx-grid-alt" aria-hidden="true"></i><span class="menu-title">Dashboard</span></a>
        </li>
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 5)
        <li class="nav-item brand-category">
          <a href="#" role="button" aria-expanded="false"><i class="bx bx-group" aria-hidden="true"></i><span class="menu-title">Admin Users</span></a>
          <ul class="menu-content">
        <li class="nav-item {{ Route::currentRouteName() === 'user-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-group" aria-hidden="true"></i>
            <span class="menu-title text-truncate">User Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'createUser' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-user')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add User</span>
              </a>
            </li>
            <li {{ 'UserList' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('users')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Users List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'company-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-folder" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Company Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'createCompany' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-company')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add Company</span>
              </a>
            </li>
            <li {{ 'CompanyList' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('company-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Company List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'power-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-folder" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Power Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'createPower' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-power')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add Power</span>
              </a>
            </li>
            <li {{ 'PowerList' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('power-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Power List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'error-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-folder" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Error Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'createError' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-error')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add Error</span>
              </a>
            </li>
            <li {{ 'ErrorList' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('error-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Error List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'accessory-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-folder" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Accessory Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'createAccessory' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-accessory')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add Accessory</span>
              </a>
            </li>
            <li {{ 'AccessoryList' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('accessory-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Accessory List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'machine-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-folder" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Custom Machines</span>
          </a>
          <ul class="menu-content">
            <li {{ 'create-custom-machine-data' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-custom-machine-data')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add Custom Machine</span>
              </a>
            </li>
            <li {{ 'custom-machine-list' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('custom-machine-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Custom Machine List</span>
              </a>
            </li>
          </ul>
        </li>
                  </ul>
        </li>

        <li class="nav-item {{ Route::currentRouteName() === 'serialization-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-list-ul" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Serialization</span>
          </a>
          <ul class="menu-content">
            <li {{ 'create-serialization-data' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-serialization-data')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add Serialization</span>
              </a>
            </li>
            <li {{ 'serialization-list' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('serialization-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Serialization List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item brand-category">
          <a href="#" role="button" aria-expanded="false"><i class="bx bx-search" aria-hidden="true"></i><span class="menu-title">Check Item</span></a>
          <ul class="menu-content">
        <li class="nav-item {{ Route::currentRouteName() === 'searchMachine' ? 'active' : '' }}">
          <a href="{{asset('search-machine')}}">
            <i class="bx bx-folder" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Search Machine</span>
          </a>
        </li>
          </ul>
        </li>
      @endif
      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 3 || Auth::user()->role_id == 5)
        <li class="nav-item brand-category">
          <a href="#" role="button" aria-expanded="false"><i class="bx bx-wrench" aria-hidden="true"></i><span class="menu-title">Test Item</span></a>
          <ul class="menu-content">
        <li class="nav-item {{ Route::currentRouteName() === 'view-testing-list' ? 'active' : '' }}">
          <a href="{{asset('view-testing-list')}}">
            <i class="bx bx-grid-alt" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Testing List</span>
          </a>
        </li>
      @if(Auth::user()->role_id == 2)
        <li class="nav-item {{ Route::currentRouteName() === 'view-testing-form' ? 'active' : '' }}">
          <a href="{{asset('view-testing-form')}}">
            <i class="bx bx-wrench" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Start Testing</span>
          </a>
        </li>
      @endif
          </ul>
        </li>
      @endif
      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 5)
        <li class="nav-item brand-category">
          <a href="#" role="button" aria-expanded="false"><i class="bx bx-file" aria-hidden="true"></i><span class="menu-title">Reporting</span></a>
          <ul class="menu-content">
        <li class="nav-item {{ Route::currentRouteName() === 'reporting' ? 'active' : '' }}">
          <a href="{{asset('reporting')}}">
            <i class="bx bx-file" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Reporting</span>
          </a>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'reporting-download' ? 'active' : '' }}">
          <a href="{{asset('reporting-download')}}">
            <i class="bx bx-file" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Download All Machine Data</span>
          </a>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'custom-machine-report' ? 'active' : '' }}">
          <a href="{{asset('custom-machine-report')}}">
            <i class="bx bx-file" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Download Custom Machine Data</span>
          </a>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'machine-list' ? 'active' : '' }}">
          <a href="{{asset('machine-list')}}">
            <i class="bx bx-printer" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Print Machine Slip</span>
          </a>
        </li>

                  </ul>
        </li>
<li class="nav-item brand-category">
          <a href="#" role="button" aria-expanded="false"><i class="bx bx-package" aria-hidden="true"></i><span class="menu-title">Inventory Management</span></a>
          <ul class="menu-content">

        <li class="nav-item {{ Route::currentRouteName() === 'viewTransitInventory' ? 'active' : '' }}">
          <a href="{{asset('view-transit-inventory')}}">
            <i class="bx bx-package" aria-hidden="true"></i>
            <span class="menu-title text-truncate">View Transit Inventory</span>
          </a>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'raw-material-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-book" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Raw Material Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'create-raw-material' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-raw-material')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add Raw Material</span>
              </a>
            </li>
            <li {{ 'raw-items-list' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('raw-items-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Raw Material List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'ups-model-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-devices" aria-hidden="true"></i>
            <span class="menu-title text-truncate">UPS Model Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'create-ups-model' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-ups-model')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add UPS Model</span>
              </a>
            </li>
            <li {{ 'ups-model-list' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('ups-model-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">UPS Model List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'finished-ups-model-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-check-circle" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Finished UPS Model Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'create-finished-ups-model' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('create-finished-ups-model')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add Finished UPS Model</span>
              </a>
            </li>
            <li {{ 'finished-ups-model-list' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('finished-ups-model-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Finished UPS Model List</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item {{ Route::currentRouteName() === 'in-complete-ups-model-management' ? 'active' : '' }}">
          <a href="{{asset('#')}}">
            <i class="bx bx-time" aria-hidden="true"></i>
            <span class="menu-title text-truncate">In-Complete UPS Model Management</span>
          </a>
          <ul class="menu-content">
            <li {{ 'select-model' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('select-model')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">Add In-Complete UPS Model</span>
              </a>
            </li>
            <li {{ 'in-complete-ups-model-list' === Route::currentRouteName() ? 'class=active' : '' }}>
              <a href="{{asset('in-complete-ups-model-list')}}"  class="d-flex align-items-center">
                <i class="bx bx-right-arrow-alt"></i>
                <span class="menu-item text-truncate">In-Complete UPS Model List</span>
              </a>
            </li>
          </ul>
        </li>

          </ul>
        </li>
      @endif
      @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 4 || Auth::user()->role_id == 5)
        <li class="nav-item brand-category">
          <a href="#" role="button" aria-expanded="false"><i class="bx bx-box" aria-hidden="true"></i><span class="menu-title">QuickBook Inventory</span></a>
          <ul class="menu-content">
        <li class="nav-item {{ Route::currentRouteName() === 'productService' ? 'active' : '' }}">
          <a href="{{asset('product-services')}}">
            <i class="bx bx-package" aria-hidden="true"></i>
            <span class="menu-title text-truncate">Product & Services</span>
          </a>
        </li>
          </ul>
        </li>
      @endif
    </ul>
  </div>
  <div class="brand-sidebar-footer"><a href="{{ route('userLogout') }}"><i class="bx bx-log-out" aria-hidden="true"></i><span>Logout</span></a></div>
</div>
@endif
