<html>
<head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery library -->
<script>
function create_notifications() {
    // User_id = 1: First User's ID
    // Command = INSERT_NOTIFICATION
    var create_notifications_url =
        "notifications_controller.php" +
        "?command=INSERT_NOTIFICATION" +
        "&user_id=1" +
        "&creation_date_time=201610101200" +
        "&view_date_time=2016101012000" +
        "&notification_text=Hello" +
        "&is_viewed=NO";

    $(document).ready(function() {
        $.ajax({
            url: create_notifications_url,
            cache: false,
            success: function(html) {
                // Handle success response here if needed
            },
            error: function(xhr, status, error) {
                // Handle error response here if needed
                console.error(error);
            }
        });
    });
}
</script>
</head>

<body>
    <input type="button" value="Create Notification" onclick="create_notifications()">
</body>
</html>
