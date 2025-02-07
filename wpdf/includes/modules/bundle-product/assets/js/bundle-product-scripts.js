jQuery(document).ready(function ($) {
    console.log("Initializing hardcoded bundle product search...");
    let bundleComponents = $('#bundle_components_input').val();

   if (bundleComponents && bundleComponents !== "null" && bundleComponents !== "") {
    try {
        let components = JSON.parse(bundleComponents);

        if (typeof components === 'object' && components !== null) {
            console.log("Loaded bundle components:", components);

            Object.entries(components).forEach(([key, component]) => {
                if (component.id && component.name && component.price && component.stock) {  
                    $('#bundle_components_body').append(`
                        <tr>
                            <td>${component.id}</td>
                            <td>${component.name}</td>
                            <td>${component.stock}</td>
                            <td>${component.price}</td>
                            <td><input type="number" name="bundle_components[${component.id}][qty]" value="${component.qty || 1}" min="1"></td>
                            <td><button type="button" class="remove-item button">Remove</button></td>
                        </tr>
                    `);
                } else {
                    console.warn("Skipping invalid component:", component);
                }
            });
        } else {
            console.warn("Bundle components data format is invalid.");
        }
    } catch (error) {
        console.error("Error parsing bundle components JSON:", error);
    }
}


    $(document).on('click', '.remove-item', function () {
        $(this).closest('tr').remove();
        updateBundleComponentsInput();
    });

    function updateBundleComponentsInput() {
        let bundleData = {};
        $('#bundle_components_body tr').each(function () {
            let row = $(this);
            let id = row.find('td:nth-child(1)').text().trim() || '';
            let name = row.find('td:nth-child(2)').text().trim() || '';
            let stock = row.find('td:nth-child(3)').text().trim() || '';
            let price = row.find('td:nth-child(4)').text().trim() || '';
            let qty = row.find('input').val() ? row.find('input').val().trim() : '1';

            bundleData[id] = {
                id: id,
                name: name,
                stock: stock,
                price: price,
                qty: qty
            };
        });

        $('#bundle_components_input').val(JSON.stringify(bundleData));
    }
    
    function renderSearchResults(data) {
        let resultsContainer = $('#bundle_search_results');
        resultsContainer.empty().css({
            'position': 'absolute',
            'background': '#fff',
            'border': '1px solid #ccc',
            'z-index': '1000',
            'width': '100%',
            'box-shadow': '0 2px 5px rgba(0,0,0,0.2)',
            'max-height': '200px',
            'overflow-y': 'auto'
        }).show();

        if (data.length === 0) {
            resultsContainer.append('<div style="padding: 10px; font-style: italic;">No products found</div>');
            return;
        }

        $.each(data, function (index, item) {
            resultsContainer.append(
                `<div class='search-result-item' data-id='${item.id}' data-stock='${item.stock}' data-price='${item.price}'
                      style='padding: 10px; border-bottom: 1px solid #eee; cursor: pointer;'>
                    ${item.text} (Stock: ${item.stock}, Price: ${item.price})
                </div>`
            );
        });

        $('.search-result-item').on('click', function () {
            let selectedId = $(this).data('id');
            let selectedStock = $(this).data('stock');
            let selectedPrice = $(this).data('price');
            let selectedText = $(this).text();

            $('#bundle_component_search').val('');
            $('#bundle_components_body').append(
                `<tr>
                    <td>${selectedId}</td>
                    <td>${selectedText}</td>
                    <td>${selectedStock}</td>
                    <td>${selectedPrice}</td>
                    <td><input type='number' name='bundle_components[${selectedId}]' value='1' min='1'></td>
                    <td><button class='remove-item button'>Remove</button></td>
                </tr>`
            );

            updateBundleComponentsInput();
            resultsContainer.hide();
            console.log("Product added to bundle table:", selectedId);
        });
    }

    function initCustomBundleSearch() {
        let $searchField = $('#bundle_component_search');

        if ($searchField.length > 0) {
            console.log("Search field found, initializing custom hardcoded search");

            $searchField.off('keyup').on('keyup', function () {
                let searchTerm = $(this).val().trim();
                console.log("Typing detected:", searchTerm);

                if (searchTerm.length < 3) {
                    console.warn("Search term too short.");
                    $('#bundle_search_results').hide();
                    return;
                }

                $.ajax({
                    url: bundleProductData.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'custom_bundle_product_search',
                        term: searchTerm,
                        security: bundleProductData.nonce
                    },
                    success: function (response) {
                        console.log("Custom AJAX Response:", response);
                        renderSearchResults(response);
                    },
                    error: function (xhr, status, error) {
                        console.error("Custom AJAX Error:", xhr.responseText);
                    }
                });
            });

            console.log("Custom hardcoded search successfully initialized.");
        } else {
            console.warn("Search field not found in the DOM.");
        }
    }

    initCustomBundleSearch();
    
     function calculateBundleStock() {
        let minStock = Infinity;

        $('#bundle_components_body tr').each(function () {
            let stock = parseInt($(this).find('td:nth-child(3)').text().trim()) || 0;
            let qty = parseInt($(this).find('input').val().trim()) || 1;

            let calculatedStock = Math.floor(stock / qty);
            if (calculatedStock < minStock) {
                minStock = calculatedStock;
            }
        });

        if (minStock === Infinity) {
            minStock = 0;
        }

        // Update the stock field in WooCommerce
        $('._stock_field input').val(minStock).trigger('change');

        // Enable Manage Stock
        $('._manage_stock_field input').prop('checked', true).trigger('change');

        console.log("Bundle stock updated to:", minStock);
    }

    // Trigger stock calculation when changes occur in the bundle components table
    $(document).on('input', '#bundle_components_body input', calculateBundleStock);
    $(document).on('click', '.remove-item', calculateBundleStock);

    // Calculate stock on page load
    calculateBundleStock();

});
