jQuery(document).ready(function ($) {
    $('.nav-tab').on('click', function (e) {
        e.preventDefault();

        // Switch active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Show corresponding tab content
        const target = $(this).attr('href');
        $('.tab-content').addClass('hidden');
        $(target).removeClass('hidden');
    });

    // Show the first tab by default
    $('.tab-content').first().addClass('active').removeClass('hidden');
    
    // Show modal on Add Platform button click
    $('#add-platform-btn').on('click', function () {
        $('#platform-form-modal').show();
    });

    // Handle platform type change
    $('#platform_type').on('change', function () {
        const platform = $(this).val();
        let formFields = '';

        switch (platform) {
            case 'woocommerce':
                formFields = `
                    <th><label for="site_name">Site Name</label></th>
                    <td><input type="text" id="site_name" name="site_name" required></td>
                    <tr><th><label for="site_url">Site URL</label></th>
                    <td><input type="url" id="site_url" name="site_url" required></td></tr>
                    <tr><th><label for="api_key">API Key</label></th>
                    <td><input type="text" id="api_key" name="api_key" required></td></tr>
                    <tr><th><label for="api_secret">API Secret</label></th>
                    <td><input type="password" id="api_secret" name="api_secret" required></td></tr>`;
                break;
            case 'takealot':
                formFields = `
                    <th><label for="store_name">Store Name</label></th>
                    <td><input type="text" id="store_name" name="store_name" required></td>
                    <tr><th><label for="api_key">API Key</label></th>
                    <td><input type="text" id="api_key" name="api_key" required></td></tr>`;
                break;
            case 'amazon':
            case 'etsy':
                formFields = `
                    <th><label for="store_name">Store Name</label></th>
                    <td><input type="text" id="store_name" name="store_name" required></td>`;
                break;
        }

        $('#platform-form-fields').html(formFields);
    });

    // Close modal on outside click (optional)
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#platform-form-modal, #add-platform-btn').length) {
            $('#platform-form-modal').hide();
        }
    });
});
