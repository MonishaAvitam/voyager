<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php';
require '../conn.php';
include './include/login_header.php';

$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
    header('Location: ../index.php');
}

include './include/sidebar.php';
?>
<style>
    .form-step {
        position: relative;
        /* stays in flow */
        opacity: 0;
        /* hidden by default */
        max-height: 0;
        /* collapse height */
        overflow: hidden;
        /* avoid layout breaks */
        transform: translateY(20px);
        transition: all 0.4s ease-in-out;
        pointer-events: none;
        /* prevent clicks */
    }

    .form-step.active {
        opacity: 1;
        max-height: 1000px;
        /* enough to fit content */
        transform: translateY(0);
        pointer-events: all;
    }


    .form-step.inactive-left {
        opacity: 0;
        transform: translateX(-100%);
    }

    .form-step.inactive-right {
        opacity: 0;
        transform: translateX(100%);
    }

    /* ensures hidden ones don’t flash */
    .form-step.hidden {
        display: none;
    }


    .spinner-border {
        width: 1rem;
        height: 1rem;
    }
</style>

<div class="container my-5">
    <h3 class="mb-4">Custom Payslip</h3>

    <form id="payslipForm">
        <!-- Step 1: Payslip Period -->
        <div class="form-step" id="step-1">
            <div class="card shadow-sm border-0 rounded-3 p-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-calendar3 text-primary me-2"></i> Payslip Period
                    </h5>

                    <!-- Month Picker -->
                    <div class="mb-4">
                        <label for="monthPicker" class="form-label fw-semibold">Select Month</label>
                        <input type="month" class="form-control form-control-lg border-primary shadow-sm"
                            id="monthPicker">
                    </div>

                    <!-- Pay Period Preview -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pay Period</label>
                        <div class="p-3 border rounded bg-light d-flex align-items-center shadow-sm">
                            <i class="bi bi-clock-history text-secondary me-2"></i>
                            <span id="datePreview" class="fw-bold text-primary">
                                Select a month to see the range
                            </span>
                        </div>
                    </div>

                    <!-- Next Button -->
                    <div class="text-end">
                        <button type="button" class="btn btn-primary btn-lg px-4 shadow-sm" id="next-1">
                            Next <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Employee Details -->
        <div class="form-step" id="step-2">
            <div class="card shadow-sm border-0 rounded-3 p-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-person-badge text-primary me-2"></i> Employee Details
                    </h5>

                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employeeID" class="form-label fw-semibold">
                                    <i class="bi bi-person-vcard me-1 text-secondary"></i> Employee ID
                                </label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="employeeID">
                            </div>
                            <div class="mb-3">
                                <label for="employeeName" class="form-label fw-semibold">
                                    <i class="bi bi-person me-1 text-secondary"></i> Employee Name
                                </label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="employeeName">
                            </div>
                            <div class="mb-3">
                                <label for="department" class="form-label fw-semibold">
                                    <i class="bi bi-building me-1 text-secondary"></i> Department
                                </label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="department">
                            </div>
                            <div class="mb-3">
                                <label for="designation" class="form-label fw-semibold">
                                    <i class="bi bi-briefcase me-1 text-secondary"></i> Designation
                                </label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="designation">
                            </div>
                            <div class="mb-3">
                                <label for="doj" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-event me-1 text-secondary"></i> Date of Joining
                                </label>
                                <input type="date" class="form-control form-control-lg shadow-sm" id="doj">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="employeeEmail" class="form-label fw-semibold">
                                    <i class="bi bi-envelope-at me-1 text-secondary"></i> Employee Email ID
                                </label>
                                <input type="email" class="form-control form-control-lg shadow-sm" id="employeeEmail">
                            </div>
                            <div class="mb-3">
                                <label for="availableCasual" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-check me-1 text-secondary"></i> Available Casual Leave
                                </label>
                                <input type="number" class="form-control form-control-lg shadow-sm"
                                    id="availableCasual">
                            </div>
                            <div class="mb-3">
                                <label for="balanceCasual" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-minus me-1 text-secondary"></i> Balance Casual Leave
                                </label>
                                <input type="number" class="form-control form-control-lg shadow-sm" id="balanceCasual">
                            </div>
                            <div class="mb-3">
                                <label for="availableSick" class="form-label fw-semibold">
                                    <i class="bi bi-emoji-frown me-1 text-secondary"></i> Available Sick Leave
                                </label>
                                <input type="number" class="form-control form-control-lg shadow-sm" id="availableSick">
                            </div>
                            <div class="mb-3">
                                <label for="balanceSick" class="form-label fw-semibold">
                                    <i class="bi bi-heart-pulse me-1 text-secondary"></i> Balance Sick Leave
                                </label>
                                <input type="number" class="form-control form-control-lg shadow-sm" id="balanceSick">
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4 shadow-sm" id="prev-2">
                            <i class="bi bi-arrow-left-circle me-2"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary btn-lg px-4 shadow-sm" id="next-2">
                            Next <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Step 3: Bank & Leave Details -->
        <div class="form-step" id="step-3">
            <div class="card shadow-sm border-0 rounded-3 p-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-bank text-primary me-2"></i> Bank & PAN Details
                    </h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="panID" class="form-label fw-semibold">
                                    <i class="bi bi-credit-card-2-front me-1 text-secondary"></i> PAN ID
                                </label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="panID">
                            </div>
                            <div class="mb-3">
                                <label for="bankName" class="form-label fw-semibold">
                                    <i class="bi bi-building-columns me-1 text-secondary"></i> Bank Name
                                </label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="bankName">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bankAccount" class="form-label fw-semibold">
                                    <i class="bi bi-wallet2 me-1 text-secondary"></i> Bank Account Number
                                </label>
                                <input type="text" class="form-control form-control-lg shadow-sm" id="bankAccount">
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4 shadow-sm" id="prev-3">
                            <i class="bi bi-arrow-left-circle me-2"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary btn-lg px-4 shadow-sm" id="next-3">
                            Next <i class="bi bi-arrow-right-circle ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Step 4: Salary Details -->
        <div class="form-step" id="step-4">
            <div class="card shadow-sm border-0 rounded-3 p-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-cash-stack text-success me-2"></i> Salary Details
                    </h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="grossSalary" class="form-label fw-semibold">
                                    <i class="bi bi-cash me-1 text-secondary"></i> Gross Salary
                                </label>
                                <input type="number" class="form-control form-control-lg shadow-sm" id="grossSalary">
                            </div>
                            <div class="mb-3">
                                <label for="deduction" class="form-label fw-semibold">
                                    <i class="bi bi-dash-circle me-1 text-danger"></i> Deduction
                                </label>
                                <input type="number" class="form-control form-control-lg shadow-sm" id="deduction">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reimbursement" class="form-label fw-semibold">
                                    <i class="bi bi-plus-circle me-1 text-primary"></i> Reimbursement Expenses
                                </label>
                                <input type="number" class="form-control form-control-lg shadow-sm" id="reimbursement">
                            </div>
                            <div class="mb-3">
                                <label for="totalSalary" class="form-label fw-semibold">
                                    <i class="bi bi-calculator me-1 text-success"></i> Total
                                </label>
                                <input type="number" class="form-control form-control-lg shadow-sm bg-light"
                                    id="totalSalary" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4 shadow-sm" id="prev-4">
                            <i class="bi bi-arrow-left-circle me-2"></i> Previous
                        </button>
                        <button type="button" class="btn btn-success btn-lg px-4 shadow-sm d-flex align-items-center"
                            id="previewPayslip">
                            <span id="previewText">Preview Payslip</span>
                            <span id="previewSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"
                                aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Step 5: Payslip Preview -->
        <div class="form-step" id="step-5">
            <div class="card shadow-sm border-0 rounded-3 p-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i> Payslip Preview
                    </h5>

                    <!-- Preview Section -->
                    <div id="payslipPreview" class="p-4 border rounded bg-light shadow-sm text-center">
                        <p class="text-muted fst-italic">Payslip content will appear here after generation.</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4 shadow-sm" id="prev-5">
                            <i class="bi bi-arrow-left-circle me-2"></i> Previous
                        </button>
                        <div class="d-flex align-items-center">
                            <button type="button"
                                class="btn btn-primary btn-lg px-4 shadow-sm d-flex align-items-center"
                                id="sendEmailBtn">
                                <i class="bi bi-envelope-paper me-2"></i>
                                <span id="sendText">Send Payslip</span>
                                <span id="sendSpinner" class="spinner-border spinner-border-sm ms-2 d-none"
                                    role="status" aria-hidden="true"></span>
                            </button>
                            <span id="emailStatus" class="ms-3 text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
    const steps = document.querySelectorAll('.form-step');
    let currentStep = 0;
    let lastGeneratedFile = null; // store PDF filename for sending email

    // Show step function
    function showStep(step) {
        steps.forEach((s, i) => {
            s.classList.remove('active', 'inactive-left', 'inactive-right', 'hidden');

            if (i === step) {
                s.classList.add('active');
                s.classList.remove('hidden');
            } else if (i < step) {
                s.classList.add('inactive-left');
            } else {
                s.classList.add('inactive-right');
            }
        });
    }


    // Validate all inputs in a step
    function validateStep(step) {
        const inputs = steps[step].querySelectorAll('input');
        let valid = true;
        inputs.forEach(input => {
            if (!input.value) {
                input.classList.add('is-invalid'); // Bootstrap red border
                valid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });
        return valid;
    }

    // Show initial step
    showStep(currentStep);

    // Handle Next buttons
    document.getElementById('next-1').addEventListener('click', () => {
        if (validateStep(0)) { currentStep = 1; showStep(currentStep); }
    });
    document.getElementById('next-2').addEventListener('click', () => {
        if (validateStep(1)) { currentStep = 2; showStep(currentStep); }
    });
    document.getElementById('next-3').addEventListener('click', () => {
        if (validateStep(2)) { currentStep = 3; showStep(currentStep); }
    });

    // Handle Previous buttons
    document.getElementById('prev-2').addEventListener('click', () => { currentStep = 0; showStep(currentStep); });
    document.getElementById('prev-3').addEventListener('click', () => { currentStep = 1; showStep(currentStep); });
    document.getElementById('prev-4').addEventListener('click', () => { currentStep = 2; showStep(currentStep); });

    // Month picker date range calculation
    const monthPicker = document.getElementById('monthPicker');
    const datePreview = document.getElementById('datePreview');
    monthPicker.addEventListener('change', function () {
        const [year, month] = this.value.split('-').map(Number);
        if (!year || !month) return;
        const startDate = new Date(year, month - 2, 15);
        const endDate = new Date(year, month - 1, 15);
        const formatDate = d => ("0" + d.getDate()).slice(-2) + "/" + ("0" + (d.getMonth() + 1)).slice(-2) + "/" + d.getFullYear();
        datePreview.textContent = `${formatDate(startDate)} to ${formatDate(endDate)}`;
    });

    // Auto-calculate total salary
    const gross = document.getElementById('grossSalary');
    const deduction = document.getElementById('deduction');
    const reimbursement = document.getElementById('reimbursement');
    const total = document.getElementById('totalSalary');

    [gross, deduction, reimbursement].forEach(el => {
        el.addEventListener('input', () => {
            const g = parseFloat(gross.value) || 0;
            const d = parseFloat(deduction.value) || 0;
            const r = parseFloat(reimbursement.value) || 0;
            total.value = g - d + r;
        });
    });

    // Preview button
    document.getElementById('previewPayslip').addEventListener('click', () => {
        if (!validateStep(3)) return;

        document.getElementById('previewSpinner').classList.remove('d-none');
        document.getElementById('previewText').textContent = "Generating...";

        function numberToWords(num) {
            const a = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven',
                'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen',
                'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
            const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
                'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            function inWords(n) {
                if (n < 20) return a[n];
                if (n < 100) return b[Math.floor(n / 10)] + (n % 10 ? " " + a[n % 10] : "");
                if (n < 1000) return a[Math.floor(n / 100)] + " Hundred" + (n % 100 ? " " + inWords(n % 100) : "");
                if (n < 100000) return inWords(Math.floor(n / 1000)) + " Thousand" + (n % 1000 ? " " + inWords(n % 1000) : "");
                if (n < 10000000) return inWords(Math.floor(n / 100000)) + " Lakh" + (n % 100000 ? " " + inWords(n % 100000) : "");
                return inWords(Math.floor(n / 10000000)) + " Crore" + (n % 10000000 ? " " + inWords(n % 10000000) : "");
            }
            if (num === 0) return "Zero Rupees only";
            return inWords(num); // remove "Rupees only" here
        }
        const totalAmount = parseFloat(document.getElementById('totalSalary').value) || 0;
        const totalInWords = numberToWords(totalAmount);
        const data = {
            EMPLOYEE_NAME: document.getElementById('employeeName').value,
            BANK_NAME: document.getElementById('bankName').value,
            AMOUNT_IN_WORDS: totalInWords,
            PAY_MONTH: document.getElementById('datePreview').textContent,
            SALARY: '₹' + (document.getElementById('grossSalary').value || 0),
            DEDuction: document.getElementById('deduction').value,
            DESIGNATION: document.getElementById('designation').value,
            DEPARTMENT: document.getElementById('department').value,
            ACCOUNT_NUMBER: document.getElementById('bankAccount').value.replace(/\d(?=\d{4})/g, "*"),
            EMPLOYEE_ID: document.getElementById('employeeID').value,
            PAN_ID: document.getElementById('panID').value,
            DOJ: document.getElementById('doj').value,
            OTHER_EXPENSES: document.getElementById('reimbursement').value,
            DS: document.getElementById('deduction').value || 0,
            TOTAL_SALARY: '₹' + document.getElementById('totalSalary').value,
            TOTAL_IN_WORDS: totalInWords,
            ACL: document.getElementById('availableCasual').value || 'N/A',
            BCL: document.getElementById('balanceCasual').value || 'N/A',
            ASL: document.getElementById('availableSick').value || 'N/A',
            BSL: document.getElementById('balanceSick').value || 'N/A'
        };

        fetch('generate_preview.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(response => {
                if (response.error) { alert(response.error); return; }

                document.getElementById('payslipPreview').innerHTML = `
                    <iframe src="${response.url}" style="width:100%; height:600px;" frameborder="0"></iframe>
                `;
                lastGeneratedFile = response.file; // save filename for email
                currentStep = 4;
                showStep(currentStep);
            })
            .catch(err => alert("Error generating preview: " + err))
            .finally(() => {
                document.getElementById('previewSpinner').classList.add('d-none');
                document.getElementById('previewText').textContent = "Preview Payslip";
            });
    });

    // Previous from preview (cleanup temp files)
    document.getElementById('prev-5').addEventListener('click', () => {
        fetch('./cleanup_temp.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => console.log("Cleanup response:", data))
            .catch(err => console.error("Cleanup error:", err))
            .finally(() => {
                currentStep = 3;
                showStep(currentStep);
            });
    });

    // Send Payslip by Email
    // Send Payslip by Email
    document.getElementById('sendEmailBtn').addEventListener('click', () => {
        const email = document.getElementById('employeeEmail').value;
        const file = lastGeneratedFile;
        const employeeName = document.getElementById('employeeName').value;
        const payMonth = document.getElementById('datePreview').textContent;

        if (!email || !file) {
            document.getElementById('emailStatus').textContent = "Missing email or PDF.";
            return;
        }

        document.getElementById('emailStatus').textContent = "Sending...";

        fetch('send_email.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },

            body: JSON.stringify({
                email: email,
                file: lastGeneratedFile,
                EMPLOYEE_NAME: employeeName,
                PAY_MONTH: payMonth
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('emailStatus').textContent = "Email sent successfully!";
                    // Trigger cleanup_temp.php
                    return fetch('./cleanup_temp.php', { method: 'POST' });
                } else {
                    throw new Error(data.error);
                }
            })
            .then(res => res.json())
            .then(cleanupData => {
                console.log("Cleanup response:", cleanupData);
                // Reload the page after cleanup
                window.location.reload();
            })
            .catch(err => {
                document.getElementById('emailStatus').textContent = "Error: " + err.message;
                console.error(err);
            });
    });


    // Fallback number to words

</script>


<?php
include './include/footer.php';
?>