<?php
include '../conn.php';
$user_id = $_SESSION['admin_id'];
?>


<!-- Rest of your HTML code -->

<!-- Enquire data -->
<div class="modal fade" id="add_enquiry" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <!-- enquiry content-->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Enquiry</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form role="form" action="./enquiryuploads.php" method="post" enctype="multipart/form-data" autocomplete="off" id="voiceNoteForm">
                            <div class="form-horizontal">

                             <!-- Hidden field to store user_id -->
                             <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
                                
                                <!-- Customer Type Section -->
                                <div class="form-group">
                                    <label class="control-label">Customer Type</label>
                                    <div>
                                        <select class="form-control" id="customer_type" name="customer_type" required>
                                            <option value="">Select Customer Type</option>
                                            <option value="potential">Potential Customer</option>
                                            <option value="existing">Customer</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Common Enquiry Name Section -->
                                <div class="form-group">
                                    <label class="control-label">Enquiry Name</label>
                                    <div class="">
                                        <input name="enquiry_name" id="enquiry_name" class="form-control" required />
                                    </div>
                                </div>
                                
                                <!-- Potential Customer Section -->
                                <div id="potential_customer_section" class="customer-section" style="display: none;">
                                    <!-- Add potential customer specific fields here -->
                                    <div class="form-group">
                                        <label class="control-label">Potential Customer Details</label>
                                        <div class="">
                                            <input name="potential_customer" id="potential_customer" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                    <label for="voiceNote">Voice Note:</label><br>
                                    <button class="btn btn-success" id="startRecordingBtn" type="button">Start Recording</button>
                                    <button class="btn btn-danger" id="stopRecordingBtn" type="button" disabled>Stop Recording</button>
                                    <audio class="mt-3" id="audioPlayer" controls style="display: block;"></audio>

                                </div>
                                </div>
                                
                                <!-- Existing Customer Section -->
                                <div id="existing_customer_section" class="customer-section" style="display: none;">
                                    <div class="form-group">
                                        <label class="control-label">Customer</label>
                                        <div class="">
                                            <?php
                                            $sql = "SELECT contact_id, customer_name FROM contacts ";
                                            $info = $obj_admin->manage_all_info($sql);
                                            ?>
                                            <select class="form-control" name="contact_id" id="contact_id">
                                                <option value="">Select Customer Name</option>
                                                <?php
                                                while ($row = $info->fetch(PDO::FETCH_ASSOC)) {
                                                    echo '<option value="' . $row['contact_id'] . '">' . $row['customer_name'] . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                        <label class="control-label">Comments</label>
                                        <div class="">
                                            <input name="comments" id="comments" class="form-control" />
                                        </div>
                                    </div>

                                <!-- File Attachment Section -->
                                <div class="form-group">
                                    <label class="control-label">File Attachment (Optional)</label>
                                    <div class="">
                                        <input type="file" name="attachment_file[]" id="attachment_file" class="form-control" multiple>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" name="add_enquiry_sales" class="btn btn-primary">Add Enquiry</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('customer_type').addEventListener('change', function() {
        var potentialSection = document.getElementById('potential_customer_section');
        var existingSection = document.getElementById('existing_customer_section');
        var customerType = this.value;

        if (customerType === 'potential') {
            potentialSection.style.display = 'block';
            existingSection.style.display = 'none';
        } else if (customerType === 'existing') {
            potentialSection.style.display = 'none';
            existingSection.style.display = 'block';
        } else {
            potentialSection.style.display = 'none';
            existingSection.style.display = 'none';
        }
    });
</script>


                        <script>
                            let mediaRecorder;
                            let audioChunks = [];
                            let audioPlayer = document.getElementById('audioPlayer');

                            navigator.mediaDevices.getUserMedia({
                                    audio: true
                                })
                                .then(function(stream) {
                                    mediaRecorder = new MediaRecorder(stream);

                                    mediaRecorder.ondataavailable = function(e) {
                                        audioChunks.push(e.data);
                                    };

                                    mediaRecorder.onstop = function() {
                                        let audioBlob = new Blob(audioChunks, {
                                            type: 'audio/wav'
                                        });
                                        let audioUrl = URL.createObjectURL(audioBlob);
                                        audioPlayer.src = audioUrl;
                                        audioPlayer.style.display = 'block';

                                        // Sending audio data to server
                                        uploadAudio(audioBlob);
                                    };
                                })
                                .catch(function(err) {
                                    console.error('Error accessing microphone:', err);
                                });

                            document.getElementById('startRecordingBtn').addEventListener('click', function() {
                                audioChunks = [];
                                mediaRecorder.start();
                                this.disabled = true;
                                document.getElementById('stopRecordingBtn').disabled = false;
                            });

                            document.getElementById('stopRecordingBtn').addEventListener('click', function() {
                                mediaRecorder.stop();
                                this.disabled = true;
                                document.getElementById('startRecordingBtn').disabled = false;
                            });

                            function uploadAudio(audioBlob) {
                                let formData = new FormData();
                                formData.append('audio', audioBlob);

                                fetch('save_audio.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error('Failed to upload audio');
                                        }
                                        console.log('Audio uploaded successfully');
                                    })
                                    .catch(error => {
                                        console.error('Error uploading audio:', error);
                                    });
                            }
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Function to display loading notification
    function showLoadingNotification() {
        toastr.options = {
            "positionClass": "toast-top-center",
            "timeOut": 0, // Set timeOut to 0 for indefinite duration
            "closeButton": false, // Hide the close button
            "progressBar": true, // Show a progress bar
            "showDuration": "300", // Duration for showing the notification
            "hideDuration": "0" // Duration for hiding the notification (set to 0 for indefinite duration)
        };

        // Show the loading notification
        toastr.info("Uploading files...", "Please wait");
    }

    // Event listener for the form submission
    $('form').on('submit', function() {
        // Show the loading notification when the form is submitted
        showLoadingNotification();
    });
</script>