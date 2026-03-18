<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../authentication.php'; // admin authentication check 
require '../conn.php';
include './include/login_header.php';

// auth check
$user_id = $_SESSION['admin_id'];
$user_name = $_SESSION['name'];
$security_key = $_SESSION['security_key'];
if ($user_id == NULL || $security_key == NULL) {
  header('Location: ../index.php');
}



// check admin
$countStatus = isset($_SESSION['countStatus']);

if ($_SESSION['payslipAccess'] === 'Granted') {
  header('Location:./payslip.php');
}

include './include/sidebar.php';

$sql = "
    SELECT
        (SELECT COUNT(*) FROM projects) AS projects_count,
        (SELECT COUNT(*) FROM deliverable_data) AS deliverable_data_count;
";


$result = $conn->query($sql);


if ($result) {
  $row = $result->fetch_assoc();
  $projects = $row['projects_count'];
  $total_deliverable_data = $row['deliverable_data_count'];
  $result->free();
} else {
  echo "Error executing query: " . $conn->error;
}


$sql01 = "
    SELECT COUNT(*) AS invoiced_projects 
    FROM (
    SELECT * FROm csa_finance_invoiced WHERE payment_status IS NULL or payment_status = ''
    ) AS combined_data; 
";



$result01 = $conn->query($sql01);


if ($result01) {
  $row = $result01->fetch_assoc();
  $invoiced_projects = $row['invoiced_projects'];
  $result01->free();
} else {
  echo "Error executing query: " . $conn->error;
}

$sql02 = "
    SELECT 
      COUNT(*) AS Total_readytobeinvoiced_projects
  FROM (
     SELECT *
                        FROM (
                            SELECT 
                                dd.project_id,
                                dd.urgency,
                                dd.reopen_status,
                                NULL AS revision_project_id,
                                dd.project_name,
                                dd.p_team,
                                dd.setTarget,
                                dd.setTargetDate,
                                NULL AS quick_project,
                                c.customer_name,
                                sp.price,
                                sp.comments,
                                sp.invoice_number,
                                sp.last_modified_date,
                                sp.rownumber,
                                sp.id,
                                sp.service_date,
                                sp.due_date,
                                sp.project_status
                            FROM 
                                deliverable_data dd 
                            LEFT JOIN 
                                contacts c ON dd.contact_id = c.contact_id
                            LEFT JOIN 
                                csa_finance_readytobeinvoiced sp ON dd.project_id = sp.project_id
                            WHERE 
                                EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id)
                            AND NOT EXISTS (
                                SELECT 1 
                                FROM csa_finance_invoiced i 
                                WHERE i.project_id = sp.project_id 
                                AND i.rownumber = sp.rownumber
                            )
                            
                            UNION ALL
                            
                            SELECT 
                                p.project_id,
                                p.urgency,
                                p.reopen_status,
                                p.revision_project_id,
                                p.project_name,
                                p.p_team,
                                p.setTarget,
                                p.setTargetDate,
                                p.quick_project,
                                c.customer_name,
                                sp.price,
                                sp.comments,
                                sp.invoice_number,
                                sp.last_modified_date,
                                sp.rownumber,
                                sp.id,
                                sp.service_date,
                                sp.due_date,
                                sp.project_status
                            FROM 
                                projects p 
                            LEFT JOIN 
                                contacts c ON p.contact_id = c.contact_id
                            LEFT JOIN 
                                csa_finance_readytobeinvoiced sp ON p.project_id = sp.project_id
                            WHERE 
                                EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id)
                        ) AS combined_data
                        WHERE NOT EXISTS (
                            SELECT 1 
                            FROM csa_finance_invoiced i 
                            WHERE i.project_id = combined_data.project_id 
                            AND i.rownumber = combined_data.rownumber
                        )
  ) AS combined_data;

  ";




$result02 = $conn->query($sql02);


if ($result02) {
  $row = $result02->fetch_assoc();
  $readytobeinvoiced_projects = $row['Total_readytobeinvoiced_projects'];
  $result02->free();
} else {
  echo "Error executing query: " . $conn->error;
}

$sql03 = "
SELECT
    (
        SELECT COUNT(*) AS target_projects_count
        FROM projects
        LEFT JOIN csa_finance_invoiced ON projects.project_id = csa_finance_invoiced.project_id
        WHERE projects.setTarget = 'Active' 
        AND csa_finance_invoiced.project_id IS NULL
        AND NOT EXISTS (
            SELECT 1
            FROM csa_finance_readytobeinvoiced
            WHERE csa_finance_readytobeinvoiced.project_id = projects.project_id
        )
    ) AS project_target_count,
    (
        SELECT COUNT(*) AS target_deliverables_count
        FROM deliverable_data
        LEFT JOIN csa_finance_invoiced ON deliverable_data.project_id = csa_finance_invoiced.project_id
        WHERE deliverable_data.setTarget = 'Active' 
        AND csa_finance_invoiced.project_id IS NULL
        AND NOT EXISTS (
            SELECT 1
            FROM csa_finance_readytobeinvoiced
            WHERE csa_finance_readytobeinvoiced.project_id = deliverable_data.project_id
        )
    ) AS deliverable_target_count;
";


$result03 = $conn->query($sql03);

if ($result03) {
  $row = $result03->fetch_assoc();
  $target_projects_count = $row['project_target_count'];
  $target_deliverables_count = $row['deliverable_target_count'];
  $result03->free();
} else {
  echo "Error executing query: " . $conn->error;
}

$total_target_projects_count = $target_projects_count + $target_deliverables_count;


$sql04 = "
SELECT 
    COUNT(*) AS project_count 
FROM (
             
           SELECT 
             dd.project_id,
             dd.urgency,
             dd.reopen_status,
             NULL AS quick_project,
             dd.project_name,
             dd.p_team,
             NULL AS revision_project_id,
             dd.setTarget,
             c.customer_name,
             cu.price,
             cu.comments,
             cu.rownumber,
             cu.last_modified_date,
             cu.service_date  -- add this line
           FROM 
             deliverable_data dd 
           LEFT JOIN 
             contacts c ON dd.contact_id = c.contact_id
           LEFT JOIN
             csa_finance_uninvoiced cu ON dd.project_id = cu.project_id
           WHERE 
             dd.setTarget IS NULL
             AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE dd.project_id = FI.project_id AND cu.rownumber = FI.rownumber)
             AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE dd.project_id = FI.project_id AND cu.rownumber IS NULL)
             AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id AND cu.rownumber = RI.rownumber)
             AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE dd.project_id = RI.project_id AND cu.rownumber IS NULL)
           
           UNION ALL
           
           SELECT 
             p.project_id,
             p.urgency,
             p.reopen_status,
             p.quick_project,
             p.project_name,
             p.p_team,
             p.revision_project_id,
             p.setTarget,
             c.customer_name,
             cu.price,
             cu.comments,
             cu.rownumber,
             cu.last_modified_date,
             cu.service_date  -- add this line
           FROM 
             projects p 
           LEFT JOIN 
             contacts c ON p.contact_id = c.contact_id
           LEFT JOIN
             csa_finance_uninvoiced cu ON p.project_id = cu.project_id
           WHERE 
             p.setTarget IS NULL
             AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE p.project_id = FI.project_id AND cu.rownumber = FI.rownumber)
             AND NOT EXISTS (SELECT 1 FROM csa_finance_invoiced FI WHERE p.project_id = FI.project_id AND cu.rownumber IS NULL)
             AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id AND cu.rownumber = RI.rownumber)
             AND NOT EXISTS (SELECT 1 FROM csa_finance_readytobeinvoiced RI WHERE p.project_id = RI.project_id AND cu.rownumber IS NULL)
             
           
) AS project_counts;
";


$result04 = $conn->query($sql04);

if ($result04) {
  $row = $result04->fetch_assoc();
  $project_count = $row['project_count'];
  $result04->free();
} else {
  echo "Error executing query: " . $conn->error;
}


$sql05 = "
    SELECT COUNT(*) AS paid_invoiced_projects 
    FROM (SELECT * FROM csa_finance_invoiced WHERE payment_status IS NOT NULL AND payment_status != '') AS combined_data;  
";


$result05 = $conn->query($sql05);


if ($result05) {
  $row = $result05->fetch_assoc();
  $paid_invoiced_projects = $row['paid_invoiced_projects'];
  $result05->free();
} else {
  echo "Error executing query: " . $conn->error;
}

$sql06 = "
SELECT 
    COUNT(*) AS unpaidInvoices 
FROM unpaidinvoices;
";

$result06 = $conn->query($sql06); // Corrected to use $sql06

if ($result06) {
  $row = $result06->fetch_assoc();
  $unpaidInvoices = $row['unpaidInvoices'];
  $result06->free();
} else {
  echo "Error executing query: " . $conn->error;
}

$sql07 = "
SELECT 
    COUNT(*) AS ready_to_pay 
FROM ready_to_pay;
";

$result07 = $conn->query($sql07); // Corrected to use $sql07

if ($result07) {
  $row = $result07->fetch_assoc();
  $ready_to_pay = $row['ready_to_pay'];
  $result07->free();
} else {
  echo "Error executing query: " . $conn->error;
}
$sql08 = "
SELECT 
    COUNT(*) AS paidinvoices 
FROM paidinvoices;
";

$result08 = $conn->query($sql08);

if ($result08) {
  $row = $result08->fetch_assoc();
  $paidinvoices = $row['paidinvoices'];
  $result08->free();
} else {
  echo "Error executing query: " . $conn->error;
}


$sql09 = "
SELECT 
    (
        SELECT COALESCE(SUM(amount), 0) FROM unpaidinvoices
    ) + (
        SELECT COALESCE(SUM(amount), 0) FROM ready_to_pay
    ) AS total_balance;
";

$result09 = $conn->query($sql09);

if ($result09) {
  $row = $result09->fetch_assoc();
  $total_balance = $row['total_balance'];
  $result09->free();
} else {
  echo "Error executing query: " . $conn->error;
}


$sql10 = "
SELECT SUM(amount) AS total_amount FROM paidinvoices
";

$result10 = $conn->query($sql10);

if ($result10) {
  $row = $result10->fetch_assoc();
  $total_payments = $row['total_amount'] ?? '0';  // Use alias from SQL
  $result10->free();
} else {
  echo "Error executing query: " . $conn->error;
}



$total_projects = $project_count + $readytobeinvoiced_projects + $invoiced_projects + $paid_invoiced_projects;

?>




<!-- dashboard content  -->

<div class="container-fluid">
  <?php if ($countStatus == 2) {  ?>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
      <h1 class="h3 mb-0 text-gray-800">Receivables Dashboard  </h1>
    </div>
  <?php } ?>

</div>
<style>
  .order-card {
    color: #fff;
  }

  .bg-c-blue {
    background: linear-gradient(45deg, #4099ff, #73b4ff);
  }

  .bg-c-green {
    background: linear-gradient(45deg, #2ed8b6, #59e0c5);
  }

  .bg-c-yellow {
    background: linear-gradient(45deg, #FFB64D, #ffcb80);
  }

  .bg-c-pink {
    background: linear-gradient(45deg, #FF5370, #ff869a);
  }

  .bg-c-navy {
    background: linear-gradient(45deg, rgb(4, 0, 120), rgb(4, 0, 77));
  }


  .card {
    border-radius: 5px;
    -webkit-box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
    box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
    border: none;
    margin-bottom: 30px;
    -webkit-transition: all 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
  }



  .card-inner {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, .125);
    border-radius: .25rem;
    border: none;
    cursor: pointer;
    transition: all 2s;
  }

  .card .card-block {
    padding: 25px;
  }

  .order-card i {
    font-size: 26px;
  }

  .f-left {
    float: left;
  }

  .f-right {
    float: right;
  }

  .search {
    width: 100%;
    margin-bottom: auto;
    margin-top: 20px;
    height: 50px;
    background-color: #fff;
    padding: 10px;
    border-radius: 5px;
    background-color: #d3d3d3;
    /* Light grey background */

  }

  .search-input {
    color: #333;
    /* Dark text color for readability */
    border: 0;
    outline: 0;
    background-color: #d3d3d3;
    /* Light grey background */
    width: 0;
    caret-color: transparent;
    transition: width 0.4s linear;
    padding: 10px;
  }

  .search .search-input {
    padding: 0 10px;
    width: 100%;
    caret-color: #536bf6;
    font-size: 19px;
    font-weight: 300;
    color: black;
    transition: width 0.4s linear;
  }

  .search-input::placeholder {
    color: #777;
    /* Grey color for the placeholder text */
  }


  .search-icon {
    height: 34px;
    width: 34px;
    float: right;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    background-color: #536bf6;
    font-size: 10px;
    bottom: 30px;
    position: relative;
    border-radius: 5px;
  }


  .search-icon-receivable {
    height: 3rem;
    width: 3rem;
    float: right;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    background-color: #536bf6;
    font-size: 10px;
    bottom: 3.1rem;
    ;
    position: relative;
    border-radius: 5px;
  }

  .search-icon-receivable:hover {

    color: #fff !important;
  }

  .search-icon:hover {

    color: #fff !important;
  }


  a:link {
    text-decoration: none
  }

  .card-inner:hover {

    transform: scale(1.1);

  }

  .mg-text span {
    color: #333;
    font-size: 12px;

  }

  .mg-text {

    line-height: 14px;
  }
</style>

<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<div class="container">




  <div class="row">
    <?php if ($countStatus == 2) {  ?>
      <div class="col-md-4 col-xl-3">
        <div class="card bg-c-blue order-card">
          <div class="card-block">
            <h6 class="m-b-20">Total UnInvoiced Projects</h6>
            <h2 class="text-right"><i class="far fa-file-alt f-left"></i><span><?php echo $project_count ?></span></h2>
            <p class="m-b-0">All Projects In Count <span class="f-right"><?php echo $total_projects ?></span></p>
          </div>
        </div>
      </div>



      <div class="col-md-4 col-xl-3">
        <div class="card bg-c-pink  order-card">
          <div class="card-block">
            <h6 class="m-b-20">Total Ready to Invoice</h6>
            <h2 class="text-right"><i class="fa fa-refresh f-left"></i><span><?php echo $readytobeinvoiced_projects ?></span></h2>
            <p class="m-b-0">All Projects In Count <span class="f-right"><?php echo $total_projects ?></span></p>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-xl-3">
        <div class="card bg-c-green order-card">
          <div class="card-block">
            <h6 class="m-b-20">Total Invoiced Projects</h6>
            <h2 class="text-right"><i class="fa fa-credit-card f-left"></i><span><?php echo $invoiced_projects ?></span></h2>
            <p class="m-b-0">All Projects In Count <span class="f-right"><?php echo $total_projects ?></span></p>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-xl-3">
        <div class="card bg-c-yellow order-card">
          <div class="card-block">
            <h6 class="m-b-20">Total Paid Projects</h6>
            <h2 class="text-right"><i class="fa fa-credit-card f-left"></i><span><?php echo $paid_invoiced_projects ?></span></h2>
            <p class="m-b-0">All Projects In Count <span class="f-right"><?php echo $total_projects ?></span></p>
          </div>
        </div>
      </div>



      <div class="d-flex justify-content-center  px-5">
        <div class="search" style="width: 55%; align-items: center; height: 4.5rem;">
          <input type="text" id="search-input-receivable" placeholder="Search Invoice no..
                " name="" class="w-100 px-2" style="background:gainsboro; outline: none; border: none; height: 3.2rem;" />
          <a href="#" class="search-icon-receivable">
            <i class="fa fa-search" style="font-size: 1.5rem;"></i>
          </a>
          <div id="search-results-receivable" style="max-height: 200px; overflow-y: auto; margin-top: 0; z-index: 1000;"></div>
        </div>
      </div>








      <?php
      $receivable_projects = [];

      // Query to get invoice numbers from the uninvoiced table
      $sql_receivable_uninvoiced = "SELECT project_id, 'Uninvoiced' AS source FROM csa_finance_uninvoiced WHERE project_status IS NULL";
      $result_receivable_uninvoiced = $conn->query($sql_receivable_uninvoiced);
      if ($result_receivable_uninvoiced->num_rows > 0) {
        while ($row_receivable = $result_receivable_uninvoiced->fetch_assoc()) {
          $receivable_projects[] = $row_receivable;
        }
      }

      // Query to get invoice numbers from the ready to invoice table
      $sql_receivable_rti = "SELECT invoice_number, project_id, 'Ready To Invoice' AS source FROM csa_finance_readytobeinvoiced WHERE project_status IS NULL";
      $result_receivable_rti = $conn->query($sql_receivable_rti);
      if ($result_receivable_rti->num_rows > 0) {
        while ($row_receivable = $result_receivable_rti->fetch_assoc()) {
          $receivable_projects[] = $row_receivable;
        }
      }

      // Query to get invoice numbers from the invoiced table
      $sql_receivable_invoiced = "SELECT invoice_number, project_id, 'Invoiced' AS source 
                            FROM csa_finance_invoiced 
                            WHERE payment_status IS NULL OR payment_status = '';";
      $result_receivable_invoiced = $conn->query($sql_receivable_invoiced);
      if ($result_receivable_invoiced->num_rows > 0) {
        while ($row_receivable = $result_receivable_invoiced->fetch_assoc()) {
          $receivable_projects[] = $row_receivable;
        }
      }

      //  From Paid table

      $sql_receivable_paid = "SELECT invoice_number, project_id, 'Paid' AS source
FROM csa_finance_invoiced
WHERE payment_status IN ('paid', 'partiallyPaid');

                              ";
      $result_receivable_paid = $conn->query($sql_receivable_paid);
      if ($result_receivable_paid->num_rows > 0) {
        while ($row_receivable = $result_receivable_paid->fetch_assoc()) {
          $receivable_projects[] = $row_receivable;
        }
      }
    } else { ?>

      <div>
        <div class="">
          <div class="row d-flex justify-content-center">

            <div class="col-md-12">
              <div class="card p-4 mt-3">
                <h3 class="heading mt-5 text-center">Hi! How can we help You?</h3>


                <div class="d-flex justify-content-center px-5">
                  <div class="search">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search Invoice Number..." name="">
                    <a href="#" class="search-icon">
                      <i class="fa fa-search"></i>
                    </a>
                    <div id="searchResults" class="list-group position-relative w-100 mt-1" style="max-height: 200px; overflow-y: auto; margin-top: 0; z-index: 1000;"></div>
                  </div>
                </div>

                <?php
                // Include your database connection
                require '../conn.php';

                // Initialize an array to hold the invoices from all tables
                $invoices = [];

                // Query to get invoice numbers from the unpaidinvoices table
                $sql_unpaid = "SELECT invoice_no,project_id, 'Unpaid' AS source FROM unpaidinvoices";
                $result_unpaid = $conn->query($sql_unpaid);
                if ($result_unpaid->num_rows > 0) {
                  while ($row = $result_unpaid->fetch_assoc()) {
                    $invoices[] = $row;
                  }
                }

                // Query to get invoice numbers from the paidinvoices table
                $sql_paid = "SELECT invoice_no,project_id, 'Paid' AS source FROM paidinvoices";
                $result_paid = $conn->query($sql_paid);
                if ($result_paid->num_rows > 0) {
                  while ($row = $result_paid->fetch_assoc()) {
                    $invoices[] = $row;
                  }
                }

                // Query to get invoice numbers from the ready_to_pay table
                $sql_ready_to_pay = "SELECT invoice_no,project_id, 'Ready to Pay' AS source FROM ready_to_pay";
                $result_ready_to_pay = $conn->query($sql_ready_to_pay);
                if ($result_ready_to_pay->num_rows > 0) {
                  while ($row = $result_ready_to_pay->fetch_assoc()) {
                    $invoices[] = $row;
                  }
                }








                // // Query to get invoice numbers from the paid table
                // $sql_receivable_paid = "SELECT invoice_no, 'Paid' AS source FROM ready_to_pay";
                // $result_ready_to_pay = $conn->query($sql_ready_to_pay);
                // if ($result_ready_to_pay->num_rows > 0) {
                //   while ($row = $result_ready_to_pay->fetch_assoc()) {
                //     $receivable_invoices[] = $row;
                //   }
                // }



                // Close the database connection
                $conn->close();
                ?>

                <script>
                  const invoices = <?php echo json_encode($invoices); ?>;

                  document.addEventListener("DOMContentLoaded", () => {

                    const searchInput = document.getElementById("searchInput");
                    const searchResults = document.getElementById("searchResults");
                    // console.log(invoices)

                    // Function to filter and display invoices
                    function filterInvoices(query) {
                      // Filter invoices based on invoice_no, project_id, OR source
                      const filteredInvoices = invoices.filter(invoice =>
                        (invoice.invoice_no && invoice.invoice_no.toLowerCase().includes(query.toLowerCase())) ||
                        (invoice.project_id && invoice.project_id.toLowerCase().includes(query.toLowerCase())) ||
                        (invoice.source && invoice.source.toLowerCase().includes(query.toLowerCase()))
                      );

                      // Clear previous results
                      searchResults.innerHTML = '';

                      // Display matching results
                      if (filteredInvoices.length > 0) {
                        filteredInvoices.forEach(invoice => {
                          const resultItem = document.createElement('a');
                          resultItem.href = '#';
                          resultItem.classList.add('list-group-item', 'list-group-item-action');
                          resultItem.textContent = `Project: ${invoice.project_id} - Invoice: ${invoice.invoice_no ? invoice.invoice_no : 'N/A'} - ${invoice.source}`;

                          // Add click event to populate input with selected invoice and redirect
                          resultItem.addEventListener('click', (e) => {
                            e.preventDefault(); // Prevent the default anchor click behavior

                            // Populate the search input with the selected invoice number
                            searchInput.value = invoice.invoice_no;

                            // Clear the search results after selection
                            searchResults.innerHTML = '';

                            // Normalize the source to lowercase and remove spaces
                            const source = invoice.source.toLowerCase().replace(/\s+/g, '_'); // Convert spaces to underscores

                            // Redirect based on invoice source (case-insensitive comparison)
                            if (source === 'paid') {
                              window.location.replace('paidinvoices.php?invoice_no=' + invoice.invoice_no); // Pass invoice number in URL
                            } else if (source === 'unpaid') {
                              window.location.replace('unpaid.php?invoice_no=' + invoice.invoice_no); // Pass invoice number in URL
                            } else if (source === 'ready_to_pay') {
                              window.location.replace('readyToPay.php?invoice_no=' + invoice.invoice_no); // Pass invoice number in URL
                            } else {
                              console.error("Invalid source: ", invoice.source); // Log invalid source if necessary
                            }
                          });

                          searchResults.appendChild(resultItem);
                        });
                      } else {
                        // If no results, show a "No results found" message
                        const noResult = document.createElement('p');
                        noResult.classList.add('list-group-item', 'list-group-item-action', 'text-center');
                        noResult.textContent = 'No results found';
                        searchResults.appendChild(noResult);
                      }
                    }

                    // Event listener for input to search invoices live
                    searchInput.addEventListener('input', () => {
                      const query = searchInput.value.trim();
                      if (query) {
                        filterInvoices(query); // Filter and display invoices
                      } else {
                        searchResults.innerHTML = ''; // Clear results if input is empty
                      }
                    });
                  });








                  // console.log(receivableProjects)
                </script>




                <div class="d-flex justify-content-center px-5 mt-5">

                  <div class="col-md-6">
                    <div class="card bg-c-navy text-white  order-card ">
                      <div class="card-block">
                        <h6 class="m-b-20">Outstanding Balance</h6>
                        <h2 class="text-right"><i class="far fa-file-alt f-left"></i><span>$ <?php echo $total_balance ?></span></h2>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="card bg-c-navy text-white  order-card ">
                      <div class="card-block">
                        <h6 class="m-b-20">Total Payments</h6>
                        <h2 class="text-right"><i class="fa fa-credit-card f-left"></i>$ <span><?php echo $total_payments ?></span></h2>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center px-5 mt-5">
                  <div class="col-md-4 col-xl-4">
                    <div class="card bg-c-pink  order-card">
                      <div class="card-block">
                        <h6 class="m-b-20">Total Unpaid Invoices</h6>
                        <h2 class="text-right"><i class="fa fa-refresh f-left"></i><span><?php echo $unpaidInvoices ?></span></h2>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-xl-4">
                    <div class="card bg-c-green order-card">
                      <div class="card-block">
                        <h6 class="m-b-20">Total Ready To Pay Invoices</h6>
                        <h2 class="text-right"><i class="fa fa-money-bill f-left"></i><span><?php echo $ready_to_pay ?></span></h2>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4 col-xl-4">
                    <div class="card bg-c-yellow order-card">
                      <div class="card-block">
                        <h6 class="m-b-20">Total Paid Invoices</h6>
                        <h2 class="text-right"><i class="fa fa-credit-card f-left"></i><span><?php echo $paidinvoices ?></span></h2>
                      </div>
                    </div>
                  </div>

                </div>

                <!-- <div class="row mt-4 g-1 px-4 mb-5">

                  <div class="col-md-4">
                    <div class="card-inner p-3 d-flex flex-column align-items-center"> <img src="../img/YLsQrn3 - Imgur.png" width="50">
                      <div class="text-center mg-text"> <span class="mg-text">Unpaid Invoices</span> </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="card-inner p-3 d-flex flex-column align-items-center"> <img src="../img/undefined - Imgur (1).png" width="50">
                      <div class="text-center mg-text"> <span class="mg-text">Ready To Pay</span> </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="card-inner p-3 d-flex flex-column align-items-center"> <img src="../img/readytopay.png" width="50">
                      <div class="text-center mg-text"> <span class="mg-text">Paid Invoices</span> </div>
                    </div>
                  </div>
                </div> -->
              </div>
            </div>
          </div>
        </div>
      </div>









    <?php  }   ?>
  </div>


</div>


<script>
  document.addEventListener("DOMContentLoaded", () => {
    const receivableProjects = <?php echo json_encode($receivable_projects); ?>;
    console.log(receivableProjects)
    const searchInputReceivable = document.getElementById("search-input-receivable");



    const searchResultsReceivable = document.getElementById("search-results-receivable");

    //  searchInputReceivable.addEventListener( "input", () => {
    //   console.log("user Input")
    // }

    // )


    // Function to filter and display invoices
    function filterReceivables(query) {
      // Filter invoices based on project_id, invoice_number, OR source
      const filteredProjects = receivableProjects.filter(project =>
        (project.project_id && project.project_id.toLowerCase().includes(query.toLowerCase())) ||
        (project.invoice_number && project.invoice_number.toLowerCase().includes(query.toLowerCase())) ||
        (project.source && project.source.toLowerCase().includes(query.toLowerCase()))
      );


      // Clear previous results
      searchResultsReceivable.innerHTML = '';

      // Display matching results
      if (filteredProjects.length > 0) {
        filteredProjects.forEach(project => {
          const resultItemReceivable = document.createElement('a');
          resultItemReceivable.href = '#';
          resultItemReceivable.classList.add('list-group-item', 'list-group-item-action');
          resultItemReceivable.textContent = `Project: ${project.project_id} - ` +
            (project.invoice_number ? `Invoice: ${project.invoice_number} - ` : '') +
            `${project.source}`;


          // Add click event to populate input with selected invoice and redirect
          resultItemReceivable.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent the default anchor click behavior

            // Populate the search input with the selected invoice number
            searchInputReceivable.value = project.invoice_number ? project.invoice_number : project.project_id;

            // Clear the search results after selection
            searchResultsReceivable.innerHTML = '';

            // Normalize the source to lowercase and remove spaces
            const source = project.source.toLowerCase().replace(/\s+/g, '_'); // Convert spaces to underscores

            // Redirect based on invoice source (case-insensitive comparison)
            if (source === 'uninvoiced') {
              window.location.replace('unInvoiced.php?project_id=' + project.project_id); // Pass invoice number in URL
            } else if (source === 'ready_to_invoice') {
              window.location.replace('readyToBeInvoiced.php?project_id=' + project.project_id); // Pass invoice number in URL
            } else if (source === 'invoiced') {
              window.location.replace('invoiced.php?project_id=' + project.project_id); // Pass invoice number in URL
            } else if (source === 'paid') {
              window.location.replace('paid_projects.php?project_id=' + project.project_id); // Pass invoice number in URL
            } else {
              console.error("Invalid source: ", project.source); // Log invalid source if necessary
            }
          });

          searchResultsReceivable.appendChild(resultItemReceivable);
        });
      } else {
        // If no results, show a "No results found" message
        const noResultReceivable = document.createElement('p');
        noResultReceivable.classList.add('list-group-item', 'list-group-item-action', 'text-center');
        noResultReceivable.textContent = 'No results found';
        searchResultsReceivable.appendChild(noResultReceivable);
      }
    }

    // Event listener for input to search invoices live
    searchInputReceivable.addEventListener('input', () => {
      const query = searchInputReceivable.value.trim();
      if (query) {
        filterReceivables(query); // Filter and display invoices
      } else {
        searchResultsReceivable.innerHTML = ''; // Clear results if input is empty
      }
    });
  });
</script>

<?php
include './include/footer.php';
?>