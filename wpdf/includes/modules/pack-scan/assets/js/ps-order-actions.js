jQuery(document).ready(function($) {
    console.log('Order Actions Script Loaded');

    // Fetch and display the packing report
    function fetchPackingReport(orderID) {
        console.log('Fetching packing report for order ID:', orderID);

        $.ajax({
            url: packscan_vars.ajax_url,
            method: 'POST',
            data: {
                action: 'generate_packing_report',
                order_id: orderID,
                security: packscan_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    console.log('Packing report fetched successfully');
                    displayReport(response.data.items, response.data.discrepancies);
                } else {
                    console.log('Error fetching packing report:', response.data.message);
                }
            },
            error: function() {
                console.log('An error occurred while fetching the packing report.');
            }
        });
    }

    // Display the packing report
    function displayReport(items, discrepancies) {
        var reportTable = $('#report-table tbody');
        reportTable.empty(); // Clear existing rows

        items.forEach(function(item) {
            var row = '<tr>' +
                '<td>' + item.name + '</td>' +
                '<td>' + item.sku + '</td>' +
                '<td>' + item.quantity_ordered + '</td>' +
                '<td>' + item.quantity_picked + '</td>' +
                '<td>' + item.quantity_packed + '</td>' +
                '</tr>';
            reportTable.append(row);

            if (item.discrepancy) {
                reportTable.find('tr:last').addClass('discrepancy'); // Highlight discrepancies
            }
        });

        // Show discrepancy actions if needed
        if (discrepancies) {
            $('#discrepancy-actions').show();
        } else {
            $('#discrepancy-actions').hide();
        }
    }

    // Handle user action for discrepancies
    $('#process-discrepancy-action').on('click', function(event) {
        event.preventDefault();

        var orderID = $('#order-id').val();
        var actionType = $('input[name="discrepancy-action"]:checked').val();

        if (!actionType) {
            alert('Please select an action.');
            return;
        }

        $.ajax({
            url: packscan_vars.ajax_url,
            method: 'POST',
            data: {
                action: 'process_discrepancy_action',
                order_id: orderID,
                action_type: actionType,
                security: packscan_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Action processed successfully.');
                    window.location.href = packscan_vars.redirect_url; // Redirect to order list or another page
                } else {
                    alert('Error processing action: ' + response.data.message);
                }
            },
            error: function() {
                alert('An error occurred while processing the action.');
            }
        });
    });

    // Example usage: Fetch packing report for a given order ID
    var orderID = $('#order-id').val(); // Assume this value is available
    fetchPackingReport(orderID);
});

