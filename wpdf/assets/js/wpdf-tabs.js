(function ($) {
    $(document).ready(function () {
        // Handle tab clicks
        $('.wpdf-tab-headers li').on('click', function () {
            var tabId = $(this).find('a').attr('href');

            // Remove active class from all tabs and headers
            $('.wpdf-tab-headers li').removeClass('active');
            $('.wpdf-tab-content').removeClass('active');

            // Add active class to the clicked tab and corresponding content
            $(this).addClass('active');
            $(tabId).addClass('active');

            return false; // Prevent default anchor behavior
        });

        // Activate the first tab by default
        $('.wpdf-tab-headers li:first').addClass('active');
        $('.wpdf-tab-content:first').addClass('active');
    });
})(jQuery);
