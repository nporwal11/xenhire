jQuery(document).ready(function ($) {

    // Refresh settings data
    $('#xenhire-refresh-settings, #xenhire-retry-settings').on('click', function (e) {
        e.preventDefault();
        location.reload();
    });

    // Handle clicks on disabled cards (Email Templates & Analytics)
    $('.xenhire-card-disabled').on('click', function () {
        var action = $(this).data('action');
        var actionName = action === 'templates' ? 'Email Templates' : 'Analytics';
        //alert(actionName + ' management - Coming in Phase 2!');
    });

});
