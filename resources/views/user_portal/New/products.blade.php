<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{asset('New/css/styles.css')}}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Sen:wght@400..800&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
      rel="stylesheet"
    />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="{{asset('New/script.js')}}"></script>
    <title>Product & Services | {{ config('brand.name') }}</title>
    @include('branding.head')
  </head>
  <body class="galaxy-workspace">
    @include('branding.header')
    <div class="container mt-4">
      <h2>Product & Services</h2>
      <!-- Tabs Section -->
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('index')}}"
            >Back to Dashboard</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('getInvoice')}}"
            >Invoices</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('productEstimates')}}"
            >Estimates</a
          >
        </li>
        <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productEstimatesCalculation')}}">Estimate Calculations</a>
            </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('getCustomer')}}"
            >Customers</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link active"
            href="{{route('productService')}}"
            >Products & Services</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('productCategories')}}"
            >Categories</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('inTransitInventory')}}"
            >In Transit</a
          >
        </li>
        <li class="nav-item">
          <a
            style="font-weight: 200; color: black"
            class="nav-link"
            href="{{route('receivedTransitInventory')}}"
            >Received Transit</a
          >
        </li>
      </ul>

      <!-- Title Section -->
      <div class="header-section">
        <h2>Products and Services</h2>

        <div class="btn-group">
          <!-- <button
            type="button"
            class="btn btn-secondary dropdown-toggle more-button"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            More
          </button>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="#">Action</a>
            <a class="dropdown-item" href="#">Another action</a>
            <a class="dropdown-item" href="#">Something else here</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">Separated link</a>
          </div> -->

          <!-- Simple button -->
          <button id="openSidebar" type="button" class="btn new-button">
            New
          </button>
          <div id="sidebar" class="sidebar">
            <div class="service-info">
              <h4>Product/Service Information</h4>
              <i class="fas fa-times close-icon" id="closeSidebar"></i>
            </div>
            <div id="inventory" class="info-item">
              <div class="shirt"></div>
              <div id="inventoryTab" class="inventory">
                <strong>Inventory</strong>
                <p>
                  Products you buy and/or sell that you track quantities of.
                </p>
              </div>
            </div>
            <div id="noninventory" class="info-item">
              <div class="box"></div>
              <div id="noninventoryTab" class="inventory">
                <strong>Non-inventory</strong>
                <p>
                  Products you buy and/or sell but don't need to (or can't)
                  track quantities of, for example, nuts and bolts used in an
                  installation.
                </p>
              </div>
            </div>
            <div id="service" class="info-item">
              <div class="brush"></div>
              <div class="inventory">
                <strong>Service</strong>
                <p>
                  Services that you provide to customers, for example,
                  landscaping or tax preparation services.
                </p>
              </div>
            </div>
            <div id="bundle" class="info-item">
              <div class="bundle"></div>
              <div class="inventory">
                <strong>Bundle</strong>
                <p>
                  A collection of products and/or services that you sell
                  together, for example, a gift basket of fruit, cheese, and
                  wine.
                </p>
              </div>
            </div>
          
          </div>
          <div class="overlay" onclick="closeSidebar()"></div>
        </div>
        <!-- Bootstrap JS, Popper.js, and jQuery -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
      </div>

      <!-- Statistics Section -->
      <div class="row text-center">
        <div class="col-md-3">
          <a href="{{route('lowStockProducts')}}" style="text-decoration: none;">
            <div class="stat-card low-stock2">
              <div class="low-stock-img">
              </div>
              <div>
                <h3>{{$low_stock}}</h3>
                <p>Low Stock</p>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3">
          <a href="{{route('outStockProducts')}}" style="text-decoration: none;">
            <div class="stat-card out-of-stock2">
              <div class="out-stock-img">
              </div>
              <div>
                <h3>{{$out_of_stock}}</h3>
                <p>Out of Stock</p>
              </div>
            </div>
          </a>
        </div>
        <div class="col-md-3">
          <div class="stat-card air-of-stock2">
            <div class="air-stock-img">
            </div>
            <div>
              <h3>{{$air_transit}}</h3>
              <p>By Air Transit</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card sea-of-stock2">
            <div class="sea-stock-img">
            </div>
            <div>
              <h3>{{$sea_transit}}</h3>
              <p>By Sea Transit</p>
            </div>
          </div>
        </div>
      </div>

      @if(config('warehouses.enabled'))<div class="mb-2"><a class="btn btn-primary" href="{{ route('warehouses.index') }}">Manage warehouses / transfer stock</a></div>@endif
      @include('warehouse._unassigned-warning')
      <!-- Search Section -->
      <div class="search-section">
        <input type="text" style="width: 300px" id="searchInput" class="form-control" placeholder="Find products and services" />
        <div class="filter-container">
          <i class="fas fa-solid fa-filter opening-filter" onclick="toggleFilterDropdown()"></i>
          <i class="fas fa-solid fa-angle-down" onclick="toggleFilterDropdown()"></i>
          <div class="filter-dropdown" id="filterDropdown">
            <form class="p-3" method="GET">
              <div class="form-group">
                <label for="status">Status</label>
                <select class="form-select" name="f_status" id="status">
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
              <div class="form-group">
                <label for="type">Type</label>
                <select class="form-select" name="f_type" id="type">
                  <option value="All">All</option>
                  <option value="Inventory">Inventory</option>
                  <option value="Non-Inventory">Non-Inventory</option>
                  <option value="Service">Service</option>
                  <option value="Bundle">Bundle</option>
                </select>
              </div>
              <div class="form-group">
                <label for="category">Category</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
                  <ul style="list-style-type: none; padding-left: 0; margin: 0;">
                    @foreach($categories as $category)
                    <li>
                      <input type="checkbox" name="f_category[]" value="{{$category->name}}">
                      {{$category->name}}
                    </li>
                    @endforeach
                  </ul>
                </div>
              </div>
              <div class="form-group">
                <label for="stockStatus">Stock Status</label>
                <select class="form-select" name="f_stock" id="stockStatus">
                  <option value="All">All</option>
                  <option value="In">In Stock</option>
                  <option value="Low">Low Stock</option>
                  <option value="Out">Out of Stock</option>
                </select>
              </div>
              <div style="justify-content: space-between;" class="d-flex">
                <button type="button" class="btn btn-secondary mt-2" onclick="toggleFilterDropdown()">Reset</button>
                <button type="submit" class="btn  mt-2 new-button">Apply</button>
              </div>

            </form>
          </div>
        </div>
        <div style="margin-left: 100px;">
          <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
          <button onclick="exportTableToPDF()" class="btn btn-danger">Export to PDF</button>
        </div>
      </div>

      <!-- Table Section -->
      <div class="table-section">
        <table class="table table-hover" id="example">
          <thead>
            <tr>
              <th scope="col"><input type="checkbox" /></th>
              <th scope="col">NAME</th>
              <th scope="col">SKU</th>
              <th scope="col">TYPE</th>
              <th scope="col">CATEGORY</th>
              <th scope="col">COST</th>
              <th scope="col">SALES DESCRIPTION</th>
              <th scope="col">QTY ON HAND</th>
              @if(config('warehouses.enabled'))<th scope="col">WAREHOUSE / LOCATION STOCK</th>@endif
              <th scope="col">REORDER POINT</th>
              <th scope="col">TRANSIT BY AIR</th>
              <th scope="col">TRANSIT BY SEA</th>
              <th scope="col">ACTION</th>
            </tr>
          </thead>
          <tbody>
            @foreach($products as $key => $product)
            <tr>
              <td><input type="checkbox" /></td>
              <td>
                {{$product->name}}
                @if(config('warehouses.enabled') && $unassignedProducts->contains('id',$product->id))<small class="d-block text-danger">Stock correction required · Not assigned to a warehouse</small>@elseif(config('warehouses.enabled'))<small class="d-block"><a href="{{ route('warehouses.index',['source_type'=>'product','source_id'=>$product->id]) }}">Warehouse / location stock</a></small>@endif
                @if($product->type == 'Bundle')
                  <div class="modal fade text-left" id="backdrop{{$product->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel4" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h4 class="modal-title" id="myModalLabel4">Bundle Detail</h4>
                        </div>
                        <div class="modal-body">
                          <b>Bundle Name: {{$product->name}}</b>
                          <br>
                          <b>Bundle SKU : {{$product->sku}}</b>
                          <hr>
                          <ul>
                            @foreach($product->bundleDetails as $key => $bundle_details)
                            <li>{{$bundle_details->quantity}} X {{$bundle_details->productDetails?->name ?? 'Product Not Found'}}</li>
                            @endforeach
                          </ul>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-light-secondary" data-dismiss="modal">
                            <i class="bx bx-x d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Close</span>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  <button class="btn" data-toggle="modal" data-backdrop="false" data-target="#backdrop{{$product->id}}"><i class="fas fa-eye" title="Click to View Making Details"></i></button>
                @endif
              </td>
              <td>{{$product->sku}}</td>
              <td>{{$product->type}}</td>
              <td>{{$product->category}}</td>
              <td>{{$product->sale_price}}</td>
              <td>{{$product->description}}</td>
              <td>{{ \App\Services\Warehouse\Quantity::display($product->total_qty) }}</td>
              @if(config('warehouses.enabled'))
              <td>
                @if($unassignedProducts->contains('id',$product->id))
                <a class="text-danger" href="{{ route('warehouses.unassigned') }}">Correction required · Unassigned</a>
                @else
                  @forelse($productWarehouseStock->get($product->id,collect()) as $stock)
                  <small class="d-block mb-1"><strong>{{ $stock->warehouse_name }}: {{ \App\Services\Warehouse\Quantity::display($stock->quantity) }}</strong><br>@if($stock->kind==='transfer') In transfer @else @if($stock->rack) Rack {{ $stock->rack }} · @endif Row {{ $stock->row }} · Column {{ $stock->column }} @endif</small>
                  @empty<span class="text-muted">{{ $product->type==='Inventory' ? 'No warehouse stock' : 'Not a physical stock item' }}</span>@endforelse
                  @if($product->type==='Inventory')<a class="small" href="{{ route('warehouses.index',['source_type'=>'product','source_id'=>$product->id]) }}">Manage stock / transfer</a>@endif
                @endif
              </td>
              @endif
              <td>{{$product->re_order}}</td>
              <td>{{$product->air_transit}}</td>
              <td>{{$product->sea_transit}}</td>
              <td>
                <div class="dropdown">
                  <button
                    class="btn btn-primary dropdown-toggle"
                    type="button"
                    id="dropdownMenuButton"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                  >
                    Actions
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <!-- Edit Product Button -->
                    <a class="dropdown-item edit-inventory"
                      href="#"
                      data-name="{{$product->name}}"
                      data-sku="{{$product->sku}}"
                      data-type="{{$product->type}}"
                      data-reorder="{{$product->re_order}}"
                      data-category="{{$product->category}}"
                      data-product_id="{{$product->id}}"
                      data-toggle="modal"
                      data-target="#editInventoryModal"
                    >
                      Edit
                    </a>

                    <!-- Edit Bundle Products Button -->
                    @if($product->type == 'Bundle')
                    <a class="dropdown-item" href="{{route('editBundleProducts', $product->id)}}">
                      Edit Bundle Products
                    </a>
                    @endif

                    <!-- Add Quantity Button -->
                    @if($product->type == 'Inventory')
                    <a class="dropdown-item quantity-button"
                      href="#"
                      data-name="{{$product->name}}"
                      data-sku="{{$product->sku}}"
                      data-type="{{$product->type}}"
                      data-total_qty="{{ \App\Services\Warehouse\Quantity::display($product->total_qty) }}"
                      data-product_id="{{$product->id}}"
                      data-toggle="modal"
                      data-target="#addInventoryModal"
                    >
                      Add Quantity
                    </a>

                    <!-- Run Report Button -->
                    <a class="dropdown-item"
                      href="{{route('inventoryReport', $product->id)}}"
                      target="_blank"
                    >
                      Run Report
                    </a>
                    @endif

                    <!-- Mark as Inactive Button -->
                    @if($product->status == 1)
                    <a class="dropdown-item"
                      href="{{route('deleteProduct', $product->id)}}"
                    >
                      Mark as Inactive
                    </a>
                    @else
                    <a class="dropdown-item"
                      href="{{route('activeProduct', $product->id)}}"
                    >
                      Mark as Active
                    </a>
                    @endif
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div id="pdfTableContainer" style="display:none;"></div>
      </div>
    </div>
    <!-- Modal -->
    <div style="color: black;" class="modal fade" id="addInventoryModal" tabindex="-1" role="dialog" aria-labelledby="addInventoryModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div style="flex: 4;">
              <h5 class="text-center" style="font-weight: 700;">Producy/ Service information</h5>
              <h5 class="modal-title text-center" style="font-weight: 700;">Add Inventory</h5>
            </div>
            <button class="close" data-dismiss="modal" style="background: none; border: none;position: absolute; right: 0; top: 0;" >
              <span aria-hidden="true" style="font-size: 32px;">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form method="post" action="{{route('saveTransitInventoryNew')}}">
              @csrf
              <input type="hidden" name="product_id" class="form-control" id="product_id" readonly>
              <div class="form-group">
                <label for="itemName" class="label-style">Name:</label>
                <input type="text" class="form-control" id="itemName" readonly>
              </div>
              <div class="form-group">
                <label for="itemSku" class="label-style mt-2">SKU:</label>
                <input type="text" class="form-control" id="itemSku" readonly>
              </div>
              <div class="form-group">
                <label for="addQuantity" class="label-style mt-2">Current Quantity:</label>
                <input type="text" class="form-control" id="addQuantity" readonly>
              </div>
              <div class="form-group">
                <label for="addQuantity" class="label-style mt-2">Add New Quantity:</label>
                <input type="number" value="1" name="new_quantity" class="form-control">
              </div>
              <div class="form-group">
                <label for="transitMethod" class="label-style mt-2">Transit:</label>
                <select class="form-select" name="transit_method" required id="transitMethod">
                  <option value="1">By Hand</option>
                  <option value="2">By Air</option>
                  <option value="3">By Sea</option>
                </select>
              </div>
              <div class="form-group">
                <label for="expectedDate" class="label-style mt-2">Expected Date of Delivery:</label>
                <input type="date" min="{{$todayDate}}" name="expected_date" class="form-control" id="expectedDate">
              </div>
              <div class="form-group">
                <label for="productionDate" class="label-style mt-2">Expected Date of Production:</label>
                <input type="date" min="{{$todayDate}}" name="production_date" class="form-control" id="productionDate">
              </div>
              <div class="modal-footer" style="justify-content: start;">
                <div class="button-container"style="border-top: 0px;">
                  <button class="save-button" type="submit">Save and close</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Category Modal -->
    <div style="color: black;" class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div style="flex: 4;">
              <h5 class="text-center" style="font-weight: 700;">Producy/ Service information</h5>
              <h5 class="modal-title text-center" style="font-weight: 700;">Add Category</h5>
            </div>
            <button class="close" data-dismiss="modal" style="background: none; border: none;position: absolute; right: 0; top: 0;" >
              <span aria-hidden="true" style="font-size: 32px;">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <div class="form-group">
                <label for="itemName" class="label-style">Name:</label>
                <input type="text" name="name" class="form-control" id="categoryitemName" >
              </div>
              <div class="modal-footer" style="justify-content: start;">
                <div class="button-container"style="border-top: 0px;">
                  <button class="save-button" onclick="saveCategoryModal()">Save and close</button>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Inventory Asset -->
    <div style="color: black;" class="modal fade" id="addAssetModal" tabindex="-1" role="dialog" aria-labelledby="addAssetModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div style="flex: 4;">
              <h5 class="text-center" style="font-weight: 700;">Producy/ Service information</h5>
              <h5 class="modal-title text-center" style="font-weight: 700;">Add Inventory Asset</h5>
            </div>
            <button class="close" data-dismiss="modal" style="background: none; border: none;position: absolute; right: 0; top: 0;" >
              <span aria-hidden="true" style="font-size: 32px;">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <div class="form-group">
                <label for="itemName" class="label-style">Name:</label>
                <input type="text" name="name" class="form-control" id="assetitemName" >
              </div>
              <div class="modal-footer" style="justify-content: start;">
                <div class="button-container"style="border-top: 0px;">
                  <button class="save-button" onclick="saveAssetModal()">Save and close</button>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Income Account -->
    <div style="color: black;" class="modal fade" id="addIncomeModal" tabindex="-1" role="dialog" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div style="flex: 4;">
              <h5 class="text-center" style="font-weight: 700;">Producy/ Service information</h5>
              <h5 class="modal-title text-center" style="font-weight: 700;">Add Income Account</h5>
            </div>
            <button class="close" data-dismiss="modal" style="background: none; border: none;position: absolute; right: 0; top: 0;" >
              <span aria-hidden="true" style="font-size: 32px;">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <div class="form-group">
                <label for="itemName" class="label-style">Name:</label>
                <input type="text" name="name" class="form-control" id="incomeitemName" >
              </div>
              <div class="modal-footer" style="justify-content: start;">
                <div class="button-container"style="border-top: 0px;">
                  <button class="save-button" onclick="saveIncomeModal()">Save and close</button>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Expense Account -->
    <div style="color: black;" class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div style="flex: 4;">
              <h5 class="text-center" style="font-weight: 700;">Producy/ Service information</h5>
              <h5 class="modal-title text-center" style="font-weight: 700;">Add Expense Account</h5>
            </div>
            <button class="close" data-dismiss="modal" style="background: none; border: none;position: absolute; right: 0; top: 0;" >
              <span aria-hidden="true" style="font-size: 32px;">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <div class="form-group">
                <label for="itemName" class="label-style">Name:</label>
                <input type="text" name="name" class="form-control" id="expenseitemName" >
              </div>
              <div class="modal-footer" style="justify-content: start;">
                <div class="button-container"style="border-top: 0px;">
                  <button class="save-button" onclick="saveExpenseModal()">Save and close</button>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Inventory Modal -->
    <div style="color: black;" class="modal fade" id="editInventoryModal" tabindex="-1" role="dialog" aria-labelledby="editInventoryModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div style="flex: 4;">
              <h5 class="text-center" style="font-weight: 700;">Producy/ Service information</h5>
              <h5 class="modal-title text-center" style="font-weight: 700;">Edit Product Info</h5>
            </div>
            <button class="close" data-dismiss="modal" style="background: none; border: none;position: absolute; right: 0; top: 0;" >
              <span aria-hidden="true" style="font-size: 32px;">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form method="post" action="{{route('updateProductInfo')}}">
              @csrf
              <input type="hidden" name="product_value" class="form-control" id="product_value">
              <div class="form-group">
                <label for="product_name" class="label-style">Product Name:</label>
                <input type="text" name="product_name" required class="form-control" id="product_name">
              </div>
              <div class="form-group">
                <label for="product_sku" class="label-style mt-2">Product SKU:</label>
                <input type="text" name="product_sku" class="form-control" id="product_sku">
              </div>
              <div class="form-group">
                <label for="product_reorder" class="label-style mt-2">Re-Order Point:</label>
                <input type="number" name="product_reorder" class="form-control" id="product_reorder">
              </div>
              <div class="form-group">
                <label for="product_category" class="label-style mt-2">Product Category:</label>
                <select class="form-select" name="product_category" required id="product_category">
                  <!-- Options will be dynamically populated here -->
                  <option selected value="">Choose a category</option>
                </select>
              </div>
              <div class="modal-footer" style="justify-content: start;">
                <div class="button-container"style="border-top: 0px;">
                  <button class="save-button" type="submit">Save and close</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script>
      document.getElementById("inventoryTab").onclick = function () {};

      document.getElementById("openSidebar").onclick = function () {
        document.getElementById("sidebar").style.width = "491px";
        document.querySelector(".overlay").style.display = "block";
      };

      document.getElementById('filterDropdown').addEventListener('click', function(event) {
        event.stopPropagation(); // Prevents clicks inside the dropdown from closing it.
      });

      // Target both the filter icon and the angle-down icon for the click event.
      var toggleIcons = document.querySelectorAll('.opening-filter, .fa-angle-down');
      toggleIcons.forEach(function(icon) {
        icon.addEventListener('click', function(event) {
          var dropdown = document.getElementById('filterDropdown');
          var isVisible = dropdown.style.display === 'block';
          if (!isVisible) {
            dropdown.style.display = 'block';
          } else {
            dropdown.style.display = 'none';
          }
          event.stopPropagation(); // Prevents this click from being captured by window listener.
        });
      });

      // Clicking anywhere outside the dropdown closes it.
      window.onclick = function(event) {
        var dropdown = document.getElementById('filterDropdown');
        if (dropdown.style.display === 'block') {
          dropdown.style.display = 'none';
        }
      };
      // Optimized instant search for large tables
      (function() {
        const input = document.getElementById("searchInput");
        const table = document.querySelector(".table tbody");
        const rows = Array.from(table.getElementsByTagName("tr"));
        // Cache row text content for fast search
        const rowCache = rows.map(row => row.textContent.toLowerCase());

        input.addEventListener('input', function() {
          const filter = input.value.toLowerCase();
          for (let i = 0; i < rows.length; i++) {
            if (!filter || rowCache[i].indexOf(filter) > -1) {
              rows[i].style.display = '';
            } else {
              rows[i].style.display = 'none';
            }
          }
        });
      })();
    </script>
    <script src="{{asset('New/script.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
      function fnExcelReport() {
          const table = document.getElementById("example");
          const tempTable = document.createElement("table");

          // Copy rows, exclude 1st and last column, and modal/button
          for (let i = 0; i < table.rows.length; i++) {
              const row = table.rows[i];
              const newRow = tempTable.insertRow();

              for (let j = 0; j < row.cells.length; j++) {
                  // Skip first (checkbox) and last (Actions) columns
                  if (j === 0 || j === row.cells.length - 1) continue;

                  const cell = row.cells[j];
                  const newCell = newRow.insertCell();

                  // Clean unwanted innerHTML (modals, buttons, icons)
                  let content = cell.innerHTML;

                  // Remove modals and buttons
                  content = content.replace(/<div class="modal[\s\S]*?<\/div>/gi, "");
                  content = content.replace(/<button[\s\S]*?<\/button>/gi, "");
                  content = content.replace(/<i[^>]*><\/i>/gi, "");
                  content = content.replace(/<svg[\s\S]*?<\/svg>/gi, "");

                  // Remove all remaining tags except basic formatting
                  content = content.replace(/<[^>]+>/g, "");

                  newCell.innerHTML = content.trim();
              }
          }

          // Convert to Excel using SheetJS
          const workbook = XLSX.utils.book_new();
          const worksheet = XLSX.utils.table_to_sheet(tempTable);
          XLSX.utils.book_append_sheet(workbook, worksheet, "Products");

          // Download as Excel
          XLSX.writeFile(workbook, "Product_Export.xlsx");
      }
    </script>
    <script>
      function exportTableToPDF() {
          const table = document.getElementById("example");
          const pdfContainer = document.getElementById("pdfTableContainer");
          pdfContainer.innerHTML = ''; // Clear old data

          const tempTable = document.createElement("table");
          tempTable.border = '1';
          tempTable.style.borderCollapse = 'collapse';
          tempTable.style.width = '100%';

          // Style table cells
          const cellStyle = "padding: 8px; border: 1px solid #ccc; font-size: 12px;";

          // Copy rows, exclude 1st and last column
          for (let i = 0; i < table.rows.length; i++) {
              const row = table.rows[i];
              const newRow = tempTable.insertRow();

              for (let j = 0; j < row.cells.length; j++) {
                  if (j === 0 || j === row.cells.length - 1) continue; // Skip checkbox + actions

                  const cell = row.cells[j];
                  const newCell = document.createElement(i === 0 ? 'th' : 'td');
                  newCell.style = cellStyle;

                  let content = cell.innerHTML;

                  // Clean HTML
                  content = content.replace(/<div class="modal[\s\S]*?<\/div>/gi, ""); // Remove modals
                  content = content.replace(/<button[\s\S]*?<\/button>/gi, "");       // Remove buttons
                  content = content.replace(/<i[^>]*><\/i>/gi, "");                   // Remove icons
                  content = content.replace(/<svg[\s\S]*?<\/svg>/gi, "");             // Remove SVGs
                  content = content.replace(/<[^>]+>/g, "");                          // Strip other tags

                  newCell.innerText = content.trim();
                  newRow.appendChild(newCell);
              }
          }

          pdfContainer.appendChild(tempTable);
          pdfContainer.style.display = 'block';

          // Convert to PDF
          const opt = {
              margin: 0.5,
              filename: 'Product_List.pdf',
              image: { type: 'jpeg', quality: 0.98 },
              html2canvas: { scale: 2 },
              jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
          };

          html2pdf().set(opt).from(pdfContainer).save().then(() => {
              pdfContainer.style.display = 'none'; // Hide again
          });
      }
    </script>
    @if(request()->boolean('add_item'))
    <script>document.addEventListener('DOMContentLoaded',function(){document.getElementById('openSidebar')?.click();});</script>
    @endif
  </body>
</html>
