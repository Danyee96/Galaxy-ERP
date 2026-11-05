function setDefaultSidebarContent() {
    document.getElementById("sidebar").innerHTML = `
    <div class="service-info">
      <h4>Product/Service Information</h4>
      <i class="fas fa-times close-icon" id="closeSidebar"></i>
    </div>
    <div class="info-item">
              <div class="shirt"></div>
              <div id="inventoryTab" class="inventory">
                <strong>Inventory</strong>
                <p>
                  Products you buy and/or sell that you track quantities of.
                </p>
              </div>
            </div>
            <div class="info-item">
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
              <div  class="bundle"></div>
              <div class="inventory">
                <strong>Bundle</strong>
                <p>
                  A collection of products and/or services that you sell
                  together, for example, a gift basket of fruit, cheese, and
                  wine.
                </p>
              </div>
            </div>
    <!-- Additional items -->
  `;
  attachEvents();
}

function attachEvents() {
    document.getElementById("closeSidebar").onclick = closeSidebar;
    document.getElementById("openSidebar").onclick = openSidebar;
    document.getElementById("inventoryTab").onclick = inventoryTabClicked;
    document.getElementById("noninventoryTab").onclick = nonInventoryTabClicked;
    document.getElementById("service").onclick = serviceTabClicked;
    document.getElementById("bundle").onclick = bundleTabClicked;
  }
  
  function inventoryTabClicked() {
    console.log("Inventory clicked");
    updateSidebarContent('Inventory');
  }
  
  function nonInventoryTabClicked() {
    console.log("Non-Inventory clicked");
    updateSidebarContent('Non-Inventory');
  }
  
  function serviceTabClicked() {
    console.log("Service clicked");
    updateSidebarContent('Service');
  }
  
  function bundleTabClicked() {
    console.log("Bundle clicked");
    updateSidebarContent('Bundle');
  }
  function attachCloseButtonListener() {
    const closeButton = document.getElementById("closeSidebar");
    if (closeButton) {
      closeButton.onclick = closeSidebar; // Attach the event listener
    }
  }
  function updateSidebarContent(type) {
    const sidebar = document.getElementById("sidebar");
    let content = "";
    switch (type) {
        case 'Inventory':
          content = `
          <div class="sidebar-header">
          <div class="service-info2">
            <div class="product-info"><h4>Product/Service Information</h4>
              <i class="fas fa-times close-icon" id="closeSidebar"></i></div>
            <div class="inven-imag">
              <img src="New/images/shirt.png" alt="">
              <p>Inventory</p>
            </div>
          </div>
          
        </div>
        <div class="sidebar-content sizing-sidebar">
          <form style="padding: 0px 11px" method="GET">
            <input type="hidden" name="type" value="Inventory">
            <div class="row image-row mt-3">
              <div class="col-6">
                <div class="mb-3">
                  <label for="name" class="form-label">Name*</label>
                  <input
                    type="text"
                    class="form-control"
                    id="name" name="name"
                    placeholder="Enter name"
                  />
                </div>
                <div class="mb-3">
                  <label for="sku" class="form-label">SKU</label>
                  <input
                    type="text"
                    class="form-control"
                    id="sku" name="sku"
                    placeholder="SKU"
                  />
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <!-- <label for="productImage" class="form-label">Product Image</label> -->
                  <input
                    type="file"
                    class="form-control"
                    id="productImage" name="image"
                    onchange="previewImage();"
                  />
                  <img
                    id="imagePreview"
                    src="#"
                    alt="Image Preview"
                    style="width: 100%; margin-top: 10px; display: none"
                  />
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="category" class="form-label">Category</label>
              <a class="info3" data-toggle="modal" data-target="#addCategoryModal">Want to Add New Category?</a>
              <select class="form-select" name="category" id="categoryDropdown"></select>
            </div>
            <div class="form-row initial-gap">
              <div class="col-6">
              <label for="quantity">Initial quantity on hand*</label></div>
              <div class="col-4">
              <input type="number" id="quantity" name="initial_qty" class="form-control" /></div>
            </div>
            <div class="form-row initial-gap">
              <div class="col-6">
                <label for="date">As of date*</label>
              </div>
              <div class="col-4">
              <input type="date" name="date" id="date" class="form-control" />
            </div>
            </div>
            <div class="form-row initial-gap">
              <div class="col-6">
              <label for="reorderPoint">Reorder point</label> 
              </div>
              <div class="col-4 ">
              <input type="number" min="0" name="re_order" id="reorderPoint" class="form-control" />
            </div>
            </div>
            <div class="mb-3">
              <label for="inventoryAsset" class="form-label"
                >Inventory asset account</label
              >
              <a class="info3" data-toggle="modal" data-target="#addAssetModal">Want to Add New Inventory Asset?</a>
              <select class="form-select" name="inventory_asset" id="assetDropdown"></select>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label"
                >Description</label
              >
              <textarea
                class="form-control"
                id="description"
                rows="3" name="description"
                placeholder="Description on sales forms"
              ></textarea>
            </div>
            <div class="row">
              <div class="col-6">
                <div class="mb-3">
                  <label for="salesPrice" class="form-label"
                    >Sales price/rate</label
                  >
                  <input
                    type="number"
                    class="form-control"
                    id="salesPrice" name="sale_price" min="0"
                    placeholder="Sales Price/Rate"
                  />
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <label for="incomeAccount" class="form-label"
                    >Income account</label
                  >
                  <a class="info3" data-toggle="modal" data-target="#addIncomeModal">Add New?</a>
                  <select class="form-select" name="income_account" id="incomeDropdown"></select>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="purchaseDescription" class="form-label"
                >Purchasing information</label
              >
              <textarea
                class="form-control"
                id="purchaseDescription"
                rows="3" name="purchase_info"
                placeholder="Description on purchase forms"
              ></textarea>
            </div>
            <div class="row">
              <div class="col-6">
                <div class="mb-3">
                  <label for="cost" class="form-label">Cost</label>
                  <input
                    type="number"
                    class="form-control"
                    id="cost" name="cost" min="0"
                    placeholder="Cost"
                  />
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <label for="expenseAccount" class="form-label"
                    >Expense account</label
                  >
                  <a class="info3" data-toggle="modal" data-target="#addExpenseModal">Add New?</a>
                  <select class="form-select" name="expense_account" id="expenseDropdown"></select>
                </div>
              </div>
            </div>
          <div class="button-container">
            <button class="save-button" type="submit">Save and close</button>
        </div>
          </form>
        </div>
          `;
          break;
        case 'Non-Inventory':
          content = `
                 <div class="sidebar-header">
              <div class="service-info2">
                <div class="product-info"><h4>Product/Service Information</h4>
                  <i class="fas fa-times close-icon" id="closeSidebar"></i></div>
                <div class="inven-imag">
                  <img src="New/images/shirt.png" alt="">
                  <p>Non-Inventory</p>
                </div>
              </div>
              
            </div>
            <div class="sidebar-content sizing-sidebar">
              <form style="padding: 0px 11px" method="GET">
                <input type="hidden" name="non_type" value="Non-Inventory">
                <div class="row image-row mt-3">
                  <div class="col-6">
                    <div class="mb-3">
                      <label for="name" class="form-label">Name*</label>
                      <input
                        type="text"
                        class="form-control"
                        id="name" name="non_name"
                        placeholder="Enter name"
                      />
                    </div>
                    <div class="mb-3">
                      <label for="sku" class="form-label">SKU</label>
                      <input
                        type="text"
                        class="form-control"
                        id="sku" name="non_sku"
                        placeholder="SKU"
                      />
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                      <input
                        type="file"
                        class="form-control"
                        id="productImage" name="non_image"
                        onchange="previewImage();"
                      />
                      <img
                        id="imagePreview"
                        src="#"
                        alt="Image Preview"
                        style="width: 100%; margin-top: 10px; display: none"
                      />
                    </div>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="category" class="form-label">Category</label>
                  <a class="info3" data-toggle="modal" data-target="#addCategoryModal">Want to Add New Category?</a>
                  <select class="form-select" name="non-category" id="categoryDropdown"></select>
                </div>
                <div class="mb-3">
                  <label for="description" class="form-label"
                    >Description</label
                  >
                  <textarea
                    class="form-control"
                    id="description"
                    rows="3" name="non_description"
                    placeholder="Description on sales forms"
                  ></textarea>
                </div>
                <div class="row">
                  <div class="col-6">
                    <div class="mb-3">
                      <label for="salesPrice" class="form-label"
                        >Sales price/rate</label
                      >
                      <input
                        type="number" min="0"
                        class="form-control"
                        id="salesPrice" name="non_sale_price"
                        placeholder="Sales Price/Rate"
                      />
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                      <label for="incomeAccount" class="form-label"
                        >Income account</label
                      >
                      <a class="info3" data-toggle="modal" data-target="#addIncomeModal">Add New?</a>
                      <select class="form-select" name="income_account" id="incomeDropdown"></select>
                    </div>
                  </div>
                </div>
              <div class="button-container">
                <button class="save-button" type="submit">Save and close</button>
            </div>
              </form>
            </div>
          `;
          break;
        case 'Service':
          content = `
               <div class="sidebar-header">
            <div class="service-info2">
            <div class="product-info"><h4>Product/Service Information</h4>
                <i class="fas fa-times close-icon" id="closeSidebar"></i></div>
            <div class="inven-imag">
                <img src="New/images/shirt.png" alt="">
                <p>Service</p>
            </div>
            </div>
            
        </div>
        <div class="sidebar-content sizing-sidebar">
            <form style="padding: 0px 11px">
            <input type="hidden" name="s_type" value="Service">
            <div class="row image-row mt-3">
                <div class="col-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Name*</label>
                    <input
                    type="text"
                    class="form-control"
                    id="name" name="s_name"
                    placeholder="Enter name"
                    />
                </div>
                <div class="mb-3">
                    <label for="sku" class="form-label">SKU</label>
                    <input
                    type="text"
                    class="form-control"
                    id="sku" name="s_sku"
                    placeholder="SKU"
                    />
                </div>
                </div>
                <div class="col-6">
                <div class="mb-3">
                    <input
                    type="file"
                    class="form-control"
                    id="productImage" name="s_image"
                    onchange="previewImage();"
                    />
                    <img
                    id="imagePreview"
                    src="#"
                    alt="Image Preview"
                    style="width: 100%; margin-top: 10px; display: none"
                    />
                </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <a class="info3" data-toggle="modal" data-target="#addCategoryModal">Want to Add New Category?</a>
                <select class="form-select" name="s_category" id="categoryDropdown"></select>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label"
                >Description</label
                >
                <textarea
                class="form-control"
                id="description"
                rows="3" name="s_description"
                placeholder="Description on sales forms"
                ></textarea>
            </div>
            <div class="row">
                <div class="col-6">
                <div class="mb-3">
                    <label for="salesPrice" class="form-label"
                    >Sales price/rate</label
                    >
                    <input
                    type="number" min="0" name="s_sale_price"
                    class="form-control"
                    id="salesPrice"
                    placeholder="Sales Price/Rate"
                    />
                </div>
                </div>
                <div class="col-6">
                <div class="mb-3">
                    <label for="incomeAccount" class="form-label"
                    >Income account</label
                    >
                    <a class="info3" data-toggle="modal" data-target="#addIncomeModal">Add New?</a>
                    <select class="form-select" name="income_account" id="incomeDropdown"></select>
                </div>
                </div>
            </div>
            
            <div class="button-container">
            <button class="save-button" type="submit">Save and close</button>
        </div>
            </form>
        </div>
          `;
          break;
        case 'Bundle':
          content = `
                   <div class="sidebar-header">
              <div class="service-info2">
              <div class="product-info"><h4>Product/Service Information</h4>
                  <i class="fas fa-times close-icon" id="closeSidebar"></i></div>
              <div class="inven-imag">
                  <img src="New/images/shirt.png" alt="">
                  <p>Bundle</p>
              </div>
              </div>
              
          </div>
          <div class="sidebar-content sizing-sidebar">
              <form style="padding: 0px 11px" method="GET">
              <input type="hidden" name="b_type" value="Bundle">
              <div class="row image-row mt-3">
                  <div class="col-6">
                  <div class="mb-3">
                      <label for="name" class="form-label">Name*</label>
                      <input
                      type="text"
                      class="form-control"
                      id="name" name="b_name"
                      placeholder="Enter name"
                      />
                  </div>
                  <div class="mb-3">
                      <label for="sku" class="form-label">SKU</label>
                      <input
                      type="text"
                      class="form-control"
                      id="sku" name="b_sku"
                      placeholder="SKU"
                      />
                  </div>
                  </div>
                  <div class="col-6">
                  <div class="mb-3">
                      <input
                      type="file"
                      class="form-control"
                      id="productImage" name="b_image"
                      onchange="previewImage();"
                      />
                      <img
                      id="imagePreview"
                      src="#"
                      alt="Image Preview"
                      style="width: 100%; margin-top: 10px; display: none"
                      />
                  </div>
                  </div>
              </div>
             
              <div class="mb-3">
                  <label for="description" class="form-label"
                  >Description</label
                  >
                  <textarea
                  class="form-control"
                  id="description"
                  rows="3" name="b_description"
                  placeholder="Description on sales forms"
                  ></textarea>
              </div>
             
              <div class="mb-3">
                  <label for="purchaseDescription" class="form-label">Products/services included in the bundle</label>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <button type="button" onclick="newAddon();" class="save-button"> Add New Row </button>
                  </div>
                </div>
              </div>
              <div id="addon-container"></div>
              <div class="button-container">
              <button class="save-button" type="submit">Save and close</button>
          </div>
              </form>
          </div>
          `;
          break;
        default:
          content = `<div>Please select a valid option from the sidebar.</div>`;
      }
    
      sidebar.innerHTML = content;
      fetchCategoriesAndPopulateDropdown("categoryDropdown");
      fetchAssetsAndPopulateDropdown("assetDropdown");
      fetchIncomeAndPopulateDropdown("incomeDropdown");
      fetchExpenseAndPopulateDropdown("expenseDropdown");
      attachCloseButtonListener();
  }

  var addonId = 0;
  function newAddon()
  {
    addonId++;
    var newAddon = '<div style="margin-top: 10px" id="row-' + addonId + '" class="row">' +
    '<div class="col-md-5">' +
    '<label>Product/Service</label>' +
    '<select class="form-select" name="product[]" id="productDropdown-' + addonId + '"></select>' +
    '</div>' +
    '<div class="col-md-5">' +
    '<label>Quantity</label>' +
    '<input type="number" required name="quantity[]" min="0" max="5000" class="form-control" placeholder="Quantity">' +
    '</div>' +
    '<div class="col-md-1">' +
    '<br>' +
    '<div class="input-group-btn">' +
    '<button class="btn" onclick="removeAddon(' + addonId + ');"><i class="fas fa-trash-alt"></i></button>' +
    '</div>' +
    '</div>' +
    '</div>';

    $("#addon-container").append(newAddon);

    fetchProductAndPopulateDropdown("productDropdown-" + addonId);
  }

  function removeAddon(rowId) {
    $("#row-" + rowId).remove();
  }

  function saveCategoryModal()
  {
    var categoryName  = $('#categoryitemName').val();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    fetch("/save-product-category", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            name: categoryName,
        }),
    })

        .then((response) => response.json())
        .then((data) => {
            if (data.success) {

                // Optionally close the modal
                $("#addCategoryModal").modal("hide");

                // Optionally refresh the category list or dropdown
                fetchCategoriesAndPopulateDropdown("categoryDropdown");

                // Reset the form
                document.querySelector("#categoryitemName").value = "";
            }
        })
  }

  function saveAssetModal()
  {
    var assetName  = $('#assetitemName').val();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    fetch("/save-product-asset", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            name: assetName,
        }),
    })

        .then((response) => response.json())
        .then((data) => {
            if (data.success) {

                // Optionally close the modal
                $("#addAssetModal").modal("hide");

                // Optionally refresh the category list or dropdown
                fetchAssetsAndPopulateDropdown("assetDropdown");

                // Reset the form
                document.querySelector("#assetitemName").value = "";
            }
        })
  }

  //income account
  function saveIncomeModal()
  {
    var dataName  = $('#incomeitemName').val();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    fetch("/save-income-account", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            name: dataName,
        }),
    })

        .then((response) => response.json())
        .then((data) => {
            if (data.success) {

                // Optionally close the modal
                $("#addIncomeModal").modal("hide");

                // Optionally refresh the category list or dropdown
                fetchIncomeAndPopulateDropdown("incomeDropdown");

                // Reset the form
                document.querySelector("#incomeitemName").value = "";
            }
        })
  }

  function fetchIncomeAndPopulateDropdown(dropdownId) {
    fetch('/get-income-account') // Replace with your API endpoint
        .then(response => response.json())
        .then(data => {
            const dropdown = document.getElementById(dropdownId);
            dropdown.innerHTML = '<option selected value="">Choose an Income Account</option>';
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name; // Assuming `id` is the category identifier
                option.textContent = category.name; // Assuming `name` is the category name
                dropdown.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching categories:', error));
  }

  //income account
  function saveExpenseModal()
  {
    var dataName  = $('#expenseitemName').val();
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    fetch("/save-expense-account", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
            name: dataName,
        }),
    })

        .then((response) => response.json())
        .then((data) => {
            if (data.success) {

                // Optionally close the modal
                $("#addExpenseModal").modal("hide");

                // Optionally refresh the category list or dropdown
                fetchExpenseAndPopulateDropdown("expenseDropdown");

                // Reset the form
                document.querySelector("#expenseitemName").value = "";
            }
        })
  }

  function fetchExpenseAndPopulateDropdown(dropdownId) {
    fetch('/get-expense-account') // Replace with your API endpoint
        .then(response => response.json())
        .then(data => {
            const dropdown = document.getElementById(dropdownId);
            dropdown.innerHTML = '<option selected value="">Choose an Expense Account</option>';
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name; // Assuming `id` is the category identifier
                option.textContent = category.name; // Assuming `name` is the category name
                dropdown.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching categories:', error));
  }

  function fetchCategoriesAndPopulateDropdown(dropdownId) {
    fetch('/get-category') // Replace with your API endpoint
        .then(response => response.json())
        .then(data => {
            const dropdown = document.getElementById(dropdownId);
            dropdown.innerHTML = '<option selected value="">Choose a category</option>';
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name; // Assuming `id` is the category identifier
                option.textContent = category.name; // Assuming `name` is the category name
                dropdown.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching categories:', error));
  }

  function fetchAssetsAndPopulateDropdown(dropdownId) {
    fetch('/get-asset') // Replace with your API endpoint
        .then(response => response.json())
        .then(data => {
            const dropdown = document.getElementById(dropdownId);
            dropdown.innerHTML = '<option selected value="">Choose a Asset</option>';
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name; // Assuming `id` is the category identifier
                option.textContent = category.name; // Assuming `name` is the category name
                dropdown.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching categories:', error));
  }

  function fetchProductAndPopulateDropdown(dropdownId) {
    fetch('/get-product-service')
      .then(response => response.json())
      .then(data => {
        const dropdown = document.getElementById(dropdownId);
        dropdown.innerHTML = '<option selected value="">Choose Product</option>';
        data.forEach(category => {
          const option = document.createElement('option');
          option.value = category.id;
          option.textContent = category.name;
          dropdown.appendChild(option);
        });

        // Initialize Select2
        $('#' + dropdownId).select2({
          placeholder: "Choose Product",
          allowClear: true,
          width: '100%'
        });
      })
      .catch(error => console.error('Error fetching categories:', error));
  }
  
  function openSidebar() {
    setDefaultSidebarContent(); // Call this if sidebar content needs to be reset each time
    document.getElementById("sidebar").style.width = "491px";
    document.querySelector(".overlay").style.display = "block";
  }
  
  
  function openProductSidebar(name, sku, type, description, salesPrice, cost, qtyOnHand, reorderPoint) {
    const sidebar = document.getElementById("sidebar");
    name = name || '';
    sku = sku || '';
    console.log(sku,"checking sku")
    type = type || '';
    description = description || '';
    salesPrice = salesPrice || '';
    cost = cost || '';
    qtyOnHand = qtyOnHand || '';
    reorderPoint = reorderPoint || '';
    sidebar.innerHTML = `
       <div class="sidebar-header">
          <div class="service-info2">
            <div class="product-info"><h4>Product/Service Information</h4>
              <i class="fas fa-times close-icon" id="closeSidebar"></i></div>
            <div class="inven-imag">
              <img src="./images/shirt.png" alt="">
              <p>${type}</p>
            </div>
          </div>
          
        </div>
        <div class="sidebar-content sizing-sidebar">
          <form style="padding: 0px 11px">
            <div class="row image-row mt-3">
              <div class="col-6">
                <div class="mb-3">
                  <label for="name" class="form-label">Name*</label>
                  <input
                    type="text"
                    class="form-control"
                    id="name"
                    value="${name}"
                  />
                </div>
                <div class="mb-3">
                  <label for="sku" class="form-label">SKU</label>
                  <input
                  value="${sku}"
                    type="text"
                    class="form-control"
                    id="sku"
                    
                  />
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <!-- <label for="productImage" class="form-label">Product Image</label> -->
                  <input
                    type="file"
                    class="form-control"
                    id="productImage"
                    onchange="previewImage();"
                  />
                  <img
                    id="imagePreview"
                    src="#"
                    alt="Image Preview"
                    style="width: 100%; margin-top: 10px; display: none"
                  />
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="category" class="form-label">Category</label>
              <select class="form-select" id="category">
                <option selected>Choose a category</option>
                <option value="1">Category 1</option>
                <option value="2">Category 2</option>
              </select>
            </div>
            
            <div class="mb-3">
              <label for="description" class="form-label"
                >Description</label
              >
              <textarea
                class="form-control"
                id="description"
                rows="3"
                value="${description}"
                placeholder="Description on sales forms"
              ></textarea>
            </div>
            <div class="row">
              <div class="col-6">
                <div class="mb-3">
                  <label for="salesPrice" class="form-label"
                    >Sales price/rate</label
                  >
                  <input
                    type="text"
                    class="form-control"
                    id="salesPrice"
                    value="${salesPrice}"
                    
                  />
                </div>
              </div>
              <div class="col-6">
                <div class="mb-3">
                  <label for="incomeAccount" class="form-label"
                    >Income account</label
                  >
                  <select class="form-select" id="incomeAccount" value="${type}">
                    <option selected>Sales of Product Income</option>
                    <option value="1">Income 1</option>
                    <option value="2">Income 2</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label for="purchaseDescription" class="form-label"
                >Purchasing information</label
              >
              <textarea
                class="form-control"
                id="purchaseDescription"
                rows="3"
                placeholder="Description on purchase forms"
              ></textarea>
            </div>
           
            
          <div class="button-container">
            <button class="save-button">Save and close</button>
        </div>
          </form>
        </div>
    `;
    
    sidebar.style.width = "491px";
    document.querySelector(".overlay").style.display = "block";
    attachCloseButtonListener()
  }
  function attachCloseButtonListener() {
    const closeButton = document.getElementById("closeSidebar");
    if (closeButton) {
      closeButton.onclick = function() {
        closeSidebar();
      };
    }
  }
  function attachProductCheckboxEvents() {
    const checkboxes = document.querySelectorAll('.table-section table tbody tr input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('click', function() {
        if (!checkbox.checked) return; // Only open the sidebar if the checkbox is being checked
  
        const row = checkbox.closest('tr');
        const name = row.cells[1].textContent.trim();
        const sku = row.cells[2].textContent.trim();
        const type = row.cells[3].textContent.trim();
        const description = row.cells[4].textContent.trim();
        const salesPrice = row.cells[5].textContent.trim();
        const cost = row.cells[6].textContent.trim();
        const qtyOnHand = row.cells[7].textContent.trim();
        const reorderPoint = row.cells[8].textContent.trim();
        openProductSidebar(name, sku, type, description, salesPrice, cost, qtyOnHand, reorderPoint);
      });
    });
  }
  function closeSidebar() {
    document.getElementById("sidebar").style.width = "0";
    document.querySelector(".overlay").style.display = "none";
  }
  window.onload = function () {
    setDefaultSidebarContent();
    attachEvents(); // Attach all events when the window loads
    attachProductCheckboxEvents();
  };

  document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(".quantity-button");

    editButtons.forEach((button) => {
      button.addEventListener("click", function () {
        // Get data attributes from the clicked button
        const name = this.getAttribute("data-name");
        const sku = this.getAttribute("data-sku");
        const totalQty = this.getAttribute("data-total_qty");
        const productId = this.getAttribute("data-product_id");

        // Populate modal fields
        document.getElementById("itemName").value = name;
        document.getElementById("itemSku").value = sku;
        document.getElementById("addQuantity").value = totalQty; // Add the new quantity
        document.getElementById("product_id").value = productId; // Product ID
      });
    });
  });

  document.addEventListener("DOMContentLoaded", function () {
    const editInventory = document.querySelectorAll(".edit-inventory");

    editInventory.forEach((button) => {
      button.addEventListener("click", function () {
        // Get data attributes from the clicked button
        const name = this.getAttribute("data-name");
        const sku = this.getAttribute("data-sku");
        const reorder = this.getAttribute("data-reorder");
        const category = this.getAttribute("data-category");
        const productId = this.getAttribute("data-product_id");

        // Populate modal fields
        document.getElementById("product_name").value = name;
        document.getElementById("product_sku").value = sku;
        document.getElementById("product_reorder").value = reorder;
        document.getElementById("product_value").value = productId;

        // Fetch categories and set the previously assigned category
        fetchCategoriesAndPopulateDropdownNew("product_category", category);
      });
    });
  });

  function fetchCategoriesAndPopulateDropdownNew(dropdownId, selectedCategory = "") {
    fetch('/get-category') // Replace with your API endpoint
      .then(response => response.json())
      .then(data => {
        const dropdown = document.getElementById(dropdownId);
        dropdown.innerHTML = '<option selected value="" disabled>Choose a category</option>';
        data.forEach(category => {
          const option = document.createElement('option');
          option.value = category.name; // Assuming `name` is the unique identifier
          option.textContent = category.name;
          if (category.name === selectedCategory) {
            option.selected = true; // Set the assigned category as selected
          }
          dropdown.appendChild(option);
        });
      })
    .catch(error => console.error('Error fetching categories:', error));
  }
