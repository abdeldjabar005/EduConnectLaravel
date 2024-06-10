$(document).ready(function() {
    $('#login-form').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                // Check if login is successful
                if (response.success) {
                    // Redirect to the dashboard
                    window.location.href = '/admin/dashboard';
                } else {
                    // Display the error message
                    $('#error-message').text(response.message);
                }
            },
            error: function(response) {
                // Check if the status code is 403 (Forbidden)
                if (response.status === 403) {
                    // Display a custom error message
                    $('#error-message').text('You are not authorized to perform this action.');
                } else {
                    // Display the error message
                    $('#error-message').text(response.responseJSON.message);
                }
            }
        });
    });
    if (performance.navigation.type === 1) {
        window.location.replace(window.location.href);
    }
});
