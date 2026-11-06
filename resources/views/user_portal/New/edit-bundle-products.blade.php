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
    <title>Edit Bundle Products | {{ config('brand.name') }}</title>
    <style>
      .product-row {
        margin-bottom: 15px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 5px;
      }
      .remove-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 3px;
        cursor: pointer;
      }
      .remove-btn:hover {
        background-color: #c82333;
      }
      .add-more-btn {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 3px;
        cursor: pointer;
        margin-top: 10px;
      }
      .add-more-btn:hover {
        background-color: #218838;
      }
    </style>
    @include('branding.head')
  </head>
  <body class="galaxy-workspace">
    @include('branding.header')
    <div class="container mt-4">
      <h2>Edit Bundle Products</h2>
      
      <!-- Breadcrumbs -->
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          @foreach ($breadcrumbs as $breadcrumb)
            @if(isset($breadcrumb['link']))
              <li class="breadcrumb-item"><a href="{{ $breadcrumb['link'] }}">{{ $breadcrumb['name'] }}</a></li>
            @else
              <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['name'] }}</li>
            @endif
          @endforeach
        </ol>
      </nav>

      <div class="card">
        <div class="card-body">
          <form action="{{ route('updateBundleProducts', $bundle->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('POST')

            <!-- Bundle Basic Info -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="bundle_name" class="form-label">Bundle Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="bundle_name" name="bundle_name" value="{{ $bundle->name }}" required>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="bundle_sku" class="form-label">Bundle SKU</label>
                  <input type="text" class="form-control" id="bundle_sku" name="bundle_sku" value="{{ $bundle->sku }}">
                </div>
              </div>
            </div>

            <hr class="my-4">

            <!-- Bundle Products Section -->
            <h4 class="mb-3">Bundle Products</h4>
            <div id="products-container">
              @if(count($bundle_recipes) > 0)
                @foreach($bundle_recipes as $index => $recipe)
                  <div class="product-row" data-index="{{ $index }}">
                    <div class="row align-items-end">
                      <div class="col-md-6">
                        <label class="form-label">Product <span class="text-danger">*</span></label>
                        <select class="form-select product-select" name="product[]" required>
                          <option value="">Select Product</option>
                          @foreach($all_products as $product)
                            <option value="{{ $product->id }}" {{ $recipe->product_id == $product->id ? 'selected' : '' }}>
                              {{ $product->name }} ({{ $product->sku }})
                            </option>
                          @endforeach
                        </select>
                      </div>
                      
                      <div class="col-md-4">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity[]" value="{{ $recipe->quantity }}" min="1" required>
                      </div>
                      
                      <div class="col-md-2">
                        <button type="button" class="remove-btn btn-remove-product w-100">Remove</button>
                      </div>
                    </div>
                  </div>
                @endforeach
              @else
                <div class="product-row" data-index="0">
                  <div class="row align-items-end">
                    <div class="col-md-6">
                      <label class="form-label">Product <span class="text-danger">*</span></label>
                      <select class="form-select product-select" name="product[]" required>
                        <option value="">Select Product</option>
                        @foreach($all_products as $product)
                          <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                      </select>
                    </div>
                    
                    <div class="col-md-4">
                      <label class="form-label">Quantity <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" name="quantity[]" value="1" min="1" required>
                    </div>
                    
                    <div class="col-md-2">
                      <button type="button" class="remove-btn btn-remove-product w-100">Remove</button>
                    </div>
                  </div>
                </div>
              @endif
            </div>

            <button type="button" class="add-more-btn" id="add-product-btn">
              <i class="fas fa-plus"></i> Add More Products
            </button>

            <hr class="my-4">

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-between">
              <a href="{{ route('productService') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Cancel
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Bundle
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
      $(document).ready(function() {
        // Initialize Select2 for existing dropdowns
        $('.product-select').select2({
          placeholder: 'Select a product',
          allowClear: true,
          width: '100%'
        });

        let productIndex = {{ count($bundle_recipes) > 0 ? count($bundle_recipes) : 1 }};

        // Function to get all selected product IDs
        function getSelectedProductIds() {
          const selectedIds = [];
          $('.product-select').each(function() {
            const value = $(this).val();
            if (value) {
              selectedIds.push(value);
            }
          });
          return selectedIds;
        }

        // Function to update all dropdowns to hide selected products
        function updateAllDropdowns() {
          const selectedIds = getSelectedProductIds();
          
          $('.product-select').each(function() {
            const currentSelect = $(this);
            const currentValue = currentSelect.val();
            
            // Hide all options that are selected in other dropdowns
            currentSelect.find('option').each(function() {
              const optionValue = $(this).val();
              if (optionValue && optionValue !== currentValue && selectedIds.includes(optionValue)) {
                $(this).prop('disabled', true).hide();
              } else if (optionValue) {
                $(this).prop('disabled', false).show();
              }
            });
            
            // Trigger Select2 to refresh
            currentSelect.select2('destroy').select2({
              placeholder: 'Select a product',
              allowClear: true,
              width: '100%'
            });
          });
        }

        // Update dropdowns on page load
        updateAllDropdowns();

        // Listen for changes on product selection
        $(document).on('change', '.product-select', function() {
          updateAllDropdowns();
        });

        // Add more products
        $('#add-product-btn').on('click', function() {
          const productOptions = `@foreach($all_products as $product)
            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
          @endforeach`;

          const newRow = `
            <div class="product-row" data-index="${productIndex}">
              <div class="row align-items-end">
                <div class="col-md-6">
                  <label class="form-label">Product <span class="text-danger">*</span></label>
                  <select class="form-select product-select" name="product[]" required>
                    <option value="">Select Product</option>
                    ${productOptions}
                  </select>
                </div>
                
                <div class="col-md-4">
                  <label class="form-label">Quantity <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" name="quantity[]" value="1" min="1" required>
                </div>
                
                <div class="col-md-2">
                  <button type="button" class="remove-btn btn-remove-product w-100">Remove</button>
                </div>
              </div>
            </div>
          `;

          $('#products-container').append(newRow);
          
          // Initialize Select2 for the new dropdown
          $(`[data-index="${productIndex}"] .product-select`).select2({
            placeholder: 'Select a product',
            allowClear: true,
            width: '100%'
          });

          productIndex++;
          
          // Update all dropdowns to hide already selected products
          updateAllDropdowns();
        });

        // Remove product row
        $(document).on('click', '.btn-remove-product', function() {
          const productRows = $('.product-row').length;
          if (productRows > 1) {
            $(this).closest('.product-row').remove();
            // Update remaining dropdowns after removal
            updateAllDropdowns();
          } else {
            alert('At least one product must be in the bundle.');
          }
        });

        // Form validation
        $('form').on('submit', function(e) {
          const products = $('select[name="product[]"]');
          const selectedValues = [];
          let hasDuplicates = false;

          products.each(function() {
            const value = $(this).val();
            if (value) {
              if (selectedValues.includes(value)) {
                hasDuplicates = true;
                return false;
              }
              selectedValues.push(value);
            }
          });

          if (hasDuplicates) {
            e.preventDefault();
            alert('Duplicate products are not allowed in the bundle. Please select different products.');
            return false;
          }
        });
      });
    </script>
  </body>
</html>
