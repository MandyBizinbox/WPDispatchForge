jQuery(document).ready(function ($) {
    console.log('Packing Scripts Loaded: JS ready');

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
                action: 'fetch_packing_order_details',
                order_number: orderNumber,
                security: packscan_vars.nonce
            },
            success: function (response) {
                console.log('Fetch order details response:', response);
                if (response.success) {
                    populateOrderDetails(response.data.order_details);
                } else {
                    showAlert(response.data.message, 'alert-danger');
                }
            },
            error: function () {
                showAlert('An error occurred while fetching order details.', 'alert-danger');
            }
        });
    }

    // Populate order details on the screen
    function populateOrderDetails(orderDetails) {
        $('#order-number').text(orderDetails.custom_order_number);
        $('#customer-name').text(orderDetails.client_name);
        $('#shipping-address').text(orderDetails.shipping_address);
        $('#shipping-method').text(orderDetails.shipping_method);
        $('#payment-method').text(orderDetails.payment_method);
        $('#customer-notes').text(orderDetails.customer_notes);

        var itemsTableBody = $('#order-items tbody');
        itemsTableBody.empty(); // Clear existing rows
        orderDetails.items.forEach(function (item) {
            var row = '<tr data-item-id="' + item.item_id + '" data-sku="' + item.sku + '" data-qty-on-order="' + item.quantity + '" data-qty-picked="' + item.quantity_picked + '" data-qty-packed="' + item.quantity_packed + '">' +
                '<td>' + item.sku + '</td>' +
                '<td>' + item.name + '</td>' +
                '<td>' + item.quantity + '</td>' +
                '<td>' + item.quantity_picked + '</td>' +
                '<td class="qty-packed">' + item.quantity_packed + '</td>' +
                '</tr>';
            itemsTableBody.append(row);
        });

        $('#order-details').show();
        $('#order-items').show();
        $('.button-group').show();
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
            var qtyPacked = parseInt(matchingRow.data('qty-packed'));

            if (qtyPacked < qtyOnOrder) {
                qtyPacked++;
                matchingRow.data('qty-packed', qtyPacked);
                matchingRow.find('.qty-packed').text(qtyPacked);
                savePackedQuantities(); // Save packed quantities with current user
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
            event.preventDefault();
            processSKUInput();
        }
    });

    $('#process-sku').on('click', function (event) {
        event.preventDefault();
        processSKUInput();
    });

    // Clear packed quantities
    $('#clear-packed-qty').on('click', function () {
        $('#order-items tbody tr').each(function () {
            $(this).data('qty-packed', 0);
            $(this).find('.qty-packed').text(0);
        });
    });

    // Continue to report
    $('#complete-packing').on('click', function (event) {
        event.preventDefault();
        savePackedQuantities(function () {
            var orderNumber = $('#order-number').text().trim();
            var reportUrl = packscan_vars.report_screen_url + '&order_number=' + encodeURIComponent(orderNumber);
            window.location.href = reportUrl;
        });
    });

    // Save packed quantities and current user
    function savePackedQuantities(callback) {
        var orderNumber = $('#order-number').text().trim();
        var packedQuantities = [];
        var currentUser = packscan_vars.current_user; // Assume current user info is available via localized object

        $('#order-items tbody tr').each(function () {
            var itemID = $(this).data('item-id');
            var packedQty = parseInt($(this).find('.qty-packed').text());
            packedQuantities.push({
                item_id: itemID,
                packed_qty: packedQty,
                packed_by_user: currentUser // Include current user
            });
        });

        $.ajax({
            url: packscan_vars.ajax_url,
            method: 'POST',
            data: {
                action: 'save_packed_quantities',
                order_number: orderNumber,
                packed_quantities: packedQuantities,
                security: packscan_vars.nonce
            },
            success: function (response) {
                if (response.success) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                } else {
                    showAlert(response.data.message, 'alert-danger');
                }
            },
            error: function () {
                showAlert('An error occurred while saving packed quantities.', 'alert-danger');
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
        $('body').removeClass('flash-error');
        $('#sku-input').prop('disabled', false).focus();
    });

    function flashScreenGreen() {
        $('body').addClass('flash-success');
        setTimeout(function () {
            $('body').removeClass('flash-success');
        }, 500);
    }

    function flashScreenRed() {
        $('body').addClass('flash-error');
        setTimeout(function () {
            $('body').removeClass('flash-error');
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
