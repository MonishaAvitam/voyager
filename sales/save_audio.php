<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['audio']['error']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES['audio']['tmp_name'];
        $destinationFolder = 'temp_voice_note/';
        if (!file_exists($destinationFolder)) {
            mkdir($destinationFolder, 0777, true);
        }
        $destinationFile = $destinationFolder . 'voice_note_latest' . '.wav';

        if (move_uploaded_file($tempFile, $destinationFile)) {
            echo 'Audio file saved successfully: ' . $destinationFile;
        } else {
            echo 'Failed to save audio file';
        }
    } else {
        echo 'Error uploading audio file';
    }
} else {
    echo 'Invalid request';
}
