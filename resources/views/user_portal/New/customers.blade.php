<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{asset('New/css/styles.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Sen:wght@400..800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  
    <title>Customers</title>
</head>
<body>
    
    <div class="container mt-4">
        <h2>Customers</h2>
        <!-- Tabs Section -->
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a style="font-weight: 200; color: black" class="nav-link" href="{{route('index')}}">Back to Dashboard</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('getInvoice')}}">Invoices</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productEstimates')}}">Estimates</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productEstimatesCalculation')}}">Estimate Calculations</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link active" href="{{route('getCustomer')}}">Customers</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('productService')}}">Products & Services</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('inTransitInventory')}}">In Transit</a>
            </li>
            <li class="nav-item">
                <a style="font-weight:200; color: black;" class="nav-link" href="{{route('receivedTransitInventory')}}">Received Transit</a>
            </li>
        </ul>

        @if(count($customers) == 0)
        <div class="row content-detail2">
            <div class="col-md-8">
                <div class="content-card">
                    <h1>Keep track of who needs <br> to pay you</h1>
                    <p><a href="">See how it works</a></p>
                    <ul>
                        <li>Find all your customers and their info in one place</li>
                        <li>See who your top customers are</li>
                        <li>Track invoices to see who owes you money</li>
                    </ul>
                    <div class="mt-3">
                    <button class="btn btn-create estimate-button">Import customers</button>
                    <button id="addCustomerManually" class="btn btn-create estimate-button-black">Add customer manually</button>
                </div>
                </div>
            </div>
            <div class="col-md-4 text-center image2">
                <img src="{{asset('New/images/709d22889bdee8948402d6b73c330214.svg')}}" alt="Illustration" class="illustration">
            </div>
        </div>
        <div class="tips-resources">
            <h5>TIPS & RESOURCES</h5>
            <a href="#">How to create an invoice</a>
            <a href="#">How to add a customer or sub-customer</a>
            <a href="#">See more</a>
        </div>
        @else
        <!-- Title Section -->
        <div class="header-section">
        <h2>Customers</h2>

        <div class="btn-group">
          <!-- Simple button -->
          <button id="addCustomerManually" class="btn new-button">
            New Customer
          </button>
        </div>
        <!-- Bootstrap JS, Popper.js, and jQuery -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
      </div>

      <!-- Statistics Section -->
      <div class="row text-center">
        <div class="col-md-3">
          <div class="stat-card low-stock2">
            <div class="low-stock-img">
            </div>
            <div>
              <h3>0</h3>
              <p>Un-Billed Amount</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card out-of-stock2">
            <div class="out-stock-img">
            </div>
            <div>
              <h3>0</h3>
              <p>Overdue Invoices</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card air-of-stock2">
            <div class="air-stock-img">
            </div>
            <div>
              <h3>0</h3>
              <p>Open Invoices</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card sea-of-stock2">
            <div class="sea-stock-img">
            </div>
            <div>
              <h3>0</h3>
              <p>Recently Paid</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Search Section -->
      <div class="search-section">
        <input type="text" style="width: 300px" id="searchInput" class="form-control" placeholder="Find products and services" onkeyup="filterTable()" />
      </div>

      <!-- Table Section -->
      <div class="table-section">
        <table class="table table-hover">
          <thead>
            <tr>
              <th scope="col"><input type="checkbox" /></th>
              <th scope="col">NAME</th>
              <th scope="col">COMPANY NAME</th>
              <th scope="col">PHONE</th>
              <th scope="col">OPENING BALANCE</th>
              <th scope="col">ACTION</th>
            </tr>
          </thead>
          <tbody>
            @foreach($customers as $key => $customer)
            <tr>
              <td><input type="checkbox" /></td>
              <td>{{$customer->display_name}}</td>
              <td>{{$customer->company_name}}</td>
              <td>{{$customer->phone}}</td>
              @if(!empty($customer->opening_balance))
              <td>Rs. {{$customer->opening_balance}}</td>
              @else
              <td>Rs. 0.00</td>
              @endif
              <td>
                <button class="btn btn-link">Edit</button>
                <button class="btn btn-link quantity-button"
                  data-name="{{$customer->name}}"
                  data-sku="{{$customer->sku}}"
                  data-type="{{$customer->type}}"
                  data-total_qty="{{$customer->total_qty}}"
                  data-product_id="{{$customer->id}}"
                  data-toggle="modal"
                  data-target="#addInventoryModal">
                  Add Quantity
                </button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif




        <div id="customerSidebar" class="sidebar customer-sidebar">
            <div class="customer-service-info">
                <h4>Customer</h4>
                <i class="fas fa-times close-customer-sidebar" id="closeCustomerSidebar"></i>
            </div>
            <div class="customer-info-item">
                <form id="customerForm" action="{{route('saveCustomer')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="fixing-gap" id="accordion">
                        <div class="card mc-4">
                          <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <div class="btn  contact" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <span class="icon-styling"><svg class="svg-styling" aria-hidden="true" focusable="false" viewBox="0 0 24 24" class="" width="24" height="24" fill="currentColor"><path d="M17.862 2H7.138A3.142 3.142 0 004 5.138V6a1 1 0 000 2v3a1 1 0 000 2v3a1 1 0 000 2v.862A3.142 3.142 0 007.138 22h10.724A3.142 3.142 0 0021 18.862V5.138A3.142 3.142 0 0017.862 2zM19 18.862A1.14 1.14 0 0117.862 20H7.138A1.139 1.139 0 016 18.862V18a1 1 0 000-2v-3a1 1 0 000-2V8a1 1 0 000-2v-.862A1.14 1.14 0 017.138 4h10.724A1.14 1.14 0 0119 5.138v13.724z"></path><path d="M13.785 12.234c.05-.04.1-.086.151-.134.379-.38.591-.896.592-1.433v-.889a2.036 2.036 0 00-.6-1.436 2.078 2.078 0 00-2.869 0 2.03 2.03 0 00-.592 1.433v.89a2.03 2.03 0 00.743 1.566 2.03 2.03 0 00-1.632 1.988V15a1.252 1.252 0 001.25 1.25h3.334A1.254 1.254 0 0015.417 15v-.778a2.03 2.03 0 00-1.632-1.988z"></path></svg></i>Name and contact</span>
                                    <i class="bi bi-chevron-down ms-auto"></i> <!-- Bootstrap Icons right arrow -->
                                </div>
                            </h5>
                          </div>
                          <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-bs-parent="#accordion">
                            <div class="card-body">
                                    <div class="row g-3 sizing-text">
                                        <div class="col-md-2">
                                            <label for="title" class="form-label small">Title</label>
                                            <input type="text" name="title" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="firstName" class="form-label small">First name</label>
                                            <input type="text" name="first_name" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="middleName" class="form-label small">Middle name</label>
                                            <input type="text" name="middle_name" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="lastName" class="form-label small">Last name</label>
                                            <input type="text" name="last_name" class="form-control" >
                                        </div>
                                        <div class="col-md-2">
                                            <label for="suffix" class="form-label small">Suffix</label>
                                            <input type="text" name="suffix" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="displayName" class="form-label small">Customer display name</label>
                                            <input type="text" name="display_name" required class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Company name" class="form-label small">Company name</label>
                                            <input type="text" name="company_name" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Email" class="form-label small">Email</label>
                                            <input type="email" name="email" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Phone number" class="form-label small">Phone number</label>
                                            <input type="text" name="phone" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Mobile number" class="form-label small">Mobile number</label>
                                            <input type="text" name="mobile" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Fax" class="form-label small">Fax</label>
                                            <input type="text" name="fax" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Other" class="form-label small">Other</label>
                                            <input type="text" name="other" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Website" class="form-label small">Website</label>
                                            <input type="text" name="website" class="form-control">
                                        </div>
                                        <!-- <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="subCustomerCheck">
                                                <label class="form-check-label" for="subCustomerCheck">
                                                    Is a sub-customer
                                                </label>
                                            </div>
                                        </div> -->
                                    </div>
                          </div>
                        </div>
                      </div>
                        <div class="card">
                          <div class="card-header" id="headingTwo">
                            <h5 class="mb-0">
                                <div class="btn  contact" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                    <span class="icon-styling"><svg class="svg-styling" aria-hidden="true" focusable="false" viewBox="0 0 24 24" class="" width="24" height="24" fill="currentColor"><path d="M12 14.5a4 4 0 110-8 4 4 0 010 8zm0-6a2 2 0 100 4 2 2 0 000-4z"></path><path d="M12 22a1 1 0 01-.858-.486L9.383 18.58a8.5 8.5 0 1110.97-9.68 8.454 8.454 0 01-5.737 9.681l-1.758 2.934A1 1 0 0112 22zm.018-18A6.493 6.493 0 0010.3 16.763c.251.068.466.23.6.453l1.1 1.838 1.1-1.838a1 1 0 01.6-.452 6.5 6.5 0 00-.4-12.638A6.801 6.801 0 0012.018 4z"></path></svg>Addresses</span>
                                    <i class="bi bi-chevron-down ms-auto"></i> <!-- Bootstrap Icons right arrow -->
                                </div>
                            </h5>
                          </div>
                          <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-bs-parent="#accordion">
                            <div class="card-body">
                                <p class="shipping-address">Billing Address</p>
                                    <div class="row g-3 sizing-text">
                                        <div class="col-md-6">
                                            <label for="Street address 1" class="form-label small">Street address 1</label>
                                            <input type="text" name="street_address_one" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Street address 2" class="form-label small">Street address 2</label>
                                            <input type="text" name="street_address_two" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="City" class="form-label small">City</label>
                                            <input type="email" name="city" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Province" class="form-label small">Province</label>
                                            <input type="text" name="province" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Postal code" class="form-label small">Postal code</label>
                                            <input type="text" name="postal_code" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Country" class="form-label small">Country</label>
                                            <input type="text" name="country" class="form-control">
                                        </div>
                                        
                                        <!-- <div class="col-12">
                                            <p class="shipping-address">Shipping address</p>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="subCustomerCheck">
                                                <label class="form-check-label" for="subCustomerCheck">
                                                    Same as billing address
                                                </label>
                                            </div>
                                        </div> -->
                                    </div>
                          </div>
                          </div>
                        </div>
                      
                        <div class="card">
                          <div class="card-header" id="headingThree">
                            <h5 class="mb-0">
                              
                              <div class="btn  contact" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
                                <span class="icon-styling"><svg class="svg-styling" aria-hidden="true" focusable="false" viewBox="0 0 24 24" class="" width="24" height="24" fill="currentColor"><path d="M21.013 10a1.024 1.024 0 00-1 1l-.02 8.014a1 1 0 01-1 1l-14-.02a1 1 0 01-1-1l.02-14a1 1 0 011-1L13 4.01a1 1 0 100-2l-7.984-.02H5.01a3 3 0 00-3 3l-.02 14a3 3 0 003 3l14 .02h.006a3 3 0 003-2.994L22.01 11a1 1 0 00-.997-1z"></path><path d="M8.975 10.885L8 14.755a1 1 0 001.212 1.215l3.873-.962a1 1 0 00.465-.262l7.756-7.732a2.373 2.373 0 000-3.35l-.962-.964A2.359 2.359 0 0018.67 2a2.348 2.348 0 00-1.672.69l-7.759 7.731a1 1 0 00-.264.464zm1.872.757l7.559-7.536a.369.369 0 01.521.001l.966.969a.374.374 0 010 .522l-7.559 7.536-1.986.494.499-1.986z"></path></svg>Notes and attachments</span>
                                <i class="bi bi-chevron-down ms-auto"></i> <!-- Bootstrap Icons right arrow -->
                            </div>
                            </h5>
                          </div>
                          <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-bs-parent="#accordion">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="notes" class="form-label sizing-text2">Notes</label>
                                    <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="attachments" class="form-label sizing-text2">Attachments</label>
                                    <input type="file" class="form-control" name="attachment" id="attachments" style="display: none;">
                                    <label for="attachments" class="btn btn-outline-secondary d-block p-4 text-center attachment" style="border-style: dashed;">
                                        Add attachment
                                        <small class="text-muted d-block mb-2">Max file size: 20 MB</small>
                                    </label>
                                    
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingFour">
                              <h5 class="mb-0">
                                
                                <div class="btn  contact" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="true" aria-controls="collapseFour">
                                  <span class="icon-styling"><svg class="svg-styling" aria-hidden="true" focusable="false" viewBox="0 0 24 24" class="" width="24" height="24" fill="currentColor"><path d="M21.133 4.892a2.982 2.982 0 00-2.12-.882l-14-.02h-.005a3 3 0 00-3 3l-.015 10a3 3 0 003 3h2a1 1 0 100-2h-2a1 1 0 01-1-1l.009-6 16 .024-.009 6a1 1 0 01-1 1h-2a1 1 0 000 2H19a3 3 0 003-3l.015-10a2.981 2.981 0 00-.882-2.122zM4.006 8.988v-2a1 1 0 011-1l14 .02a1 1 0 011 1v2l-16-.02z"></path><path d="M15.707 15.293a1 1 0 00-1.414 0L12 17.586l-.793-.793a1 1 0 00-1.414 1.414l1.5 1.5a1 1 0 001.414 0l3-3a1 1 0 000-1.414z"></path></svg>Payments</span>
                                  <i class="bi bi-chevron-down ms-auto"></i> <!-- Bootstrap Icons right arrow -->
                              </div>
                              </h5>
                            </div>
                            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-bs-parent="#accordion">
                              <div class="card-body">
                                      <div class="row g-3 sizing-text">
                                          
                                          <div class="col-md-6">
                                              <label for="displayName" class="form-label small">Primary payment method</label>
                                              <select class="form-select" name="payment_method" id="primaryPaymentMethod">
                                                <option selected value="">Choose...</option>
                                                <option value="Cash">Cash</option>
                                                <option value="Cheque">Cheque</option>
                                                <option value="Direct Debit">Direct Debit</option>
                                                <option value="Credit Card">Credit Card</option>
                                            </select>
                                          </div>
                                          <div class="col-md-6">
                                              <label for="suffix" class="form-label small">Terms
                                            </label>
                                            <select class="form-select" name="terms" id="terms">
                                                <option selected value="">Choose...</option>
                                                <option value="Due on Receipt">Due on Receipt</option>
                                                <option value="Net 15">Net 15</option>
                                                <option value="Net 30">Net 30</option>
                                                <option value="Net 60">Net 60</option>
                                            </select>
                                          </div>
                                          <div class="col-md-6">
                                              <label for="suffix" class="form-label small">Sales form delivery options
                                            </label>
                                            <select class="form-select" name="delivery_option" id="salesFormDeliveryOptions">
                                                <option selected value="Use Company Default">Use company default</option>
                                                <option value="Print Later">Print Later</option>
                                                <option value="Send Later">Send Later</option>
                                                <option value="None">None</option>
                                            </select>
                                          </div>
                                          <div class="col-md-6">
                                              <label for="suffix" class="form-label small">Language to use when you send invoices</label>
                                              <select class="form-select" name="language" id="language">
                                                <option value="English" selected>English</option>
                                                <option value="French">French</option>
                                                <option value="Spanish">Spanish</option>
                                            </select>
                                          </div>
                                          
                                          
                                      </div>
                            </div>
                            </div>
                          </div>
                          <div class="card">
                            <div class="card-header" id="headingFive">
                              <h5 class="mb-0">
                                
                                <div class="btn  contact" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="true" aria-controls="collapseFive">
                                  <span class="icon-styling"><svg class="svg-styling" aria-hidden="true" focusable="false" viewBox="0 0 24 24" class="" width="24" height="24" fill="currentColor"><path d="M20.988 8.939a1 1 0 00-.054-.265.973.973 0 00-.224-.374v-.005l-6-6a1 1 0 00-.283-.191c-.031-.014-.064-.022-.1-.034a.992.992 0 00-.259-.052C14.042 2.011 14.023 2 14 2H6a3 3 0 00-3 3v14a3 3 0 003 3h12a3 3 0 003-3V9c0-.022-.011-.04-.012-.061zM15 5.414L17.586 8H16a1 1 0 01-1-1V5.414zM18 20H6a1 1 0 01-1-1V5a1 1 0 011-1h7v3a3 3 0 003 3h3v9a1 1 0 01-1 1z"></path><path d="M7 10h3a1 1 0 100-2H7a1 1 0 000 2zm7 3H7a1 1 0 000 2h7a1 1 0 000-2zm0 3H7a1 1 0 000 2h7a1 1 0 000-2z"></path></svg>Addtional info</span>
                                  <i class="bi bi-chevron-down ms-auto"></i> <!-- Bootstrap Icons right arrow -->
                              </div>
                              </h5>
                            </div>
                            <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-bs-parent="#accordion">
                              <div class="card-body">
                                      <div class="row g-3 sizing-text">
                                        <p class="shipping-address">Taxes</p>
                                          <div class="col-md-6">
                                              <label for="registration" class="form-label small">Sales tax registration</label>
                                              <input type="text" name="taxes" class="form-control">
                                          </div>
                                          <div class="col-md-6">

                                          </div>
                                          <p class="shipping-address">Opening balance</p>
                                          <div class="col-md-6">
                                              <label for="Opening balance" class="form-label small">Opening balance
                                            </label>
                                            <input type="text" name="opening_balance" class="form-control">
                                          </div>
                                          <div class="col-md-6">
                                              <label for="As of" class="form-label small">
                                                As of
                                            </label>
                                            <input type="date" name="date" class="form-control">
                                          </div>
                                          
                                          
                                          
                                      </div>
                            </div>
                            </div>
                          </div>
                      </div>
                      
                    <!-- <button type="submit" class="btn btn-success">Save Customer</button> -->
                    <div class="button-container">
                        <button class="save-button change-color">Save</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="overlay-customer" onclick="closeSidebar()"></div>
        <!-- <div class="overlay-customer" id="overlayCustomer" onclick="closeCustomerSidebar()"></div> -->
    </div>
    <script src="{{asset('New/customerScript.js')}}"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toLowerCase();
        const table = document.querySelector(".table tbody");
        const rows = table.getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
          const cells = rows[i].getElementsByTagName("td");
          let match = false;

          // Check if any cell in the row contains the search term
          for (let j = 0; j < cells.length; j++) {
            if (cells[j]) {
              const cellValue = cells[j].textContent || cells[j].innerText;
              if (cellValue.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
              }
            }
          }

          // Show or hide the row based on the search match
          rows[i].style.display = match ? "" : "none";
        }
      }
    </script>
</body>
</html>