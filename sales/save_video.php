<?php
if (isset($_FILES['video'])) {
    $videoBlob = $_FILES['video']['tmp_name'];

    // Generate a unique filename for the video
    $filename = 'captured_video_' . time() . '.webm';

    // Move the video file to the server's upload directory
    move_uploaded_file($videoBlob, 'uploads/' . $filename);

    // Optionally, you can store the filename or do additional processing here

    echo $filename; // Send the filename back to the client
} else {
    echo 'No video data received';
}
?>