<script>
    // Filter and limit table rows
    function updateTable() {
    const searchTerm = document.getElementById("searchInput").value.toLowerCase();
    let showCount = parseInt(document.getElementById("showEntries").value, 10) || 1;

    if (showCount < 1) showCount = 1;

    const tableBody = document.getElementById("productTableBody"); // Correct ID
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

    // Add event listeners for input elements
    document.getElementById("searchInput").addEventListener("input", updateTable);
    document.getElementById("showEntries").addEventListener("input", updateTable);

    // Initial load
    window.addEventListener("DOMContentLoaded", updateTable);

    // Toggle Sidebar
    function toggleSidebar() {
    const sidebar = document.getElementById("sidebarMenu");
    sidebar.classList.toggle("expand");
    }


    // Fetch categories from the database and populate the dropdown in the "Add Product" modal
    $(document).ready(function() {

        // Using AJAX for category population
        $.ajax({
            url: 'getCategories.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);  // Log the response to check the data
                if (response && response.categories && Array.isArray(response.categories)) {
                    const categoryDropdown = $('#categorySelect'); // Get the category dropdown in the modal
                    categoryDropdown.empty(); // Clear existing options
                    categoryDropdown.append('<option value="">Select Category</option>'); // Default option

                    // Populate dropdown with categories
                    response.categories.forEach(function(category) {
                        categoryDropdown.append(`<option value="${category.id}">${category.category}</option>`);
                    });
                } else {
                    console.error('Invalid response format from getCategories.php');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error fetching categories: ' + error);
            }
        });

        // Handle product saving
        document.getElementById("saveProductBtn").addEventListener("click", function () {
            const category = document.getElementById("categorySelect").value; // Get selected category ID from the dropdown
            const code = document.getElementById("codeInput").value.trim();
            const description = document.getElementById("descriptionInput").value.trim();
            const stock = document.getElementById("stockInput").value.trim();
            const buyingPrice = document.getElementById("buyingPriceInput").value.trim();
            const markup = document.getElementById("markupInput").value.trim();

            // Validate the inputs
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
                    location.reload(); // Refresh the page to show the new product
                }
            };

            xhr.send(params.toString());
        });
    });
    

    // Show the modal
    const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
    editModal.show();

    // Handle edit submit
    $('#editProductForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: 'edit_product.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
        alert(response); // Alert the response from the server
        }
    });
    });
</script>
