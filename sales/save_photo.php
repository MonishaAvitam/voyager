<?php
if (isset($_POST['photo'])) {
    $dataURL = $_POST['photo'];

    // Remove the "data:image/png;base64," prefix from the data URL
    $base64_image = str_replace('data:image/png;base64,', '', $dataURL);

    // Decode the base64 image data
    $image_data = base64_decode($base64_image);

    // Save the image to a file on the server
    $filename = 'captured_photo_' . time() . '.png';
    file_put_contents($filename, $image_data);

    // Optionally, you can store the filename or do additional processing here

    echo 'Photo saved successfully';
} else {
    echo 'No photo data received';
}
?>