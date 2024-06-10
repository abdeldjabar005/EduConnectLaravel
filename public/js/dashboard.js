$(document).ready(function() {
    $('.dropdown-toggle').on('click', function() {
        $(this).next('.dropdown-menu').toggle();
    });

    // Handle form submission
    $('form').on('submit', function(e) {
        e.preventDefault();

        // Show a confirmation dialog
        var confirmAction = confirm('Are you sure you want to perform this action?');
        if (!confirmAction) {
            return;
        }

        var form = $(this);
        var url = form.attr('action');

        $.ajax({
            type: 'POST',
            url: url,
            data: form.serialize(),
            success: function(response) {
                // Handle success
                alert(response.message);
                console.log(response);
                // Remove the row from the table
                $('#school-' + response.school_id).remove();
            },
            error: function(response) {
                // Handle error
                alert('An error occurred: ' + response.responseJSON.message);
            }
        });
    });
    if (performance.navigation.type === 1) {
        window.location.replace(window.location.href);
    }
});
