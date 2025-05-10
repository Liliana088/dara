// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById("sidebarMenu");
    sidebar.classList.toggle("expand");
  }
  
  // Filter and limit table rows
  function updateTable() {
    const searchTerm = document.getElementById("searchInput").value.toLowerCase();
    let showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;
    if (showCount < 1) showCount = 1;
  
    const tableBody = document.querySelector("table tbody");
    const rows = tableBody.querySelectorAll("tr");
  
    let visibleCount = 0;
  
    rows.forEach(row => {
      const cells = row.querySelectorAll("td");
      const rowMatches = Array.from(cells).some(cell =>
        cell.textContent.toLowerCase().includes(searchTerm)
      );
  
      if (rowMatches && visibleCount < showCount) {
        row.style.display = "";
        visibleCount++;
      } else {
        row.style.display = "none";
      }
    });
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
      const price = cost + markupAmount;
  
      const priceField = row.querySelector(".price-field");
      if (priceField) priceField.value = price.toFixed(2);
  
      subtotal += cost * quantity;
      totalMarkup += markupAmount * quantity;
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
    newRow.classList.add("mb-3", "d-flex", "align-items-center", "gap-2", "product-row");
    newRow.innerHTML = `
      <select class="form-select product-select" name="product_id[]" onchange="updateTotals()">
        ${productOptionsHTML}
      </select>
      <input type="number" class="form-control quantity" name="quantity[]" value="1" min="1" style="width: 70px;">
      <span>₱</span>
      <input type="text" name="product_price[]" class="form-control price-field" readonly style="width: 100px;">
    `;
    return newRow;
  }
  
  document.addEventListener("DOMContentLoaded", () => {
    // Fetch PHP product data
    const rawData = document.getElementById("product-data").textContent;
    const products = JSON.parse(rawData);
  
    // Convert to HTML
    const productOptionsHTML = products.map(product => `
      <option value="${product.id}" data-cost="${product.cost}" data-markup="${product.markup}">
        ${product.Description}
      </option>
    `).join("");
  
    // Add row
    document.getElementById("addProductBtn").addEventListener("click", () => {
      const container = document.getElementById("productContainer");
      const newRow = createProductRow(productOptionsHTML);
      container.appendChild(newRow);
      updateTotals();
    });
  
    // Input listeners
    document.getElementById("productContainer").addEventListener("input", function (e) {
      if (e.target.classList.contains("quantity") || e.target.classList.contains("product-select")) {
        updateTotals();
      }
    });
  
    document.getElementById("cashReceived").addEventListener("input", updateChange);
    document.getElementById("searchInput").addEventListener("input", updateTable);
    document.getElementById("showEntries").addEventListener("input", updateTable);
  
    updateTable();
  });
  