jQuery(document).ready(function ($) {
    const tabs = $('.nav-tab-wrapper a');
    const tabContents = $('.tab-content');

    // Handle tab click events
    tabs.on('click', function (e) {
        e.preventDefault();

        // Get tab name from URL
        const urlParams = new URLSearchParams($(this).attr('href').split('?')[1]);
        const tabName = urlParams.get('tab');

        // Log active tab name
        console.log('Active Tab:', tabName);

        // Remove active classes
        tabs.removeClass('nav-tab-active');
        tabContents.removeClass('active');

        // Activate current tab and content
        $(this).addClass('nav-tab-active');
        const activeContent = $(`#${tabName}`);
        if (activeContent.length) {
            activeContent.addClass('active');
        }

        // Log active content
        console.log('Active Content:', activeContent);
    });

    // Set the active tab based on the URL query parameter
    const currentParams = new URLSearchParams(window.location.search);
    const currentTab = currentParams.get('tab') || 'connected-platforms'; // Default tab
    const activeTab = $(`.nav-tab-wrapper a[href*="tab=${currentTab}"]`);
    const activeContent = $(`#${currentTab}`);

    // Log default active tab and content
    console.log('Default Active Tab:', currentTab);
    console.log('Default Active Content:', activeContent);

    if (activeTab.length) activeTab.addClass('nav-tab-active');
    if (activeContent.length) activeContent.addClass('active');
});
