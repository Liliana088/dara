// Sidebar toggle
function toggleSidebar() {
  const sidebar = document.getElementById("sidebarMenu");
  sidebar.classList.toggle("expand");
}

// Filter and limit table rows
function updateTable() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase();
  const showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;

  const urlParams = new URLSearchParams(window.location.search);
  const currentPage = parseInt(urlParams.get("page")) || 1;

  const tableBody = document.querySelector("#salesTableBody");
  const rows = Array.from(tableBody.querySelectorAll("tr"));

  // Filter rows that match the search term
  const filteredRows = rows.filter(row => {
    const cells = row.querySelectorAll("td");
    return Array.from(cells).some(cell =>
      cell.textContent.toLowerCase().includes(searchTerm)
    );
  });

  // Calculate start and end indexes based on page number
  const startIndex = (currentPage - 1) * showCount;
  const endIndex = startIndex + showCount;

  filteredRows.forEach((row, index) => {
    if (index >= startIndex && index < endIndex) {
      row.style.display = "";
    } else {
      row.style.display = "none";
    }
  });

  // Hide rows that don't match the filter
  rows.forEach(row => {
    if (!filteredRows.includes(row)) {
      row.style.display = "none";
    }
  });
}

    // Add event listeners for input elements
    document.getElementById("searchInput").addEventListener("input", updateTable);
    document.getElementById("showEntries").addEventListener("change", function () {
    const entries = this.value;
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set("entries", entries);
    urlParams.set("page", 1); // Reset to first page on entries change
    window.location.search = urlParams.toString();
});

    // Initial load
    window.addEventListener("DOMContentLoaded", updateTable);

function updatePrice(selectElement) {
  const selectedOption = selectElement.options[selectElement.selectedIndex];
  const cost = parseFloat(selectedOption.dataset.cost) || 0;
  const markup = parseFloat(selectedOption.dataset.markup) || 0;
  const markupAmount = (markup / 100) * cost;
  const rawPrice = cost + markupAmount;
  const roundedPrice = Math.ceil(rawPrice);

  const row = selectElement.closest(".product-row");
  const quantity = parseInt(row.querySelector(".quantity").value) || 1;

  const priceField = row.querySelector(".price-field");
  if (priceField) {
    priceField.value = (roundedPrice * quantity).toFixed(2);
  }

  updateTotals();
}

function updateTotals() {
  let subtotal = 0;
  let totalMarkup = 0;

  document.querySelectorAll(".product-row").forEach(row => {
    const select = row.querySelector(".product-select");
    const quantity = parseInt(row.querySelector(".quantity").value) || 1;

    const cost = parseFloat(select?.selectedOptions[0]?.dataset.cost) || 0;
    const markupPercent = parseFloat(select?.selectedOptions[0]?.dataset.markup) || 0;
    const markupAmount = (markupPercent / 100) * cost;
    const rawPrice = cost + markupAmount;
    const roundedPrice = Math.ceil(rawPrice);

    const priceField = row.querySelector(".price-field");
    if (priceField) priceField.value = (roundedPrice * quantity).toFixed(2);

    subtotal += Math.ceil(cost) * quantity;
    totalMarkup += Math.ceil(markupAmount) * quantity;
  });

  const total = subtotal + totalMarkup;

  document.getElementById("subtotalDisplay").value = subtotal.toFixed(2);
  document.getElementById("totalCost").value = total.toFixed(2);
  document.getElementById("subtotalInput").value = subtotal.toFixed(2);
  document.getElementById("markupInput").value = totalMarkup.toFixed(2);

  updateChange();
}

function updateChange() {
  const total = parseFloat(document.getElementById("totalCost").value) || 0;
  const received = parseFloat(document.getElementById("cashReceived").value) || 0;
  const change = received - total;
  document.getElementById("changeDue").value = change >= 0 ? change.toFixed(2) : '0.00';
}

function createProductRow(productOptionsHTML) {
  const newRow = document.createElement("div");
  newRow.classList.add("mb-2", "d-flex", "align-items-center", "product-row");
  newRow.innerHTML = `
    <div class="input-group me-2" style="width: 1300px;">
      <button type="button" class="btn btn-outline-danger delete-row-btn">
        <i class="fa-solid fa-trash"></i>
      </button>
      <select class="form-select product-select" name="product_id[]" onchange="updatePrice(this)">
        ${productOptionsHTML}
      </select>
    </div>
    <input type="number" class="form-control quantity" name="quantity[]" value="1" min="1" style="width: 60px;">
    <span>₱</span>
    <input type="text" name="product_price[]" class="form-control price-field" readonly>
  `;
  return newRow;
}

// Call updatePrice() for all selects on page load
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll('.product-select').forEach(function(select) {
    updatePrice(select);
  });
});

document.addEventListener("DOMContentLoaded", () => {
  // Fetch PHP product data
  const rawData = document.getElementById("product-data").textContent;
  const products = JSON.parse(rawData);

  // Convert to HTML for the product select options
  const productOptionsHTML = products.map(product => `
    <option value="${product.id}" data-cost="${product.cost}" data-markup="${product.markup}">
      ${product.Description}
    </option>
  `).join("");

  // Add row when "Add Product" button is clicked
  document.getElementById("addProductBtn").addEventListener("click", () => {
    const container = document.getElementById("productContainer");
    const newRow = createProductRow(productOptionsHTML);
    container.appendChild(newRow);

    // Trigger price update for the select in the new row
    const select = newRow.querySelector('.product-select');
    updatePrice(select); // sets price and updates totals
  });

  // Call updatePrice() for the first row
  const firstSelect = document.querySelector('.product-select');
  if (firstSelect) {
    updatePrice(firstSelect); // sets price for the first row
  }

  // Input listeners for quantity and product selection change
  document.getElementById("productContainer").addEventListener("input", function (e) {
    if (e.target.classList.contains("quantity") || e.target.classList.contains("product-select")) {
      updateTotals();
    }
  });

  // Update change on cash received input
  document.getElementById("cashReceived").addEventListener("input", updateChange);
  document.getElementById("searchInput").addEventListener("input", updateTable);
  document.getElementById("showEntries").addEventListener("input", updateTable);

  // Call updateTable to initialize table filtering on page load
  updateTable();

  // Handle delete-row buttons
  document.getElementById("productContainer").addEventListener("click", function (e) {
    if (e.target.closest(".delete-row-btn")) {
      const row = e.target.closest(".product-row");
      if (row) {
        row.remove();  // Remove the row from the container
        updateTotals(); // Recalculate totals after deletion
      }
    }
  });
});

//cash received validation

document.addEventListener("DOMContentLoaded", function () {
  const cashInput = document.getElementById("cashReceived");
  const changeField = document.getElementById("changeDue");
  const totalCostInput = document.getElementById("totalCostInput");
  const totalCostDisplay = document.getElementById("totalCostDisplay");
  const warningText = document.getElementById("cashWarning");
  const saveButton = document.getElementById("saveSaleButton");

  function calculateTotals() {
    let subtotal = 0;
    let markup = 0;

    document.querySelectorAll(".product-row").forEach(row => {
      const cost = parseFloat(row.querySelector(".cost").textContent || 0);
      const markupVal = parseFloat(row.querySelector(".markup").textContent || 0);
      const qty = parseFloat(row.querySelector(".quantity").value || 0);

      subtotal += cost * qty;
      markup += markupVal * qty;
    });

    const total = subtotal + markup;
    totalCostDisplay.value = total.toFixed(2);
    totalCostInput.value = total.toFixed(2);
    document.getElementById("subtotalInput").value = subtotal.toFixed(2);
    document.getElementById("markupInput").value = markup.toFixed(2);

    validateCash();
  }

  function validateCash() {
    const cash = parseFloat(cashInput.value) || 0;
    const total = parseFloat(totalCostInput.value) || 0;
    const change = cash - total;
  
    if (cash < total) {
      const alert = bootstrap.Alert.getOrCreateInstance(document.getElementById("cashWarning"));
      document.getElementById("cashWarning").style.display = "block";
      saveButton.disabled = true;
      changeField.value = "0.00";
    } else {
      document.getElementById("cashWarning").style.display = "none";
      saveButton.disabled = false;
      changeField.value = change.toFixed(2);
    }
  }
  

  // Hook into relevant events
  cashInput.addEventListener("input", validateCash);

  // Attach quantity change handler
  document.querySelectorAll(".quantity").forEach(input => {
    input.addEventListener("input", calculateTotals);
  });

  // Initial calculation on page load
  calculateTotals();
});
//checks the cash received
const cashInput = document.getElementById('cashReceived');
  const totalCostInput = document.getElementById('totalCost');

  cashInput.addEventListener('input', () => {
    const cash = parseFloat(cashInput.value);
    const totalCost = parseFloat(totalCostInput.value);

    if (!isNaN(cash) && !isNaN(totalCost)) {
      if (cash >= totalCost) {
        cashInput.classList.remove('is-invalid');
        cashInput.classList.add('is-valid');
      } else {
        cashInput.classList.remove('is-valid');
        cashInput.classList.add('is-invalid');
      }
    } else {
      cashInput.classList.remove('is-valid');
      cashInput.classList.add('is-invalid');
    }
  });