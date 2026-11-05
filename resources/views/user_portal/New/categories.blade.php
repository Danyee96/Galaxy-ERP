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
    <title>Product Categories</title>
  </head>
  <body>
    <div class="container mt-4">
      <h2>Product Categories</h2>
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
            class="nav-link active"
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
        <h2>Products Categories</h2>

        <!-- Bootstrap JS, Popper.js, and jQuery -->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
      </div>

      <!-- Search Section -->
      <div class="search-section">
        <input type="text" style="width: 300px" id="searchInput" class="form-control" placeholder="Find Category" onkeyup="filterTable()" />
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
              <div style="justify-content: space-between;" class="d-flex">
                <button type="button" class="btn btn-secondary mt-2" onclick="toggleFilterDropdown()">Reset</button>
                <button type="submit" class="btn  mt-2 new-button">Apply</button>
              </div>

            </form>
          </div>
        </div>
        <!-- <div style="margin-left: 100px;">
          <button id="btnExport" onclick="fnExcelReport();" class="btn btn-primary">Export to Excel</button>
          <button onclick="exportTableToPDF()" class="btn btn-danger">Export to PDF</button>
        </div> -->
      </div>

      <!-- Table Section -->
      <div class="table-section">
        <table class="table table-hover" id="example">
          <thead>
            <tr>
              <th scope="col">#</th>
              <th scope="col">NAME</th>
              <th scope="col">ACTION</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categories as $key => $category)
            <tr>
              <td>
                {{$key + 1}}
              </td>
              <td>
                {{$category->name}}
              </td>
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
                    <!-- Mark as Inactive Button -->
                    @if($category->status == 1)
                    <a class="dropdown-item"
                      href="{{route('deleteCategory', $category->id)}}"
                    >
                      Mark as Inactive
                    </a>
                    @else
                    <a class="dropdown-item"
                      href="{{route('activeCategory', $category->id)}}"
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

    <script>
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
  </body>
</html>
