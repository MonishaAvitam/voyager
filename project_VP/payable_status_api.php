<?php
require '../conn.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get outsourced projects with payable status from all three tables
    function getOutsourcedPayableStatus() {
        global $conn;
    
        // Get data from paidinvoices table
        $sql_paid = "SELECT 
        pi.project_id,
        p.revision_project_id,
        pi.invoice_no,
        pi.comments,
        pi.vendor_id,
        pi.service_id,
        (SELECT GROUP_CONCAT(name) FROM services WHERE FIND_IN_SET(id, REPLACE(REPLACE(pi.service_id, '[\"', ''), '\"]', ''))) as service_name,
        pi.invoice_date,
        pi.amount,
        pi.booked_date,
        pi.received_date,
        NOW() as timestamp,
        'paid' as payment_status,
        'paid' as source_table,
        v.company_name,
        v.phone,
        v.contact_person,
        v.address,
        v.state,
        v.email_id
    FROM 
        paidinvoices pi
    LEFT JOIN 
        projects p ON pi.project_id = p.project_id
    LEFT JOIN
        vendors v ON pi.vendor_id = v.id";

// Get data from ready_to_pay table
$sql_ready = "SELECT 
        rtp.project_id,
        p.revision_project_id,
        rtp.invoice_no,
        rtp.comments,
        rtp.vendor_id,
        rtp.service_id,
        (SELECT GROUP_CONCAT(name) FROM services WHERE FIND_IN_SET(id, REPLACE(REPLACE(rtp.service_id, '[\"', ''), '\"]', ''))) as service_name,
        rtp.invoice_date,
        rtp.amount,
        rtp.booked_date,
        rtp.received_date,
        NOW() as timestamp,
        'ready to pay' as payment_status,
        'ready_to_pay' as source_table,
        v.company_name,
        v.phone,
        v.contact_person,
        v.address,
        v.state,
        v.email_id
    FROM 
        ready_to_pay rtp
    LEFT JOIN 
        projects p ON rtp.project_id = p.project_id
    LEFT JOIN
        vendors v ON rtp.vendor_id = v.id";

// Get data from unpaidinvoices table
$sql_unpaid = "SELECT 
        ui.project_id,
        p.revision_project_id,
        ui.invoice_no,
        ui.comments,
        ui.vendor_id,
        ui.service_id,
        (SELECT GROUP_CONCAT(name) FROM services WHERE FIND_IN_SET(id, REPLACE(REPLACE(ui.service_id, '[\"', ''), '\"]', ''))) as service_name,
        ui.invoice_date,
        ui.amount,
        ui.booked_date,
        ui.received_date,
        NOW() as timestamp,
        'unpaid' as payment_status,
        'unpaid' as source_table,
        v.company_name,
        v.phone,
        v.contact_person,
        v.address,
        v.state,
        v.email_id
    FROM 
        unpaidinvoices ui
    LEFT JOIN 
        projects p ON ui.project_id = p.project_id
    LEFT JOIN
        vendors v ON ui.vendor_id = v.id";
    
        // Execute queries
        $result_paid = $conn->query($sql_paid);
        $result_ready = $conn->query($sql_ready);
        $result_unpaid = $conn->query($sql_unpaid);
    
        if (!$result_paid || !$result_ready || !$result_unpaid) {
            return ["error" => $conn->error];
        }
    
        // Combine results
        $data = [];
    
        // Process paid invoices
        while ($row = $result_paid->fetch_assoc()) {
            formatRow($row);
            $data[] = $row;
        }
    
        // Process ready to pay invoices
        while ($row = $result_ready->fetch_assoc()) {
            formatRow($row);
            $data[] = $row;
        }
    
        // Process unpaid invoices
        while ($row = $result_unpaid->fetch_assoc()) {
            formatRow($row);
            $data[] = $row;
        }
    
        return $data;
    }
    // Helper function to format row data
    function formatRow(&$row) {
        // Format the project ID for display
        $displayId = $row['revision_project_id'] ? $row['revision_project_id'] : $row['project_id'];
        $row['display_id'] = $displayId;
        
        // Format dates
        $row['invoice_date_formatted'] = $row['invoice_date'] ? date('Y-m-d', strtotime($row['invoice_date'])) : 'N/A';
        $row['booked_date_formatted'] = $row['booked_date'] ? date('Y-m-d', strtotime($row['booked_date'])) : 'N/A';
        $row['received_date_formatted'] = $row['received_date'] ? date('Y-m-d', strtotime($row['received_date'])) : 'N/A';
        
        // Format payment status based on source table
        if ($row['source_table'] === 'paid') {
            $row['payment_status_formatted'] = 'Paid';
            $row['payment_status_color'] = 'success';
        } else if ($row['source_table'] === 'ready_to_pay') {
            $row['payment_status_formatted'] = 'Ready to Pay';
            $row['payment_status_color'] = 'warning';
        } else if ($row['source_table'] === 'unpaid') {
            $row['payment_status_formatted'] = 'Unpaid';
            $row['payment_status_color'] = 'danger';
        } else {
            $row['payment_status_formatted'] = 'Unknown';
            $row['payment_status_color'] = 'secondary';
        }
    }
    
    // Handle API requests
    $response = getOutsourcedPayableStatus();
    echo json_encode($response);
    
} catch (Exception $e) {
    // Return error as JSON
    echo json_encode(['error' => $e->getMessage()]);
}
?>
