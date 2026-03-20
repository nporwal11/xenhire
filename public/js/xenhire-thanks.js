jQuery(document).ready(function ($) {
    var appId = (typeof xenhireThanksData !== 'undefined') ? xenhireThanksData.appId : 0;
    if (appId) {
        // Call SendMail API
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_public_send_mail',
                nonce: xenhireAjax.nonce,
                application_id: appId
            },
            success: function (res) {
                // console.log('Mail Sent:', res);
            }
        });
    }

    // Logout Logic
    $('#xh-logout-btn').click(function (e) {
        e.preventDefault();
        // Clear Cookies
        document.cookie = "xenhire_candidate_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "xenhire_candidate_email=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = "xenhire_candidate_otp=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

        // Redirect
        window.location.href = xenhireAjax.login_url;
    });
});
