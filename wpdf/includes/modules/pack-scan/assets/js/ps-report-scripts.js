jQuery(document).ready(function ($) {
    console.log('Report Scripts Loaded: JS ready');

    // Function to set messages with proper styling
    function setMessage(message, type) {
        var messageClass = (type === 'success') ? 'alert-success' : 'alert-danger';
        var alertDiv = $('<div class="alert ' + messageClass + '" style="font-size: 20px; text-align: center; padding: 10px; margin-top: 20px; border-radius: 5px;">' + message + '</div>');
        $('#report-message').html(alertDiv).show();
        setTimeout(function () {
            alertDiv.fadeOut(function () {
                $(this).remove();
            });
        }, 3000);
    }

    // Function to fetch order report details
    function fetchReportDetails(orderNumber) {
        console.log('Fetching report details for order:', orderNumber);

        $.ajax({
            url: packscan_vars.ajax_url,
            method: 'POST',
            data: {
                action: 'fetch_order_report_details',
                order_number: orderNumber,
                security: packscan_vars.nonce
            },
            success: function (response) {
                console.log('Fetch report success response:', response);
                if (response.success) {
                    // Populate the order details and items
                    $('#order-number').text(response.data.order_details.custom_order_number);
                    $('#customer-name').text(response.data.order_details.client_name);
                    $('#shipping-address').text(response.data.order_details.shipping_address);
                    $('#billing-details').text(response.data.order_details.billing_address);
                    $('#shipping-method').text(response.data.order_details.shipping_method);
                    $('#payment-method').text(response.data.order_details.payment_method);
                    $('#picking-user').text(response.data.order_details.picking_user); // Display picking user
                    $('#packing-user').text(response.data.order_details.packing_user); // Display packing user

                    var itemsTableBody = $('#order-items tbody');
                    itemsTableBody.empty(); // Clear existing rows
                    var hasDiscrepancies = false;
                    response.data.order_details.items.forEach(function (item) {
                        // Check for discrepancies between quantity_packed and quantity_ordered
                        var discrepancyClass = (item.quantity_ordered != item.quantity_packed) ? 'discrepancy-row' : '';
                        var row = '<tr class="' + discrepancyClass + '">' +
                            '<td>' + item.sku + '</td>' +
                            '<td>' + item.name + '</td>' +
                            '<td>' + item.quantity_ordered + '</td>' +
                            '<td>' + item.quantity_picked + '</td>' +  // Keeping picked for display, but not for comparison
                            '<td>' + item.quantity_packed + '</td>' +
                            '</tr>';
                        itemsTableBody.append(row);

                        if (item.quantity_ordered != item.quantity_packed) {
                            hasDiscrepancies = true;
                        }
                    });

                    $('#order-details').show();
                    $('#order-items').show();
                    $('.button-group').show();

                    // Show/Hide buttons based on discrepancies
                    if (hasDiscrepancies) {
                        $('#complete-order').hide();
                        $('#hold-order, #split-order').show();
                    } else {
                        $('#hold-order, #split-order').hide();
                        $('#complete-order').show();
                    }

                    setMessage('Report loaded successfully.', 'success');
                } else {
                    setMessage(response.data.message, 'error');
                }
            },
            error: function () {
                setMessage('An error occurred while fetching order report details.', 'error');
            }
        });
    }

    // Extract order number from URL and fetch report
    const urlParams = new URLSearchParams(window.location.search);
    const orderNumberFromUrl = urlParams.get('order_number');
    if (orderNumberFromUrl) {
        console.log('Fetching report details from URL order_number:', orderNumberFromUrl);
        $('#report-order-number').val(orderNumberFromUrl); // Set the input field with the order number from URL
        fetchReportDetails(orderNumberFromUrl); // Fetch the report details immediately
    }

    // User-triggered fetch report action
    $('#fetch-report-details').on('click', function () {
        var orderNumber = $('#report-order-number').val().trim();
        console.log('Fetch Report button clicked, order number:', orderNumber);
        if (orderNumber) {
            fetchReportDetails(orderNumber);
        } else {
            setMessage('Please enter a valid order number.', 'error');
        }
    });

    // Handle On Hold action
    $('#hold-order').on('click', function () {
        var orderNumber = $('#order-number').text().trim();
        console.log('Attempting to place order on hold for order number:', orderNumber);

        if (orderNumber) {
            $.ajax({
                url: packscan_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'packscan_place_order_on_hold',
                    order_number: orderNumber,
                    security: packscan_vars.nonce
                },
                success: function (response) {
                    console.log('AJAX success response:', response);
                    if (response.success) {
                        setMessage(response.data.message, 'success');
                        location.reload(); // Reload the page to reflect status change
                    } else {
                        setMessage(response.data.message, 'error'); // Properly display the error message
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    setMessage('Failed to place order on hold. Please try again.', 'error');
                }
            });
        } else {
            setMessage('No order number found to place on hold.', 'error');
        }
    });

    // Splitting the Order Logic
    $('#split-order').on('click', function () {
        var orderNumber = $('#order-number').text().trim();
        console.log('Attempting to split order for order number:', orderNumber);

        if (orderNumber) {
            $.ajax({
                url: packscan_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'packscan_split_order',
                    order_number: orderNumber,
                    security: packscan_vars.nonce
                },
                success: function (response) {
                    console.log('AJAX success response:', response);
                    if (response.success) {
                        setMessage(response.data.message, 'success');
                        location.reload(); // Reload the page to reflect new orders
                    } else {
                        setMessage(response.data.message, 'error'); // Properly display the error message
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    setMessage('Failed to split order. Please try again.', 'error');
                }
            });
        } else {
            setMessage('No order number found to split.', 'error');
        }
    });

    // Complete Order Logic
    $('#complete-order').on('click', function () {
        var orderNumber = $('#order-number').text().trim();
        console.log('Attempting to complete order for order number:', orderNumber);

        if (orderNumber) {
            $.ajax({
                url: packscan_vars.ajax_url,
                method: 'POST',
                data: {
                    action: 'packscan_complete_order',
                    order_number: orderNumber,
                    security: packscan_vars.nonce
                },
                success: function (response) {
                    console.log('AJAX success response:', response);
                    if (response.success) {
                        setMessage(response.data.message, 'success');
                        location.reload(); // Reload the page to show the updated order status
                    } else {
                        setMessage(response.data.message, 'error'); // Display the error message
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    setMessage('Failed to complete the order. Please try again.', 'error');
                }
            });
        } else {
            setMessage('No order number found to complete.', 'error');
        }
    });

    // Print report logic
    $('#print-report').on('click', function () {
        var orderNumber = $('#order-number').text().trim();
        if (orderNumber) {
            var printUrl = packscan_vars.print_report_url + '?order_number=' + encodeURIComponent(orderNumber);
            window.open(printUrl, '_blank');
        } else {
            setMessage('No order number found to print.', 'error');
        }
    });
});
