jQuery(document).ready(function ($) {
    console.log('Picking Scripts Loaded: JS ready');

    // Automatically fetch order details if the order number is in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const orderNumberFromUrl = urlParams.get('order_number');
    if (orderNumberFromUrl) {
        fetchOrderDetails(orderNumberFromUrl);
    }

    // Function to fetch order details
    function fetchOrderDetails(orderNumber) {
        console.log('Fetching order details for order number:', orderNumber);
        $.ajax({
            url: packscan_vars.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_picking_order_details',
                order_number: orderNumber,
                security: packscan_vars.nonce
            },
            success: function (response) {
                console.log('Fetch order details response:', response);
                if (response.success) {
                    // Populate the order details and items
                    $('#order-number').text(response.data.order_details.custom_order_number);
                    $('#customer-name').text(response.data.order_details.client_name);
                    $('#shipping-address').text(response.data.order_details.shipping_address);
                    $('#shipping-method').text(response.data.order_details.shipping_method);
                    $('#payment-method').text(response.data.order_details.payment_method);
                    $('#customer-notes').text(response.data.order_details.customer_notes);

                    var itemsTableBody = $('#order-items tbody');
                    itemsTableBody.empty(); // Clear existing rows
                    response.data.order_details.items.forEach(function (item) {
                        var row = '<tr data-item-id="' + item.item_id + '" data-sku="' + item.sku + '" data-qty-on-order="' + item.quantity_ordered + '" data-qty-picked="' + item.quantity_picked + '">' +
                            '<td>' + item.sku + '</td>' +
                            '<td>' + item.name + '</td>' +
                            '<td>' + item.quantity_ordered + '</td>' +
                            '<td class="qty-picked">' + item.quantity_picked + '</td>' +
                            '</tr>';
                        itemsTableBody.append(row);
                    });

                    $('#order-details').show(); // Show the order details section
                } else {
                    showAlert(response.data.message, 'alert-danger');
                }
            },
            error: function () {
                showAlert('An error occurred while fetching order details.', 'alert-danger');
            }
        });
    }

    // Fetch order details when button is clicked
    $('#fetch-order-details').on('click', function () {
        var orderNumber = $('#order-number-input').val().trim();
        if (orderNumber) {
            fetchOrderDetails(orderNumber);
        } else {
            showAlert('Please enter a valid order number.', 'alert-danger');
        }
    });

    // Process SKU input
    function processSKUInput() {
        var enteredSKU = $('#sku-input').val().trim().toUpperCase();

        if (!enteredSKU) {
            console.log('No SKU entered');
            return;
        }

        var matchingRow = $('#order-items tbody tr').filter(function () {
            var skuData = $(this).data('sku');
            if (typeof skuData === 'string') {
                return skuData.toUpperCase() === enteredSKU;
            } else {
                console.warn('SKU data is not a string or is undefined:', skuData);
                return false;  // Prevents matching if skuData is not a valid string
            }
        });

        if (matchingRow.length) {
            flashScreenGreen();
            $('#sku-error-message').hide();

            var qtyOnOrder = parseInt(matchingRow.data('qty-on-order'));
            var qtyPicked = parseInt(matchingRow.data('qty-picked'));

            if (qtyPicked < qtyOnOrder) {
                qtyPicked++;
                matchingRow.data('qty-picked', qtyPicked);
                matchingRow.find('.qty-picked').text(qtyPicked);
                savePickedQuantities(); // Save picked quantities with current user
                $('#sku-input').val('');
            } else {
                showModal();
                $('#sku-input').val('');
            }
        } else {
            flashScreenRed();
            $('#sku-error-message').show();
            $('#sku-input').val('');
        }
    }

    // Trigger SKU processing on Tab or Enter key
    $('#sku-input').on('keydown', function (event) {
        if (event.key === 'Tab' || event.key === 'Enter' || event.keyCode === 9 || event.keyCode === 13) {
            event.preventDefault(); // Prevent default tab/enter behavior
            processSKUInput(); // Call the SKU processing function
        }
    });

    $('#process-sku').on('click', function (event) {
        event.preventDefault();
        processSKUInput();
    });

    // Clear picked quantities
    $('#clear-picked-qty').on('click', function () {
        $('#order-items tbody tr').each(function () {
            $(this).data('qty-picked', 0);
            $(this).find('.qty-picked').text(0);
        });
    });

    // Continue to packing
    $('#continue-to-packing').on('click', function (event) {
        event.preventDefault();
        savePickedQuantities(function () {
            var orderNumber = $('#order-number').text().trim();
            var packingUrl = packscan_vars.packing_screen_url + '&order_number=' + encodeURIComponent(orderNumber);
            window.location.href = packingUrl;
        });
    });

    // Save picked quantities and current user
    function savePickedQuantities(callback) {
        var orderNumber = $('#order-number').text().trim();
        var pickedQuantities = [];
        var currentUser = packscan_vars.current_user; // Assume current user info is available via localized object

        $('#order-items tbody tr').each(function () {
            var itemID = $(this).data('item-id');
            var pickedQty = parseInt($(this).find('.qty-picked').text());
            pickedQuantities.push({
                item_id: itemID,
                picked_qty: pickedQty,
                picked_by_user: currentUser // Include current user
            });
        });

        $.ajax({
            url: packscan_vars.ajax_url,
            method: 'POST',
            data: {
                action: 'save_picked_quantities',
                order_number: orderNumber,
                picked_quantities: pickedQuantities,
                security: packscan_vars.nonce
            },
            success: function (response) {
                console.log('Save picked quantities response:', response);
                if (response.success) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                } else {
                    showAlert(response.data.message, 'alert-danger');
                }
            },
            error: function () {
                showAlert('An error occurred while saving picked quantities.', 'alert-danger');
            }
        });
    }

    // Flash and modal functions
    function showModal() {
        $('#sku-input').prop('disabled', true);
        $('body').addClass('flash-red');
        $('#excess-qty-modal').show();
    }

    $('#close-warning-modal').on('click', function () {
        $('#excess-qty-modal').hide();
        $('body').removeClass('flash-red');
        $('#sku-input').prop('disabled', false).focus();
    });

    function flashScreenGreen() {
        $('body').addClass('flash-green');
        setTimeout(function () {
            $('body').removeClass('flash-green');
        }, 500);
    }

    function flashScreenRed() {
        $('body').addClass('flash-red');
        setTimeout(function () {
            $('body').removeClass('flash-red');
        }, 500);
    }

    function showAlert(message, alertType) {
        var alertDiv = $('<div class="alert ' + alertType + '" style="font-size: 20px; text-align: center; padding: 10px; border-radius: 5px;">' + message + '</div>');
        $('body').prepend(alertDiv);
        setTimeout(function () {
            alertDiv.fadeOut(function () {
                $(this).remove();
            });
        }, 3000);
    }
});
