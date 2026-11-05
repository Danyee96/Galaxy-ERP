<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{asset('New/css/styles.css')}}" />
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
    <title>Received Transit Inventory</title>
  </head>
  <body>
    <div class="container mt-4">
      <h2>Received Transit Inventory</h2>
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
            class="nav-link"
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
            class="nav-link active"
            href="{{route('receivedTransitInventory')}}"
            >Received Transit</a
          >
        </li>
      </ul>

      <!-- Statistics Section -->
      <div class="row text-center">
        <div class="col-md-4">
          <div class="stat-card air-of-stock2">
            <div class="air-stock-img">
            </div>
            <div>
              <h3>{{$by_hand}}</h3>
              <p>By Hand</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card air-of-stock2">
            <div class="air-stock-img">
            </div>
            <div>
              <h3>{{$air_transit}}</h3>
              <p>By Air Transit</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
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

      <!-- Table Section -->
      <div class="table-section">
        <table class="table table-hover">
          <thead>
            <tr>
              <th scope="col">SR. #</th>
              <th scope="col">PRODUCT NAME</th>
              <th scope="col">SKU</th>
              <th scope="col">TRANSIT MODE</th>
              <th scope="col">TRANSIT QUANTITY</th>
              <th scope="col">EXPECTED DELIVERY DATE</th>
              <th scope="col">ENTRY BY</th>
              <th scope="col">RECEIVED BY</th>
            </tr>
          </thead>
          <tbody>
            @foreach($product_inventories as $key => $inventory)
            <tr>
              <td>{{++$key}}</td>
              <td>{{$inventory->productDetails ? $inventory->productDetails->name : ''}}</td>
              <td>{{$inventory->productDetails ? $inventory->productDetails->sku : ''}}</td>
              <?php
                $transit_method = '';
                if($inventory->transit_id == 1)
                {
                  $transit_method = 'By Hand';
                }
                elseif ($inventory->transit_id == 2)
                {
                  $transit_method = 'By Air';
                }
                elseif ($inventory->transit_id == 3)
                {
                  $transit_method = 'By Sea';
                }
              ?>
              <td>{{$transit_method}}</td>
              <td>{{$inventory->stock_in}}</td>
              <td>{{$inventory->expected_date}}</td>
              <td>{{$inventory->entryBy ? $inventory->entryBy->name : ''}}</td>
              @if($inventory->transit_id == 1)
              <td>{{$inventory->entryBy ? $inventory->entryBy->name : ''}}</td>
              @else
              <td>{{$inventory->receivedBy ? $inventory->receivedBy->name : ''}}</td>
              @endif
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <script src="{{asset('New/script.js')}}"></script>
  </body>
</html>
