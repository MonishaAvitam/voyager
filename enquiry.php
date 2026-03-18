<?php
include 'conn.php';
?>


<!-- Rest of your HTML code -->

<!-- Enquire data -->
<div class="modal fade" id="customer_id_data" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                                <div class="form-group">
                                    <label class="control-label">Customer Name</label>
                                    <div class="">
                                        <input type="text" placeholder="Customer Name" id="customer_name" name="customer_name" class="form-control" id="default">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Enquiry Details</label>
                                    <div class="">
                                        <textarea name="enquiry_details" id="enquiry_details" cols="5" rows="5" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Customer Contact</label>
                                    <div class="">
                                        <input type="text" placeholder="Customer Contact" id="customer_contact" name="customer_contact" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Customer Email-Id</label>
                                    <div class="">
                                        <input type="text" placeholder="Customer Email-Id" id="customer_emailid" name="customer_emailid" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Customer Address</label>
                                    <div class="">
                                        <input type="text" placeholder="Customer Address" id="customer_address" name="customer_address" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">File Attachment</label>
                                    <div class="">
                                        <input type="file" name="attachment_file[]" id="attachment_file" class="form-control" multiple>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="voiceNote">Voice Note:</label><br>
                                    <button class="btn btn-success" id="startRecordingBtn" type="button">Start Recording</button>
                                    <button class="btn btn-danger" id="stopRecordingBtn" type="button" disabled>Stop Recording</button>
                                    <audio class="mt-3" id="audioPlayer" controls style="display: block;"></audio>

                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="add_enquiry_sales" class="btn btn-primary">Add Enquiry</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </form>

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
      "positionClass":  "toast-top-right", // Set the position of the notification
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