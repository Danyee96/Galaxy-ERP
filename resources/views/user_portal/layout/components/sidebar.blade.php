<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow" data-scroll-to-active="true">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
            <li class="nav-item mr-auto"><a class="navbar-brand" href="{{ route('index') }}">
                    @include('branding.logo')
                </a></li>
            <li class="nav-item nav-toggle"><a class="nav-link modern-nav-toggle pr-0" data-toggle="collapse"><i class="bx bx-x d-block d-xl-none font-medium-4 primary toggle-icon"></i><i class="toggle-icon bx bx-disc font-medium-4 d-none d-xl-block collapse-toggle-icon primary" data-ticon="bx-disc"></i></a></li>
        </ul>
    </div>
    <div class="shadow-bottom"></div>
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation" data-icon-style="">
            <li class=" nav-item">
                <a href="{{route('index')}}"><i class="bx bx-home-alt"></i><span class="menu-title text-truncate" data-i18n="Dashboard">Dashboard</a>
                <!-- <ul class="menu-content">
                    <li><a class="d-flex align-items-center" href="dashbo   ard-ecommerce.html"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="eCommerce">eCommerce</span></a>
                    </li>
                    <li class="active"><a class="d-flex align-items-center" href="dashboard-analytics.html"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="Analytics">Analytics</span></a>
                    </li>
                </ul> -->
            </li>
            
            <li class=" nav-item"><a href="#"><i class="bx bx-user-plus"></i><span class="menu-title text-truncate" data-i18n="User">Users</span></a>
                <ul class="menu-content">
                    <li><a class="d-flex align-items-center" href="{{route('UserList')}}"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="List">User List</span></a>
                    </li>
                    <li><a class="d-flex align-items-center" href="{{route('createUser')}}"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="View">Add New User</span></a>
                    </li>
                </ul>
            </li>

            <li class=" nav-item"><a href="#"><i class="bx bx-user"></i><span class="menu-title text-truncate" data-i18n="User">Customers</span></a>
                <ul class="menu-content">
                    <li><a class="d-flex align-items-center" href="{{route('CustomerList')}}"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="List">Customers List</span></a>
                    </li>
                    <li><a class="d-flex align-items-center" href="{{route('createCustomer')}}"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="View">Add New Customer</span></a>
                    </li>
                </ul>
            </li>

            <li class=" nav-item"><a href="#"><i class="bx bxl-product-hunt"></i><span class="menu-title text-truncate" data-i18n="User">Products</span></a>
                <ul class="menu-content">
                    <li><a class="d-flex align-items-center" href="{{route('productList')}}"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="List">Products List</span></a>
                    </li>
                    <li><a class="d-flex align-items-center" href="{{route('createProduct')}}"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="View">Add New Product</span></a>
                    </li>
                </ul>
            </li>

            <li class=" nav-item"><a href="#"><i class="bx bx-cart"></i><span class="menu-title text-truncate" data-i18n="User">Orders</span></a>
                <ul class="menu-content">
                    <li><a class="d-flex align-items-center" href="{{route('orderList')}}"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="List">Orders List</span></a>
                    </li>
                    <li><a class="d-flex align-items-center" href="{{route('createOrder')}}"><i class="bx bx-right-arrow-alt"></i><span class="menu-item text-truncate" data-i18n="View">Add New Order</span></a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>