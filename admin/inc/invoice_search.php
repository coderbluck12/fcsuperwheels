<div class="form-group mb-4" style="position:relative; max-width:300px;">
  <input
    id="searchInput"
    type="text"
    class="form-control"
    placeholder="Search by Name or Receipt No..."
    autocomplete="off"
  />
  <div id="briefInfo" class="mt-1 bg-white border rounded" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1000; box-shadow:0 4px 6px rgba(0,0,0,0.1); max-height:300px; overflow-y:auto;">
  </div>
</div>

<style>
    .search-result-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .search-result-item:last-child {
        border-bottom: none;
    }
    .search-result-item:hover {
        background-color: #f3f4f6;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const briefInfo = document.getElementById('briefInfo');

    searchInput.addEventListener('input', function() {
        let query = this.value.trim();

        // Only search if typing 2 or more characters
        if (query.length < 2) {
            briefInfo.style.display = 'none';
            return;
        }

        // Fetch data from the new PHP backend file
        fetch('inc/ajax_search_receipt.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                briefInfo.innerHTML = ''; // Clear previous results
                
                if (data.length > 0) {
                    data.forEach(receipt => {
                        let div = document.createElement('div');
                        div.className = 'search-result-item';
                        
                        // Display Receipt Number, Customer Name, and Vehicle
                        div.innerHTML = `
                            <div style="font-weight:bold; color:#2563eb;">#${receipt.prefix_receipt_number}</div>
                            <div style="font-size:0.9rem; color:#111827;">${receipt.customer_name}</div>
                            <div style="font-size:0.8rem; color:#6b7280;">${receipt.vehicle}</div>
                        `;
                        
                        // When clicked, redirect to view_receipt.php with the ENCRYPTED ID
                        div.onclick = function() {
                            window.location.href = 'view_receipt.php?prefix_receipt_number=' + encodeURIComponent(receipt.encrypted_id);
                        };
                        
                        briefInfo.appendChild(div);
                    });
                    briefInfo.style.display = 'block';
                } else {
                    briefInfo.innerHTML = '<div class="p-3 text-muted text-center" style="font-size:0.9rem;">No receipts found.</div>';
                    briefInfo.style.display = 'block';
                }
            })
            .catch(error => console.error('Error fetching search results:', error));
    });

    // Hide dropdown when clicking anywhere outside the search box
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !briefInfo.contains(e.target)) {
            briefInfo.style.display = 'none';
        }
    });
});
</script>