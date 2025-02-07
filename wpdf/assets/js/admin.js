jQuery(document).ready(function ($) {

    // Show modal on Add Platform button click
    $('#add-platform-btn').on('click', function () {
        $('#platform-form-modal').show();
    });




    // Handle platform type change
$('#platform_type').on('change', function () {
        const platform = $(this).val();
        let formFields = '';

        if (platform === 'woocommerce') {
            formFields = `
                <tr>
                    <th><label for="site_name">Site Name</label></th>
                    <td><input type="text" id="site_name" name="site_name" required>
                        <p class="description">Human readable Site name (e.g., Example.com)</p></td>
                </tr>
                <tr>
                    <th><label for="site_url">Site URL</label></th>
                    <td><input type="url" id="site_url" name="site_url" required>
                        <p class="description">Full URL of the website (e.g., https://www.yourdomain.co.za/)</p></td>
                </tr>
                <tr>
                    <th><label for="api_key">API Key</label></th>
                    <td><input type="text" id="api_key" name="api_key" required>
                        <p class="description">Generated on the connecting website under WooCommerce Settings</p></td>
                </tr>
                <tr>
                    <th><label for="api_secret">API Secret</label></th>
                    <td><input type="password" id="api_secret" name="api_secret" required>
                        <p class="description">Generated on the connecting website under WooCommerce Settings</p></td>
                </tr>`;
        } else if (platform === 'takealot') {
            formFields = `
                <tr>
                    <th><label for="store_name">Store Name</label></th>
                    <td><input type="text" id="store_name" name="store_name" required>
                        <p class="description">Your store name as listed on Takealot.com</p></td>
                </tr>
                <tr>
                    <th><label for="store_id">Store ID</label></th>
                    <td><input type="text" id="store_id" name="store_id" required>
                        <p class="description">ID of your store in Takealot Seller Portal</p></td>
                </tr>
                <tr>
                    <th><label for="api_key">API Key</label></th>
                    <td><input type="text" id="api_key" name="api_key" required>
                        <p class="description">Located in the Seller Portal under API Integration > Authentication</p></td>
                </tr>
                <tr>
                    <th><label for="warehouse_id">Warehouse ID</label></th>
                    <td><input type="text" id="warehouse_id" name="warehouse_id" required>
                        <p class="description">Located under API Integration > Offers in the Seller Portal</p></td>
                </tr>
                <tr>
                    <th><label for="webhook_url">Webhook URL</label></th>
                    <td><input type="url" id="webhook_url" name="webhook_url" readonly value="https://yourdomain.co.za/wpdispatchforge/takealot-webhook/">
                        <p class="description">Copy this URL into the Takealot Seller Portal to set up webhooks</p></td>
                </tr>
                <tr>
                    <th><label for="webhook_secret">Webhook Secret</label></th>
                    <td><input type="text" id="webhook_secret" name="webhook_secret" required>
                        <p class="description">Provided by the Seller Portal when setting up webhooks</p></td>
                </tr>`;
        }

        $('#platform-form-fields').html(formFields);
    });

    // Close modal on outside click (optional)
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#platform-form-modal, #add-platform-btn').length) {
            $('#platform-form-modal').hide();
        }
    });
    
        // Test Connection
    $('.test-connection').on('click', function () {
        const siteId = $(this).data('site-id');
        $.post(ajaxurl, {
            action: 'test_connection',
            site_id: siteId,
        }, function (response) {
            alert(response.message);
        });
    });

    // Remove Site
    $('.remove-site').on('click', function () {
        const siteId = $(this).data('site-id');
        if (confirm('Are you sure you want to remove this site?')) {
            $.post(ajaxurl, {
                action: 'remove_site',
                site_id: siteId,
            }, function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            });
        }
    });

    // Edit Site
    $('.edit-site').on('click', function () {
        const siteId = $(this).data('site-id');
        // Fetch data and populate the form
        $.post(ajaxurl, {
            action: 'get_site_data',
            site_id: siteId,
        }, function (response) {
            if (response.success) {
                // Populate form fields with the site data
                $('#platform_type').val(response.data.platform).change();
                $('#platform-form-fields input, #platform-form-fields select').each(function () {
                    const field = $(this).attr('name');
                    $(this).val(response.data[field]);
                });
                $('#platform-form-modal').show();
            } else {
                alert(response.message);
            }
        });
    });

    // Sync Products
    $('.sync-products').on('click', function () {
        const siteId = $(this).data('site-id');
        $.post(ajaxurl, {
            action: 'sync_products',
            site_id: siteId,
        }, function (response) {
            alert(response.message);
        });
    });

    // Sync Orders
    $('.sync-orders').on('click', function () {
        const siteId = $(this).data('site-id');
        $.post(ajaxurl, {
            action: 'sync_orders',
            site_id: siteId,
        }, function (response) {
            alert(response.message);
        });
    });
    
});
