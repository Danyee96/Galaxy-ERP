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
        <h2>{{$name}}</h2>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
      </div>

      <!-- Search Bar -->
      <div class="row mb-3 mt-4">
        <div class="col-md-4">
          <input type="text" id="searchInput" class="form-control" placeholder="Search products... (Name, SKU, Category, etc.)">
        </div>
      </div>

      <!-- Table Section -->
      <div class="table-section">
        <table class="table table-hover" id="productsTable">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">NAME</th>
              <th scope="col">SKU</th>
              <th scope="col">TYPE</th>
              <th scope="col">CATEGORY</th>
              <th scope="col">COST</th>
              <th scope="col">SALES DESCRIPTION</th>
              <th scope="col">QTY ON HAND</th>
              <th scope="col">REORDER POINT</th>
              <th scope="col">TRANSIT BY AIR</th>
              <th scope="col">TRANSIT BY SEA</th>
            </tr>
          </thead>
          <tbody>
            @foreach($products as $key => $product)
            <tr>
              <td>{{++$key}}</td>
              <td>{{$product->name}}</td>
              <td>{{$product->sku}}</td>
              <td>{{$product->type}}</td>
              <td>{{$product->category}}</td>
              <td>{{$product->sale_price}}</td>
              <td>{{$product->description}}</td>
              <td>{{$product->total_qty}}</td>
              <td>{{$product->re_order}}</td>
              <td>{{$product->air_transit}}</td>
              <td>{{$product->sea_transit}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const searchInput = document.getElementById('searchInput');
          const table = document.getElementById('productsTable');
          const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

          searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            for (let i = 0; i < rows.length; i++) {
              let rowText = rows[i].textContent.toLowerCase();
              if (rowText.indexOf(filter) > -1) {
                rows[i].style.display = '';
              } else {
                rows[i].style.display = 'none';
              }
            }
          });
        });
      </script>
    </div>
  </body>
</html>
