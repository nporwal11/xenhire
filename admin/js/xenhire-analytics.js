jQuery(document).ready(function($) {
    
    var submitButton = document.getElementById('kt_analytics_submit');
    var form = document.getElementById('kt_analytics_form');
    
    // Form submit handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitButton.setAttribute('data-kt-indicator', 'on');
        submitButton.disabled = true;
        
        // Get values from inputs
        var analyticsData = {
            FacebookAnalyticsCode: $('#FacebookAnalyticsCode').val().trim(),
            GoogleAnalyticsCode: $('#GoogleAnalyticsCode').val().trim(),
            GoogleTagManager: $('#GoogleTagManager').val().trim()
        };
        
        // AJAX call to save data
        $.ajax({
            url: xenhireAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'xenhire_save_analytics',
                nonce: xenhireAjax.nonce,
                analytics: analyticsData
            },
            success: function(response) {
                
                if (response.success) {
                    // Success notification
                    Swal.fire({
                        title: 'Success!',
                        text: 'Analytics settings saved successfully!',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '999999';
                        }
                    });
                } else {
                    // Error notification
                    Swal.fire({
                        title: 'Error!',
                        text: response.data?.message || 'Failed to save analytics settings',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        didOpen: () => {
                            document.querySelector('.swal2-container').style.zIndex = '999999';
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                
                // Network error notification
                Swal.fire({
                    title: 'Oops!',
                    text: 'An unexpected error occurred. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    didOpen: () => {
                        document.querySelector('.swal2-container').style.zIndex = '999999';
                    }
                });
            },
            complete: function() {
                // Hide loading state
                submitButton.setAttribute('data-kt-indicator', 'off');
                submitButton.disabled = false;
            }
        });
    });
    
});

