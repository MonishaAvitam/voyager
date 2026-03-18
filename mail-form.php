<?php

require 'authentication.php'; // admin authentication check 

require 'conn.php';

include 'include/login_header.php';

// auth check

$user_id = $_SESSION['admin_id'];

$user_name = $_SESSION['name'];

$security_key = $_SESSION['security_key'];

if ($user_id == NULL || $security_key == NULL) {

    header('Location: index.php');
}

// check admin

$user_role = $_SESSION['user_role'];

include 'include/sidebar.php';

include 'add_project.php';

include 'enquiry.php';

?>

<?php

// Check if the project_id parameter is present in the URL

if (isset($_GET['project_id'])) {
    // Get the project ID from the URL
    $project_id = $_GET['project_id'];

    // Include your database connection
    include 'conn.php';

    // Construct your SQL query to fetch the project name and customer_id
    $sql = "SELECT project_name, contact_id FROM deliverable_data WHERE project_id = $project_id";

    // Execute the query
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Fetch the project name and customer_id from the result
        $row = $result->fetch_assoc();
        $project_name = $row['project_name'];
        $contact_id = $row['contact_id'];

        // Construct a SQL query to fetch the contact email using a prepared statement
        $sql2 = "SELECT contact_email,customer_name FROM contacts WHERE contact_id = ?";

        // Create a prepared statement
        $stmt = $conn->prepare($sql2);

        // Bind the customer_id as a parameter
        $stmt->bind_param("i", $contact_id);

        // Execute the prepared statement
        $stmt->execute();

        // Bind the result to a variable
        $stmt->bind_result($contact_email, $customer_name);

        // Fetch the result
        $stmt->fetch();

        // Close the prepared statement
        $stmt->close();

        // Populate the input field with the project name and contact email
        echo '<script>document.getElementById("projectName").value = "' . $project_name . '";</script>';
        echo '<script>document.getElementById("to_mail").value = "' . $contact_email . '";</script>';
    }

    // Close the database connection
}


$project_name = $row['project_name'];




?>

<style>
    @import url(https://fonts.googleapis.com/css?family=Open+Sans:400italic,400,300,600);

    #contact_mail input {

        font: 400 12px/16px;

        width: 100%;

        border: 1px solid #CCC;

        background: #FFF;

        margin: 10 5px;

        padding: 10px;

    }

    .h1_mail {

        margin-bottom: 30px;

        font-size: 30px;

    }

    #contact_mail {

        background: #F9F9F9;

        padding: 25px;

        margin: 50px 0;

    }

    .fieldset_mail {

        border: medium none !important;

        margin: 0 0 10px;

        min-width: 100%;

        padding: 0;

        width: 100%;

    }

    .file-preview {
        display: flex;
        flex-wrap: wrap;
    }

    .file-preview-item {
        margin-right: 10px;
        margin-bottom: 10px;
    }

    .file-preview-item img {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }
</style>

<div class="container-fluid">

    <form id="contact_mail" action="mail.php" method="post" enctype="multipart/form-data">

        <h1 class="h1_mail">Compose Mail</h1>

        <fieldset class="fieldset_mail">

            <input placeholder="Client Name " value="<?php echo $customer_name  ?>" name="name" type="text" tabindex="1" required>

        </fieldset>

        <fieldset class="fieldset_mail">

            <input type="email" name="to_mail" id="" value="<?php echo $contact_email; ?>" hidden>

        </fieldset>

        <fieldset class="fieldset_mail">

            <input value="<?php echo $project_id ?>" placeholder="project_id" type="text" name="project_id" tabindex="4" hidden>
            <input value="<?php echo $project_name ?>" placeholder="Subject" type="text" name="subject" tabindex="4" required>

        </fieldset>

        <fieldset class="fieldset_mail">

            <textarea style="width: 100%; height:100px; " name="message" placeholder="Type your Message Details Here..." tabindex="5"></textarea>

        </fieldset>

        <fieldset class="fieldset_mail">
            <label for="">Deliverable Files</label>
            <div id="file_preview" class="d-flex file-preview"></div>
            <div class="d-flex align-items-center mr-3">
                <input type="file" name="selected_files[]" multiple onchange="previewFiles(this)">
            </div>
        </fieldset>

        <fieldset class="fieldset_mail">

            <button class="btn btn-primary" type="submit" name="send_deliverables" id="contact-submit">Send Now</button>

        </fieldset>

    </form>

</div>

<script>
    function previewFiles(input) {
        var filePreview = document.getElementById('file_preview');
        filePreview.innerHTML = ''; // Clear existing preview

        var files = input.files;
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var reader = new FileReader();

            reader.onload = function (e) {
                var previewItem = document.createElement('div');
                previewItem.classList.add('file-preview-item');

                // Use a unique identifier for each preview item
                previewItem.id = 'file_preview_item_' + i;

                // Display an image preview for image files, adjust as needed
                if (file.type.startsWith('image/')) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    previewItem.appendChild(img);
                }

                // Display file name as text for all files
                var fileName = document.createElement('p');
                fileName.textContent = file.name;
                previewItem.appendChild(fileName);

                filePreview.appendChild(previewItem);
            };

            reader.readAsDataURL(file);
        }
    }
</script>

<?php include 'include/footer.php' ?>