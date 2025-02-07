jQuery(document).ready(function ($) {
    // Click event to show order details in a popup
    $('.kanban-card').on('click', function () {
        var orderId = $(this).data('order-id');

        // Fetch order details using AJAX
        $.ajax({
            url: ajaxurl, // WordPress AJAX URL
            method: 'POST',
            data: {
                action: 'fetch_order_details',
                order_id: orderId
            },
            success: function (response) {
                if (response.success) {
                    $('.order-details-content').html(response.data);
                    $('#order-details-modal').fadeIn();
                } else {
                    alert('Failed to load order details.');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error); // Debugging statement
            }
        });
    });

    // Close modal on click
    $(document).on('click', '.close-modal', function () {
        $('#order-details-modal').fadeOut();
    });

    // Initialize draggable and droppable for kanban cards
    $('.kanban-card').draggable({
        helper: 'clone', // Use clone to maintain the original position
        revert: 'invalid',
        appendTo: 'body', // Allow dragging over the entire document
        start: function (event, ui) {
            $(ui.helper).css({
                zIndex: 1000, // Bring the card to the front during drag
                width: $(this).width() // Maintain the card's width during drag
            });
        }
    });

    $('.kanban-column').droppable({
        accept: '.kanban-card',
        tolerance: 'pointer', // Make the drop easier to trigger
        drop: function (event, ui) {
            var orderId = $(ui.helper).data('order-id');
            var newStatus = $(this).data('status');

            // Update order status using AJAX
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'update_order_status',
                    order_id: orderId,
                    new_status: newStatus
                },
                success: function (response) {
                    if (response.success) {
                        // Move the original card to the new column
                        var originalCard = $(ui.draggable); // Get the original card
                        originalCard.detach().css({ // Detach and reset its position
                            top: 'auto',
                            left: 'auto',
                            position: 'relative', // Reset to relative positioning
                            zIndex: 'auto' // Reset z-index to normal
                        }).appendTo($(event.target));

                        console.log('Order status updated successfully.'); // Debugging statement
                    } else {
                        alert('Error updating order status!');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error on status update:', status, error); // Debugging statement
                }
            });
        }
    });
});
