// Filter and limit table rows
function updateTable() {
    const searchTerm = document.getElementById("searchInput").value.toLowerCase();
    let showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;
    if (showCount < 1) showCount = 1;
  
    const tableBody = document.getElementById("productTableBody");
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
  
  // Event listeners
  window.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    const showEntries = document.getElementById("showEntries");
    showEntries.value = showEntries.dataset.items;
  
    searchInput.addEventListener("input", updateTable);
    showEntries.addEventListener("input", updateTable);
    updateTable();
  
    showEntries.addEventListener("change", function () {
      const entries = this.value;
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.set("entries", entries);
      urlParams.set("page", 1);
      window.location.search = urlParams.toString();
    });
  });
  
  // Toggle Sidebar
  function toggleSidebar() {
    const sidebar = document.getElementById("sidebarMenu");
    sidebar.classList.toggle("expand");
  }
  
  // Populate category dropdowns using AJAX
  $(document).ready(function () {
    let cachedCategories = [];
  
    $.ajax({
      url: 'getCategories.php',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response && Array.isArray(response.categories)) {
          cachedCategories = response.categories;
  
          const addSelect = $('#categorySelect');
          const editSelect = $('#editCategorySelect');
  
          addSelect.empty().append('<option value="">Select Category</option>');
          editSelect.empty().append('<option value="">Select Category</option>');
  
          response.categories.forEach(function (category) {
            const option = `<option value="${category.id}">${category.category}</option>`;
            addSelect.append(option);
            editSelect.append(option);
          });
        }
      },
      error: function (xhr, status, error) {
        console.log('Error fetching categories: ' + error);
      }
    });
  
    // Save new product
    document.getElementById("saveProductBtn").addEventListener("click", function () {
      const category = document.getElementById("categorySelect").value;
      const code = document.getElementById("codeInput").value.trim();
      const description = document.getElementById("descriptionInput").value.trim();
      const stock = document.getElementById("stockInput").value.trim();
      const buyingPrice = document.getElementById("buyingPriceInput").value.trim();
      const markup = document.getElementById("markupInput").value.trim();
  
      if (!category || !code || !description || !stock || !buyingPrice || !markup) {
        alert("Please fill in all fields.");
        return;
      }
  
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "insert_product.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  
      const params = new URLSearchParams({
        category: category,
        code: code,
        description: description,
        stock: stock,
        buying_price: buyingPrice,
        markup: markup
      });
  
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          alert(xhr.responseText);
          location.reload();
        }
      };
  
      xhr.send(params.toString());
    });
  
    // Edit product submit
    $('#editProductForm').on('submit', function (e) {
      e.preventDefault();
      $.ajax({
        url: 'edit_product.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function (response) {
          alert(response);
        }
      });
    });
  });
  
  // Populate Edit Modal
  function populateEditModal(id, code, description, stock, cost, markup, categoryId) {
    document.getElementById('editId').value = id;
    document.getElementById('editCode').value = code;
    document.getElementById('editDescription').value = description;
    document.getElementById('editStock').value = stock;
    document.getElementById('editBuyingPrice').value = cost;
    document.getElementById('editMarkup').value = markup;
  
    const sellingPrice = parseFloat(cost) + (parseFloat(cost) * parseFloat(markup) / 100);
    document.getElementById('editSellingPrice').value = sellingPrice.toFixed(2);
  
    $.ajax({
      url: 'getCategories.php',
      type: 'GET',
      dataType: 'json',
      success: function (response) {
        if (response && Array.isArray(response.categories)) {
          const categoryDropdown = $('#editCategorySelect');
          categoryDropdown.empty().append('<option value="">Select Category</option>');
  
          response.categories.forEach(function (cat) {
            const selected = cat.id == categoryId ? 'selected' : '';
            categoryDropdown.append(`<option value="${cat.id}" ${selected}>${cat.category}</option>`);
          });
        }
      },
      error: function (xhr, status, error) {
        console.log('Error fetching categories: ' + error);
      }
    });
  
    const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
    editModal.show();
  }
  
  // Selling price calculation
  function calculateSellingPrice() {
    const buyingPrice = parseFloat(document.getElementById('buyingPriceInput').value) || 0;
    const markup = parseFloat(document.getElementById('markupInput').value) || 0;
    const sellingPrice = buyingPrice + (buyingPrice * markup / 100);
    document.getElementById('sellingPriceInput').value = sellingPrice.toFixed(2);
  }
  
  document.getElementById('buyingPriceInput').addEventListener('input', calculateSellingPrice);
  document.getElementById('markupInput').addEventListener('input', calculateSellingPrice);
  
  function calculateEditSellingPrice() {
    const buyingPrice = parseFloat(document.getElementById('editBuyingPrice').value) || 0;
    const markup = parseFloat(document.getElementById('editMarkup').value) || 0;
    const sellingPrice = buyingPrice + (buyingPrice * markup / 100);
    document.getElementById('editSellingPrice').value = sellingPrice.toFixed(2);
  }
  
  document.getElementById('editBuyingPrice').addEventListener('input', calculateEditSellingPrice);
  document.getElementById('editMarkup').addEventListener('input', calculateEditSellingPrice);
  
  // Save edit button
  document.getElementById("saveEditBtn").addEventListener("click", function () {
    const id = document.getElementById("editId").value.trim();
    const category = document.getElementById("editCategorySelect").value;
    const code = document.getElementById("editCode").value.trim();
    const description = document.getElementById("editDescription").value.trim();
    const stock = document.getElementById("editStock").value.trim();
    const buyingPrice = document.getElementById("editBuyingPrice").value.trim();
    const markup = document.getElementById("editMarkup").value.trim();
  
    if (!category || !code || !description || !stock || !buyingPrice || !markup) {
      alert("Please fill in all fields.");
      return;
    }
  
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "edit_product.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  
    const params = new URLSearchParams({
      id: id,
      category: category,
      code: code,
      description: description,
      stock: stock,
      buying_price: buyingPrice,
      markup: markup
    });
  
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        const response = xhr.responseText;
        console.log(response);
        if (response === "success") {
          location.reload();
        } else {
          alert(response);
        }
      }
    };
  
    xhr.send(params.toString());
  });