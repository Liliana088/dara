// Sidebar toggle
function toggleSidebar() {
  document.getElementById("sidebarMenu").classList.toggle("expand");
}

// Filter and paginate table rows based on search and show entries
function updateTable() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase();
  const showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;
  const urlParams = new URLSearchParams(window.location.search);
  const currentPage = parseInt(urlParams.get("page")) || 1;

  const tableBody = document.querySelector("#salesTableBody");
  const rows = Array.from(tableBody.querySelectorAll("tr"));

  // Filter rows by search term
  const filteredRows = rows.filter(row => {
    return Array.from(row.cells).some(cell =>
      cell.textContent.toLowerCase().includes(searchTerm)
    );
  });

  // Pagination indices
  const startIndex = (currentPage - 1) * showCount;
  const endIndex = startIndex + showCount;

  // Show only rows in current page and matching filter
  rows.forEach(row => {
    row.style.display = "none";
  });
  filteredRows.slice(startIndex, endIndex).forEach(row => {
    row.style.display = "";
  });
}

// Update price for a single product row when product or quantity changes
function updatePrice(selectElement) {
  const selectedOption = selectElement.selectedOptions[0];
  const cost = parseFloat(selectedOption?.dataset.cost) || 0;
  const markup = parseFloat(selectedOption?.dataset.markup) || 0;
  const markupAmount = (markup / 100) * cost;
  const roundedPrice = Math.round(cost + markupAmount);

  const row = selectElement.closest(".product-row");
  const quantity = parseInt(row.querySelector(".quantity").value) || 1;
  const priceField = row.querySelector(".price-field");

  if (priceField) {
    priceField.value = (roundedPrice * quantity).toFixed(2);
  }

  updateTotals();
}

// Calculate and update subtotal, markup total, total cost, and change
function updateTotals() {
  let subtotal = 0;
  let totalMarkup = 0;

  document.querySelectorAll(".product-row").forEach(row => {
    const select = row.querySelector(".product-select");
    const quantity = parseInt(row.querySelector(".quantity").value) || 1;

    const cost = parseFloat(select?.selectedOptions[0]?.dataset.cost) || 0;
    const markupPercent = parseFloat(select?.selectedOptions[0]?.dataset.markup) || 0;
    const markupAmount = (markupPercent / 100) * cost;
    const roundedPrice = Math.round(cost + markupAmount);

    const priceField = row.querySelector(".price-field");
    if (priceField) {
      priceField.value = (roundedPrice * quantity).toFixed(2);
    }

    subtotal += Math.round(cost) * quantity;
    totalMarkup += Math.round(markupAmount) * quantity;
  });

  const total = subtotal + totalMarkup;

  document.getElementById("subtotalDisplay").value = subtotal.toFixed(2);
  document.getElementById("totalCost").value = total.toFixed(2);
  document.getElementById("subtotalInput").value = subtotal.toFixed(2);
  document.getElementById("markupInput").value = totalMarkup.toFixed(2);

  updateChange();
}

// Update change due based on cash received and total cost
function updateChange() {
  const total = parseFloat(document.getElementById("totalCost").value) || 0;
  const received = parseFloat(document.getElementById("cashReceived").value) || 0;
  const change = received - total;
  document.getElementById("changeDue").value = change >= 0 ? change.toFixed(2) : "0.00";
}

// Create a new product row element with given product options HTML
function createProductRow(productOptionsHTML) {
  const newRow = document.createElement("div");
  newRow.className = "mb-2 d-flex align-items-center product-row";
  newRow.innerHTML = `
    <div class="input-group me-2" style="width: 1300px;">
      <button type="button" class="btn btn-outline-danger delete-row-btn">
        <i class="bi bi-trash-fill"></i>
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

// Initialize all event listeners and setups on DOM load
document.addEventListener("DOMContentLoaded", () => {
  // Fetch products data from hidden element and build options HTML
  const rawData = document.getElementById("product-data")?.textContent || "[]";
  const products = JSON.parse(rawData);
  const productOptionsHTML = products.map(p => `
    <option value="${p.id}" data-cost="${p.cost}" data-markup="${p.markup}">${p.Description}</option>
  `).join("");

  // Add product row button
  document.getElementById("addProductBtn")?.addEventListener("click", () => {
    const container = document.getElementById("productContainer");
    const newRow = createProductRow(productOptionsHTML);
    container.appendChild(newRow);
    updatePrice(newRow.querySelector(".product-select"));
  });

  // Update price on page load for existing selects
  document.querySelectorAll(".product-select").forEach(updatePrice);

  // Listen for quantity or product select changes in product container
  document.getElementById("productContainer")?.addEventListener("input", e => {
    if (e.target.classList.contains("quantity") || e.target.classList.contains("product-select")) {
      updateTotals();
    }
  });

  // Handle deleting product rows
  document.getElementById("productContainer")?.addEventListener("click", e => {
    if (e.target.closest(".delete-row-btn")) {
      e.target.closest(".product-row")?.remove();
      updateTotals();
    }
  });

  // Update change when cash received input changes
  document.getElementById("cashReceived")?.addEventListener("input", () => {
    updateChange();
    validateCashReceived();
  });

  // Update table filtering when search input or show entries change
  document.getElementById("searchInput")?.addEventListener("input", updateTable);
  document.getElementById("showEntries")?.addEventListener("input", updateTable);

  // Initialize table filtering on load
  updateTable();

  // Validate cash input on load
  validateCashReceived();
  updateTotals();
});

// Validate cash received field and toggle warning & save button state
function validateCashReceived() {
  const cashInput = document.getElementById("cashReceived");
  const totalCost = parseFloat(document.getElementById("totalCost")?.value) || 0;
  const changeField = document.getElementById("changeDue");
  const warningText = document.getElementById("cashWarning");
  const saveButton = document.getElementById("saveSaleButton");

  const cash = parseFloat(cashInput?.value) || 0;

  if (cash < totalCost) {
    warningText.style.display = "block";
    saveButton.disabled = true;
    changeField.value = "0.00";
    cashInput.classList.add("is-invalid");
    cashInput.classList.remove("is-valid");
  } else {
    warningText.style.display = "none";
    saveButton.disabled = false;
    changeField.value = (cash - totalCost).toFixed(2);
    cashInput.classList.remove("is-invalid");
    cashInput.classList.add("is-valid");
  }
}
