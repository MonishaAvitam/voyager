<div id="wrapper">
  <?php
  if (!isset($_SESSION['countStatus'])) {
    $_SESSION['countStatus'] = '2'; // Default to '1'
  }

  // Toggle session variable when the toggle link is clicked
  if (isset($_GET['toggle'])) {
    $_SESSION['countStatus'] = $_SESSION['countStatus'] === '2' ? '1' : '2';
    header('location:dashboard.php');
  }

  // Get the current session status
  $countStatus = $_SESSION['countStatus'];

  if ($_SESSION['payslipAccess'] === 'Granted') {
    $_SESSION['user_role'] = 4;
  } else {
    $_SESSION['payslipAccess'] ?? 'Not Granted';

  }
  ?>
  <!-- Sidebar -->

  <ul class="navbar-nav  sidebar sidebar-dark accordion" style="background-color: #070A19;" id="accordionSidebar">

    <!-- Sidebar - Brand -->

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">

      <div class="sidebar-brand-icon rotate-n-15">

        <i class="fa fa-cog"></i>
      </div>&nbsp;&nbsp;
      <!-- <div class="sidebar-brand-text mx-3">CSA Engineering </div> -->
      <img class="sidebar-brand-text mx-3"
        src="https://www.csaengineering.com.au/wp-content/uploads/2022/10/White-Logo.png" alt="logo"
        style="max-width: 100px; height: auto;">
    </a>
    <!-- Divider -->
    <hr class="sidebar-divider my-0">
    <?php

    if ($_SESSION['user_role'] == 5) {

      ?>
      <!-- Nav Item - Dashboard -->
      <li class="nav-item">
        <a class="nav-link" href="../allApps.php">
          <i class="fas fa-fw fa-tachometer-alt" style="color: #843DCA;"></i>
          <span>Home</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./dashboard.php">
          <i class="fas fa-fw fa-tachometer-alt" style="color: #843DCA;"></i>
          <span>Dashboard</span></a>
      </li>
      <?php if ($countStatus == 2) { ?>

        <li class="nav-item">
          <a class="nav-link " href="./quick_Project.php" data-target="#quickProjectModal">
            <i class="fas fa-paper-plane" style="color: #FFD600;"></i> <!-- Yellow -->

            <span>Quick Project</span>
          </a>
        </li>
        <li class="nav-item">
          <a href="./csa_performance.php" class="nav-link"><i class="fas fa-solid fa-chart-pie"
              style="color: #ff006e;">&nbsp;</i><span>CSA Performance</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./contacts.php">
            <i class="fas fa-fw fa-id-card" style="color: white;"></i>
            <span>Clients</span></a>
        </li>
      <?php } ?>

      <?php if ($countStatus == 1) { ?>
        <li class="nav-item">
          <a class="nav-link" href="./recordnewinvoice.php">
            <i class="fas fa-fw fa-file-invoice" style="color: white;"></i>
            <span>Record New invoice</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./vendors.php">
            <i class="fas fa-fw fa-building" style="color: white;"></i>
            <span>Vendors</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="./services.php">
            <i class="fas fa-fw fa-vials" style="color: white;"></i>
            <span>Services</span></a>
        </li>

      <?php } ?>

      <?php if ($countStatus == 2) { ?>

        <li class="nav-item">
          <a class="nav-link" href="project_status_view.php">
            <i class="fas fa-chart-line" style="color: white;"></i>
            <span>Project Status View</span></a>
        </li>

      <?php } ?>


      <li class="nav-item">
        <!-- Link to toggle the session variable -->
        <a class="nav-link bg-primary" href="?toggle=1">
          <i class="fas fa-random" style="color: white;"></i>
          <span id="toggleText">
            <?php echo $countStatus === '1' ? 'Switch to Receivables' : 'Switch to Payable'; ?>
          </span>
        </a>
      </li>







      <!-- Divider -->
      <hr class="sidebar-divider">
      <!-- Heading -->
      <div class="sidebar-heading">
        MAIN
      </div>




    <?php } ?>

    <?php



    if ($_SESSION['user_role'] == 5) {

      if ($countStatus == 2) {
        ?>

        <li class="nav-item" id="collapsed">

          <a class="nav-link collapsed" id="" href="./unInvoiced.php">

            <img src="https://cdn3.emoji.gg/emojis/4996-number-one.png"
              style="width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

            <span>UnInvoiced Projects</span>

          </a>


        </li>
        <li class="nav-item" id="collapsed">

          <a class="nav-link collapsed" href="./readyToBeInvoiced.php">
            <img src="https://www.actratoronto.com/wp-content/uploads/2020/09/512px-Icon_2_red.svg.png"
              style="width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

            <span>Ready to Invoice</span>

          </a>


        </li>
        <li class="nav-item" id="collapsed">

          <a class="nav-link collapsed" href="./invoiced.php">

            <img src="./include/Icon_3_green.svg" style="width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

            <span>Invoiced Projects</span>

          </a>


        </li>
        <li class="nav-item" id="collapsed">

          <a class="nav-link collapsed" href="./paid_projects.php">

            <img src="https://www.sonicaire.com/wp-content/uploads/2021/02/SonicAire-4.png"
              style="width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

            <span>Paid Projects</span>

          </a>


        </li>
        <li class="nav-item " style="margin-top: 2rem;" id="uncollapsed">
          <div class=""
            style="position: absolute; border-left: 1px dotted white; display: flex; margin-left: 1.6rem; width: 197px;">
            <!-- <div style="position: relative; top: 20px; left: -11px; width: 1.6rem; height: 1.4rem; border-radius: 50%; background: #4e73df; padding:  0rem  0.4rem;" > 1 </div> -->
            <img src="https://cdn3.emoji.gg/emojis/4996-number-one.png"
              style=" position: relative; top: 20px; left: -11px; width:1.2rem; height:1.2rem; margin-bottom: 5px;" />



            <a class="nav-link " href="./unInvoiced.php">


              <span style="position: relative; top: 0; left: -0.9rem;" class="fw-bolder text-light "> Uninvoiced
                Projects</span>

            </a>
          </div>

        </li>
        <li class="ps-4 nav-item" style="margin-top: 3rem;" id="uncollapsed">
          <div class=""
            style="position: absolute; border-left: 1px dotted white; display: flex; margin-left: 0.1rem;  width: 197px;">
            <!-- <div style="position: relative; top: 20px; left: -11px; width: 1.6rem; height: 1.4rem; border-radius: 50%; background: #e74a3b; padding:  0rem  0.4rem;" > 2 </div> -->
            <img src="https://www.actratoronto.com/wp-content/uploads/2020/09/512px-Icon_2_red.svg.png"
              style=" position: relative; top: 20px; left: -11px; width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

            <a class="nav-link " href="./readyToBeInvoiced.php">


              <span style="position: relative; top: 0; left: -0.9rem;" class="fw-bolder text-light;">Ready to Invoice</span>

            </a>
          </div>

        </li>
        <li class="ps-4 nav-item" style="margin-top: 3rem;" id="uncollapsed">
          <div class=""
            style="position: absolute; border-left: 1px dotted white; display: flex; margin-left: 0.1rem; width: 197px;">

            <!-- <div style="position: relative; top: 20px; left: -11px; width: 1.6rem; height: 1.4rem; border-radius: 50%; background: #1cc88a; padding:  0rem  0.4rem;" > 3 </div> -->
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/Icon_3_green.svg/1024px-Icon_3_green.svg.png"
              style=" position: relative; top: 20px; left: -11px; width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

            <a class="nav-link " href="./invoiced.php">

              <span style="position: relative; top: 0; left:-0.9rem;" class="fw-bolder text-light">Invoiced Projects</span>

            </a>
          </div>

        </li>
        <li class="ps-4 nav-item" style="margin-top: 3rem; margin-bottom: 5rem;" id="uncollapsed">
          <div class=""
            style="position: absolute; border-left: 1px dotted white; display: flex; margin-left: 0.1rem; width: 197px;">
            <!-- <div style="position: relative; top: 20px; left: -11px; width: 1.6rem; height: 1.4rem; border-radius: 50%; background: #dfa934; padding:  0rem  0.4rem;" > 4 </div> -->
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/30/Eo_circle_amber_white_number-4.svg/1024px-Eo_circle_amber_white_number-4.svg.png"
              style="position: relative; top: 20px; left: -11px; width:1.2rem; height:1.2rem; margin-bottom: 5px;" />
            <a class="nav-link " href="./paid_projects.php">
              <span style="position: relative; top: 0; left: -0.9rem;" class="fw-bolder text-light">Paid Projects</span>
            </a>
          </div>

        </li>

      <?php }
    }
    if ($countStatus == 1) {

      ?>

      <li class="nav-item" id="collapsed">

        <a class="nav-link collapsed" id="" href="./unpaid.php">

          <img src="https://cdn3.emoji.gg/emojis/4996-number-one.png"
            style="width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

          <span>Unpaid Invoices</span>

        </a>


      </li>
      <li class="nav-item" id="collapsed">

        <a class="nav-link collapsed" href="./readyToPay.php">
          <img src="https://www.actratoronto.com/wp-content/uploads/2020/09/512px-Icon_2_red.svg.png"
            style="width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

          <span>Ready to Pay</span>

        </a>


      </li>
      <li class="nav-item" id="collapsed">

        <a class="nav-link collapsed" href="./paidinvoices.php">

          <img
            src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/Icon_3_green.svg/1024px-Icon_3_green.svg.png"
            style="width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

          <span>Paid Invoices</span>

        </a>


      </li>


      <li class="nav-item " style="margin-top: 2rem;" id="uncollapsed">
        <div class=""
          style="position: absolute; border-left: 1px dotted white; display: flex; margin-left: 1.6rem; width: 197px;">
          <!-- <div style="position: relative; top: 20px; left: -11px; width: 1.6rem; height: 1.4rem; border-radius: 50%; background: #4e73df; padding:  0rem  0.4rem;" > 1 </div> -->
          <img src="https://cdn3.emoji.gg/emojis/4996-number-one.png"
            style=" position: relative; top: 20px; left: -11px; width:1.2rem; height:1.2rem; margin-bottom: 5px;" />



          <a class="nav-link " href="./unpaid.php">


            <span style="position: relative; top: 0; left: -0.9rem;" class="fw-bolder text-light ">Unpaid Invoices</span>

          </a>
        </div>

      </li>
      <li class="ps-4 nav-item" style="margin-top: 3rem;" id="uncollapsed">
        <div class=""
          style="position: absolute; border-left: 1px dotted white; display: flex; margin-left: 0.1rem;  width: 197px;">
          <!-- <div style="position: relative; top: 20px; left: -11px; width: 1.6rem; height: 1.4rem; border-radius: 50%; background: #e74a3b; padding:  0rem  0.4rem;" > 2 </div> -->
          <img src="https://www.actratoronto.com/wp-content/uploads/2020/09/512px-Icon_2_red.svg.png"
            style=" position: relative; top: 20px; left: -11px; width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

          <a class="nav-link " href="./readyToPay.php">


            <span style="position: relative; top: 0; left: -0.9rem;" class="fw-bolder text-light;">Ready to Pay</span>

          </a>
        </div>

      </li>
      <li class="ps-4 nav-item" style="margin-top: 3rem; margin-bottom: 5rem;" id="uncollapsed">
        <div class=""
          style="position: absolute; border-left: 1px dotted white; display: flex; margin-left: 0.1rem; width: 197px;">

          <!-- <div style="position: relative; top: 20px; left: -11px; width: 1.6rem; height: 1.4rem; border-radius: 50%; background: #1cc88a; padding:  0rem  0.4rem;" > 3 </div> -->
          <img
            src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ac/Icon_3_green.svg/1024px-Icon_3_green.svg.png"
            style=" position: relative; top: 20px; left: -11px; width:1.2rem; height:1.2rem; margin-bottom: 5px;" />

          <a class="nav-link " href="./paidinvoices.php">

            <span style="position: relative; top: 0; left:-0.9rem;" class="fw-bolder text-light">Paid Invoices</span>

          </a>
        </div>

      </li>



      <?php

    }
    if ($_SESSION['user_role'] == 4) { ?>
      <li class="nav-item">
        <a class="nav-link collapsed" href="../allApps.php">
          <i class="fa-solid fa-house-chimney"></i>
          <span>Home</span>
        </a>


      </li>
      <li class="nav-item">

        <a class="nav-link collapsed" href="./payslip.php">

          <i class="fas fa-fw fa-address-card " style="color: #A8B2FC;"></i>

          <span>Pay Slip</span>

        </a>


      </li>
      <li class="nav-item">

        <a class="nav-link collapsed" href="./payslip_records.php">

          <i class="fas fa-fw fa-file-pdf " style="color: #FB5C5E;"></i>

          <span>Pay-Slip Records</span>

        </a>


      </li>
      <li class="nav-item">

        <a class="nav-link collapsed" href="./employees.php">
          <i class="fas fa-fw fa-address-card " style="color: #DAA520;"></i>
          <span>Employees</span>
        </a>


      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="./timesheet-payslip.php">
          <i class="fas fa-calendar-times" style="color: green;"></i>
          <span>Employees TimeSheet</span>
        </a>


      </li>

      <li class="nav-item">
        <a class="nav-link collapsed" href="./email_queue.php">
          <i class="fas fa-envelope" style="color: #d93025;"></i>
          <span>Email Status</span>
        </a>


      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="./reimbursement.php">
          <i class="fa-solid fa-receipt" style="color: yellow;"></i>
          <span>Reimbursement</span>
        </a>


      </li>
      <li class="nav-item">
        <a class="nav-link collapsed" href="./custom_payslip.php">
          <i class="fas fa-file-invoice" style="color: #007bff;"></i> <!-- Font Awesome icon -->
          <span>Custom Pay Slip</span>
        </a>
      </li>











      <?php



    }



    ?>



    <!-- Nav Item - Utilities Collapse Menu -->


    <script>
      document.addEventListener('DOMContentLoaded', () => {

        document.querySelectorAll("#uncollapsed").forEach(el => {
          el.style.display = "block"
        })
        document.querySelectorAll("#collapsed").forEach(el => {
          el.style.display = "none"
        })

        let collapsed = false



        let myButton = document.getElementById("sidebarToggle")
        myButton.addEventListener('click', () => {
          collapsed = !collapsed
          if (collapsed === true) {


            document.querySelectorAll("#uncollapsed").forEach(el => {
              el.style.display = "none"
            })


            document.querySelectorAll("#collapsed").forEach(el => {
              el.style.display = "block"
            })
          } else {
            document.querySelectorAll("#uncollapsed").forEach(el => {
              el.style.display = "block"
            })
            document.querySelectorAll("#collapsed").forEach(el => {
              el.style.display = "none"
            })
          }



        })











      })
    </script>


    <!-- Divider -->



    <!-- Divider -->

    <hr class="sidebar-divider d-none d-md-block">



    <!-- Sidebar Toggler (Sidebar) -->

    <div class="text-center d-none d-md-inline">

      <button class="rounded-circle border-0" id="sidebarToggle"></button>

    </div>

    <div class="d-flex mt-5 justify-content-center align-items-center  ">
      <style>
        /* REMASTERED */
        /* RTX-ON */
        /* completely redone toggle and droid */






        .bb8-toggle {
          --toggle-size: 12px;
          /* Adjust font size for smaller screens */
          --toggle-width: 8em;
          /* Adjust width */
          --toggle-height: 2.5em;

          --toggle-offset: calc((var(--toggle-height) - var(--bb8-diameter)) / 2);
          --toggle-bg: linear-gradient(#2c4770, #070e2b 35%, #628cac 50% 70%, #a6c5d4) no-repeat;
          --bb8-diameter: 4.375em;
          --radius: 99em;
          --transition: 0.4s;
          --accent: #de7d2f;
          --bb8-bg: #fff;
        }


        @media (min-width: 768px) {
          .bb8-toggle {
            --toggle-size: 12px;
            /* Adjust font size for smaller screens */
            --toggle-width: 8em;
            /* Adjust width */
            --toggle-height: 2.5em;
            ;

          }
        }

        /* Media query for smaller screens */
        @media (max-width: 768px) {
          .bb8-toggle {
            --toggle-size: 12px;
            /* Adjust font size for smaller screens */
            --toggle-width: 8em;
            /* Adjust width */
            --toggle-height: 2.5em;
            /* Adjust height */
            /* Adjust other styles as needed */
          }
        }

        /* Media query for even smaller screens */
        @media (max-width: 480px) {
          .bb8-toggle {
            --toggle-size: 10px;
            /* Further reduce font size */
            --toggle-width: 6em;
            /* Further adjust width */
            --toggle-height: 2em;
            /* Further adjust height */
            /* Additional adjustments */
          }
        }


        .bb8-toggle,
        .bb8-toggle *,
        .bb8-toggle *::before,
        .bb8-toggle *::after {
          -webkit-box-sizing: border-box;
          box-sizing: border-box;
        }

        .bb8-toggle {
          cursor: pointer;
          margin-top: var(--margin-top-for-head);
          font-size: var(--toggle-size);
        }

        .bb8-toggle__checkbox {
          -webkit-appearance: none;
          -moz-appearance: none;
          appearance: none;
          display: none;
        }

        .bb8-toggle__container {
          width: var(--toggle-width);
          height: var(--toggle-height);
          background: var(--toggle-bg);
          background-size: 100% 11.25em;
          background-position-y: -5.625em;
          border-radius: var(--radius);
          position: relative;
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
        }

        .bb8 {
          display: -webkit-box;
          display: -ms-flexbox;
          display: flex;
          -webkit-box-orient: vertical;
          -webkit-box-direction: normal;
          -ms-flex-direction: column;
          flex-direction: column;
          -webkit-box-align: center;
          -ms-flex-align: center;
          align-items: center;
          position: absolute;
          top: calc(var(--toggle-offset) - 1.688em + 0.188em);
          left: var(--toggle-offset);
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
          z-index: 2;
        }

        .bb8__head-container {
          position: relative;
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
          z-index: 2;
          -webkit-transform-origin: 1.25em 3.75em;
          -ms-transform-origin: 1.25em 3.75em;
          transform-origin: 1.25em 3.75em;
        }

        .bb8__head {
          overflow: hidden;
          margin-bottom: -0.188em;
          width: 2.5em;
          height: 1.688em;
          background: -o-linear-gradient(transparent 0.063em,
              dimgray 0.063em 0.313em,
              transparent 0.313em 0.375em,
              var(--accent) 0.375em 0.5em,
              transparent 0.5em 1.313em,
              silver 1.313em 1.438em,
              transparent 1.438em),
            -o-linear-gradient(45deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
            -o-linear-gradient(135deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
            -o-linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
          background: -o-linear-gradient(transparent 0.063em,
              dimgray 0.063em 0.313em,
              transparent 0.313em 0.375em,
              var(--accent) 0.375em 0.5em,
              transparent 0.5em 1.313em,
              silver 1.313em 1.438em,
              transparent 1.438em),
            -o-linear-gradient(45deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
            -o-linear-gradient(135deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
            -o-linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
          background: -o-linear-gradient(transparent 0.063em,
              dimgray 0.063em 0.313em,
              transparent 0.313em 0.375em,
              var(--accent) 0.375em 0.5em,
              transparent 0.5em 1.313em,
              silver 1.313em 1.438em,
              transparent 1.438em),
            -o-linear-gradient(45deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
            -o-linear-gradient(135deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
            -o-linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
          background: -o-linear-gradient(transparent 0.063em,
              dimgray 0.063em 0.313em,
              transparent 0.313em 0.375em,
              var(--accent) 0.375em 0.5em,
              transparent 0.5em 1.313em,
              silver 1.313em 1.438em,
              transparent 1.438em),
            -o-linear-gradient(45deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
            -o-linear-gradient(135deg, transparent 0.188em, var(--bb8-bg) 0.188em 1.25em, transparent 1.25em),
            -o-linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
          background: linear-gradient(transparent 0.063em,
              dimgray 0.063em 0.313em,
              transparent 0.313em 0.375em,
              var(--accent) 0.375em 0.5em,
              transparent 0.5em 1.313em,
              silver 1.313em 1.438em,
              transparent 1.438em),
            linear-gradient(45deg,
              transparent 0.188em,
              var(--bb8-bg) 0.188em 1.25em,
              transparent 1.25em),
            linear-gradient(-45deg,
              transparent 0.188em,
              var(--bb8-bg) 0.188em 1.25em,
              transparent 1.25em),
            linear-gradient(var(--bb8-bg) 1.25em, transparent 1.25em);
          border-radius: var(--radius) var(--radius) 0 0;
          position: relative;
          z-index: 1;
          -webkit-filter: drop-shadow(0 0.063em 0.125em gray);
          filter: drop-shadow(0 0.063em 0.125em gray);
        }

        .bb8__head::before {
          content: "";
          position: absolute;
          width: 0.563em;
          height: 0.563em;
          background: -o-radial-gradient(0.25em 0.375em,
              0.125em circle,
              red,
              transparent),
            -o-radial-gradient(0.375em 0.188em, 0.063em circle, var(--bb8-bg) 50%, transparent 100%),
            -o-linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
          background: -o-radial-gradient(0.25em 0.375em,
              0.125em circle,
              red,
              transparent),
            -o-radial-gradient(0.375em 0.188em, 0.063em circle, var(--bb8-bg) 50%, transparent 100%),
            -o-linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
          background: -o-radial-gradient(0.25em 0.375em,
              0.125em circle,
              red,
              transparent),
            -o-radial-gradient(0.375em 0.188em, 0.063em circle, var(--bb8-bg) 50%, transparent 100%),
            -o-linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
          background: -o-radial-gradient(0.25em 0.375em,
              0.125em circle,
              red,
              transparent),
            -o-radial-gradient(0.375em 0.188em, 0.063em circle, var(--bb8-bg) 50%, transparent 100%),
            -o-linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
          background: radial-gradient(0.125em circle at 0.25em 0.375em,
              red,
              transparent),
            radial-gradient(0.063em circle at 0.375em 0.188em,
              var(--bb8-bg) 50%,
              transparent 100%),
            linear-gradient(45deg, #000 0.188em, dimgray 0.313em 0.375em, #000 0.5em);
          border-radius: var(--radius);
          top: 0.413em;
          left: 50%;
          -webkit-transform: translate(-50%);
          -ms-transform: translate(-50%);
          transform: translate(-50%);
          -webkit-box-shadow: 0 0 0 0.089em lightgray, 0.563em 0.281em 0 -0.148em,
            0.563em 0.281em 0 -0.1em var(--bb8-bg), 0.563em 0.281em 0 -0.063em;
          box-shadow: 0 0 0 0.089em lightgray, 0.563em 0.281em 0 -0.148em,
            0.563em 0.281em 0 -0.1em var(--bb8-bg), 0.563em 0.281em 0 -0.063em;
          z-index: 1;
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
        }

        .bb8__head::after {
          content: "";
          position: absolute;
          bottom: 0.375em;
          left: 0;
          width: 100%;
          height: 0.188em;
          background: -o-linear-gradient(left,
              var(--accent) 0.125em,
              transparent 0.125em 0.188em,
              var(--accent) 0.188em 0.313em,
              transparent 0.313em 0.375em,
              var(--accent) 0.375em 0.938em,
              transparent 0.938em 1em,
              var(--accent) 1em 1.125em,
              transparent 1.125em 1.875em,
              var(--accent) 1.875em 2em,
              transparent 2em 2.063em,
              var(--accent) 2.063em 2.25em,
              transparent 2.25em 2.313em,
              var(--accent) 2.313em 2.375em,
              transparent 2.375em 2.438em,
              var(--accent) 2.438em);
          background: -webkit-gradient(linear,
              left top,
              right top,
              color-stop(0.125em, var(--accent)),
              color-stop(0.125em, transparent),
              color-stop(0.188em, var(--accent)),
              color-stop(0.313em, transparent),
              color-stop(0.375em, var(--accent)),
              color-stop(0.938em, transparent),
              color-stop(1em, var(--accent)),
              color-stop(1.125em, transparent),
              color-stop(1.875em, var(--accent)),
              color-stop(2em, transparent),
              color-stop(2.063em, var(--accent)),
              color-stop(2.25em, transparent),
              color-stop(2.313em, var(--accent)),
              color-stop(2.375em, transparent),
              color-stop(2.438em, var(--accent)));
          background: linear-gradient(to right,
              var(--accent) 0.125em,
              transparent 0.125em 0.188em,
              var(--accent) 0.188em 0.313em,
              transparent 0.313em 0.375em,
              var(--accent) 0.375em 0.938em,
              transparent 0.938em 1em,
              var(--accent) 1em 1.125em,
              transparent 1.125em 1.875em,
              var(--accent) 1.875em 2em,
              transparent 2em 2.063em,
              var(--accent) 2.063em 2.25em,
              transparent 2.25em 2.313em,
              var(--accent) 2.313em 2.375em,
              transparent 2.375em 2.438em,
              var(--accent) 2.438em);
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
        }

        .bb8__antenna {
          position: absolute;
          -webkit-transform: translateY(-90%);
          -ms-transform: translateY(-90%);
          transform: translateY(-90%);
          width: 0.059em;
          border-radius: var(--radius) var(--radius) 0 0;
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
        }

        .bb8__antenna:nth-child(1) {
          height: 0.938em;
          right: 0.938em;
          background: -o-linear-gradient(#000 0.188em, silver 0.188em);
          background: -webkit-gradient(linear,
              left top,
              left bottom,
              color-stop(0.188em, #000),
              color-stop(0.188em, silver));
          background: linear-gradient(#000 0.188em, silver 0.188em);
        }

        .bb8__antenna:nth-child(2) {
          height: 0.375em;
          left: 50%;
          -webkit-transform: translate(-50%, -90%);
          -ms-transform: translate(-50%, -90%);
          transform: translate(-50%, -90%);
          background: silver;
        }

        .bb8__body {
          width: 4.375em;
          height: 4.375em;
          background: var(--bb8-bg);
          border-radius: var(--radius);
          position: relative;
          overflow: hidden;
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
          z-index: 1;
          -webkit-transform: rotate(45deg);
          -ms-transform: rotate(45deg);
          transform: rotate(45deg);
          background: -webkit-gradient(linear,
              right top,
              left top,
              color-stop(4%, var(--bb8-bg)),
              color-stop(4%, var(--accent)),
              color-stop(10%, transparent),
              color-stop(90%, var(--accent)),
              color-stop(96%, var(--bb8-bg))),
            -webkit-gradient(linear, left top, left bottom, color-stop(4%, var(--bb8-bg)), color-stop(4%, var(--accent)), color-stop(10%, transparent), color-stop(90%, var(--accent)), color-stop(96%, var(--bb8-bg))),
            -webkit-gradient(linear, left top, right top, color-stop(2.156em, transparent), color-stop(2.156em, silver), color-stop(2.188em, transparent)),
            -webkit-gradient(linear, left top, left bottom, color-stop(2.156em, transparent), color-stop(2.156em, silver), color-stop(2.188em, transparent));
          background: -o-linear-gradient(right,
              var(--bb8-bg) 4%,
              var(--accent) 4% 10%,
              transparent 10% 90%,
              var(--accent) 90% 96%,
              var(--bb8-bg) 96%),
            -o-linear-gradient(var(--bb8-bg) 4%, var(--accent) 4% 10%, transparent 10% 90%, var(--accent) 90% 96%, var(--bb8-bg) 96%),
            -o-linear-gradient(left, transparent 2.156em, silver 2.156em 2.219em, transparent 2.188em),
            -o-linear-gradient(transparent 2.156em, silver 2.156em 2.219em, transparent 2.188em);
          background: linear-gradient(-90deg,
              var(--bb8-bg) 4%,
              var(--accent) 4% 10%,
              transparent 10% 90%,
              var(--accent) 90% 96%,
              var(--bb8-bg) 96%),
            linear-gradient(var(--bb8-bg) 4%,
              var(--accent) 4% 10%,
              transparent 10% 90%,
              var(--accent) 90% 96%,
              var(--bb8-bg) 96%),
            linear-gradient(to right,
              transparent 2.156em,
              silver 2.156em 2.219em,
              transparent 2.188em),
            linear-gradient(transparent 2.156em,
              silver 2.156em 2.219em,
              transparent 2.188em);
          background-color: var(--bb8-bg);
        }

        .bb8__body::after {
          content: "";
          bottom: 1.5em;
          left: 0.563em;
          position: absolute;
          width: 0.188em;
          height: 0.188em;
          background: rgb(236, 236, 236);
          color: rgb(236, 236, 236);
          border-radius: 50%;
          -webkit-box-shadow: 0.875em 0.938em, 0 -1.25em, 0.875em -2.125em,
            2.125em -2.125em, 3.063em -1.25em, 3.063em 0, 2.125em 0.938em;
          box-shadow: 0.875em 0.938em, 0 -1.25em, 0.875em -2.125em, 2.125em -2.125em,
            3.063em -1.25em, 3.063em 0, 2.125em 0.938em;
        }

        .bb8__body::before {
          content: "";
          width: 2.625em;
          height: 2.625em;
          position: absolute;
          border-radius: 50%;
          z-index: 0.1;
          overflow: hidden;
          top: 50%;
          left: 50%;
          -webkit-transform: translate(-50%, -50%);
          -ms-transform: translate(-50%, -50%);
          transform: translate(-50%, -50%);
          border: 0.313em solid var(--accent);
          background: -o-radial-gradient(center,
              1em circle,
              rgb(236, 236, 236) 50%,
              transparent 51%),
            -o-radial-gradient(center, 1.25em circle, var(--bb8-bg) 50%, transparent 51%),
            -o-linear-gradient(right, transparent 42%, var(--accent) 42% 58%, transparent 58%),
            -o-linear-gradient(var(--bb8-bg) 42%, var(--accent) 42% 58%, var(--bb8-bg) 58%);
          background: -o-radial-gradient(center,
              1em circle,
              rgb(236, 236, 236) 50%,
              transparent 51%),
            -o-radial-gradient(center, 1.25em circle, var(--bb8-bg) 50%, transparent 51%),
            -o-linear-gradient(right, transparent 42%, var(--accent) 42% 58%, transparent 58%),
            -o-linear-gradient(var(--bb8-bg) 42%, var(--accent) 42% 58%, var(--bb8-bg) 58%);
          background: radial-gradient(1em circle at center,
              rgb(236, 236, 236) 50%,
              transparent 51%),
            radial-gradient(1.25em circle at center, var(--bb8-bg) 50%, transparent 51%),
            -webkit-gradient(linear, right top, left top, color-stop(42%, transparent), color-stop(42%, var(--accent)), color-stop(58%, transparent)),
            -webkit-gradient(linear, left top, left bottom, color-stop(42%, var(--bb8-bg)), color-stop(42%, var(--accent)), color-stop(58%, var(--bb8-bg)));
          background: radial-gradient(1em circle at center,
              rgb(236, 236, 236) 50%,
              transparent 51%),
            radial-gradient(1.25em circle at center, var(--bb8-bg) 50%, transparent 51%),
            linear-gradient(-90deg,
              transparent 42%,
              var(--accent) 42% 58%,
              transparent 58%),
            linear-gradient(var(--bb8-bg) 42%, var(--accent) 42% 58%, var(--bb8-bg) 58%);
        }

        .artificial__hidden {
          position: absolute;
          border-radius: inherit;
          inset: 0;
          pointer-events: none;
          overflow: hidden;
        }

        .bb8__shadow {
          content: "";
          width: var(--bb8-diameter);
          height: 20%;
          border-radius: 50%;
          background: #3a271c;
          -webkit-box-shadow: 0.313em 0 3.125em #3a271c;
          box-shadow: 0.313em 0 3.125em #3a271c;
          opacity: 0.25;
          position: absolute;
          bottom: 0;
          left: calc(var(--toggle-offset) - 0.938em);
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
          -webkit-transform: skew(-70deg);
          -ms-transform: skew(-70deg);
          transform: skew(-70deg);
          z-index: 1;
        }

        .bb8-toggle__scenery {
          width: 100%;
          height: 100%;
          pointer-events: none;
          overflow: hidden;
          position: relative;
          border-radius: inherit;
        }

        .bb8-toggle__scenery::before {
          content: "";
          position: absolute;
          width: 100%;
          height: 30%;
          bottom: 0;
          background: #b18d71;
          z-index: 1;
        }

        .bb8-toggle__cloud {
          z-index: 1;
          position: absolute;
          border-radius: 50%;
        }

        .bb8-toggle__cloud:nth-last-child(1) {
          width: 0.875em;
          height: 0.625em;
          -webkit-filter: blur(0.125em) drop-shadow(0.313em 0.313em #ffffffae) drop-shadow(-0.625em 0 #fff) drop-shadow(-0.938em -0.125em #fff);
          filter: blur(0.125em) drop-shadow(0.313em 0.313em #ffffffae) drop-shadow(-0.625em 0 #fff) drop-shadow(-0.938em -0.125em #fff);
          right: 1.875em;
          top: 2.813em;
          background: -o-linear-gradient(bottom left, #ffffffae, #ffffffae);
          background: -webkit-gradient(linear,
              left bottom,
              right top,
              from(#ffffffae),
              to(#ffffffae));
          background: linear-gradient(to top right, #ffffffae, #ffffffae);
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
        }

        .bb8-toggle__cloud:nth-last-child(2) {
          top: 0.625em;
          right: 4.375em;
          width: 0.875em;
          height: 0.375em;
          background: #dfdedeae;
          -webkit-filter: blur(0.125em) drop-shadow(-0.313em -0.188em #e0dfdfae) drop-shadow(-0.625em -0.188em #bbbbbbae) drop-shadow(-1em 0.063em #cfcfcfae);
          filter: blur(0.125em) drop-shadow(-0.313em -0.188em #e0dfdfae) drop-shadow(-0.625em -0.188em #bbbbbbae) drop-shadow(-1em 0.063em #cfcfcfae);
          -webkit-transition: 0.6s;
          -o-transition: 0.6s;
          transition: 0.6s;
        }

        .bb8-toggle__cloud:nth-last-child(3) {
          top: 1.25em;
          right: 0.938em;
          width: 0.875em;
          height: 0.375em;
          background: #ffffffae;
          -webkit-filter: blur(0.125em) drop-shadow(0.438em 0.188em #ffffffae) drop-shadow(-0.625em 0.313em #ffffffae);
          filter: blur(0.125em) drop-shadow(0.438em 0.188em #ffffffae) drop-shadow(-0.625em 0.313em #ffffffae);
          -webkit-transition: 0.8s;
          -o-transition: 0.8s;
          transition: 0.8s;
        }

        .gomrassen,
        .hermes,
        .chenini {
          position: absolute;
          border-radius: var(--radius);
          background: -o-linear-gradient(#fff, #6e8ea2);
          background: -webkit-gradient(linear,
              left top,
              left bottom,
              from(#fff),
              to(#6e8ea2));
          background: linear-gradient(#fff, #6e8ea2);
          top: 100%;
        }

        .gomrassen {
          left: 0.938em;
          width: 1.875em;
          height: 1.875em;
          -webkit-box-shadow: 0 0 0.188em #ffffff52, 0 0 0.188em #6e8ea24b;
          box-shadow: 0 0 0.188em #ffffff52, 0 0 0.188em #6e8ea24b;
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
        }

        .gomrassen::before,
        .gomrassen::after {
          content: "";
          position: absolute;
          border-radius: inherit;
          -webkit-box-shadow: inset 0 0 0.063em rgb(140, 162, 169);
          box-shadow: inset 0 0 0.063em rgb(140, 162, 169);
          background: rgb(184, 196, 200);
        }

        .gomrassen::before {
          left: 0.313em;
          top: 0.313em;
          width: 0.438em;
          height: 0.438em;
        }

        .gomrassen::after {
          width: 0.25em;
          height: 0.25em;
          left: 1.25em;
          top: 0.75em;
        }

        .hermes {
          left: 3.438em;
          width: 0.625em;
          height: 0.625em;
          -webkit-box-shadow: 0 0 0.125em #ffffff52, 0 0 0.125em #6e8ea24b;
          box-shadow: 0 0 0.125em #ffffff52, 0 0 0.125em #6e8ea24b;
          -webkit-transition: 0.6s;
          -o-transition: 0.6s;
          transition: 0.6s;
        }

        .chenini {
          left: 4.375em;
          width: 0.5em;
          height: 0.5em;
          -webkit-box-shadow: 0 0 0.125em #ffffff52, 0 0 0.125em #6e8ea24b;
          box-shadow: 0 0 0.125em #ffffff52, 0 0 0.125em #6e8ea24b;
          -webkit-transition: 0.8s;
          -o-transition: 0.8s;
          transition: 0.8s;
        }

        .tatto-1,
        .tatto-2 {
          position: absolute;
          width: 1.25em;
          height: 1.25em;
          border-radius: var(--radius);
        }

        .tatto-1 {
          background: #fefefe;
          right: 3.125em;
          top: 0.625em;
          -webkit-box-shadow: 0 0 0.438em #fdf4e1;
          box-shadow: 0 0 0.438em #fdf4e1;
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
        }

        .tatto-2 {
          background: -o-linear-gradient(#e6ac5c, #d75449);
          background: -webkit-gradient(linear,
              left top,
              left bottom,
              from(#e6ac5c),
              to(#d75449));
          background: linear-gradient(#e6ac5c, #d75449);
          right: 1.25em;
          top: 2.188em;
          -webkit-box-shadow: 0 0 0.438em #e6ad5c3d, 0 0 0.438em #d755494f;
          box-shadow: 0 0 0.438em #e6ad5c3d, 0 0 0.438em #d755494f;
          -webkit-transition: 0.7s;
          -o-transition: 0.7s;
          transition: 0.7s;
        }

        .bb8-toggle__star {
          position: absolute;
          width: 0.063em;
          height: 0.063em;
          background: #fff;
          border-radius: var(--radius);
          -webkit-filter: drop-shadow(0 0 0.063em #fff);
          filter: drop-shadow(0 0 0.063em #fff);
          color: #fff;
          top: 100%;
        }

        .bb8-toggle__star:nth-child(1) {
          left: 3.75em;
          -webkit-box-shadow: 1.25em 0.938em, -1.25em 2.5em, 0 1.25em, 1.875em 0.625em,
            -3.125em 1.875em, 1.25em 2.813em;
          box-shadow: 1.25em 0.938em, -1.25em 2.5em, 0 1.25em, 1.875em 0.625em,
            -3.125em 1.875em, 1.25em 2.813em;
          -webkit-transition: 0.2s;
          -o-transition: 0.2s;
          transition: 0.2s;
        }

        .bb8-toggle__star:nth-child(2) {
          left: 4.688em;
          -webkit-box-shadow: 0.625em 0, 0 0.625em, -0.625em -0.625em, 0.625em 0.938em,
            -3.125em 1.25em, 1.25em -1.563em;
          box-shadow: 0.625em 0, 0 0.625em, -0.625em -0.625em, 0.625em 0.938em,
            -3.125em 1.25em, 1.25em -1.563em;
          -webkit-transition: 0.3s;
          -o-transition: 0.3s;
          transition: 0.3s;
        }

        .bb8-toggle__star:nth-child(3) {
          left: 5.313em;
          -webkit-box-shadow: -0.625em -0.625em, -2.188em 1.25em, -2.188em 0,
            -3.75em -0.625em, -3.125em -0.625em, -2.5em -0.313em, 0.75em -0.625em;
          box-shadow: -0.625em -0.625em, -2.188em 1.25em, -2.188em 0, -3.75em -0.625em,
            -3.125em -0.625em, -2.5em -0.313em, 0.75em -0.625em;
          -webkit-transition: var(--transition);
          -o-transition: var(--transition);
          transition: var(--transition);
        }

        .bb8-toggle__star:nth-child(4) {
          left: 1.875em;
          width: 0.125em;
          height: 0.125em;
          -webkit-transition: 0.5s;
          -o-transition: 0.5s;
          transition: 0.5s;
        }

        .bb8-toggle__star:nth-child(5) {
          left: 5em;
          width: 0.125em;
          height: 0.125em;
          -webkit-transition: 0.6s;
          -o-transition: 0.6s;
          transition: 0.6s;
        }

        .bb8-toggle__star:nth-child(6) {
          left: 2.5em;
          width: 0.125em;
          height: 0.125em;
          -webkit-transition: 0.7s;
          -o-transition: 0.7s;
          transition: 0.7s;
        }

        .bb8-toggle__star:nth-child(7) {
          left: 3.438em;
          width: 0.125em;
          height: 0.125em;
          -webkit-transition: 0.8s;
          -o-transition: 0.8s;
          transition: 0.8s;
        }

        /* actions */

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(1) {
          top: 0.625em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(2) {
          top: 1.875em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(3) {
          top: 1.25em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(4) {
          top: 3.438em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(5) {
          top: 3.438em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(6) {
          top: 0.313em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__star:nth-child(7) {
          top: 1.875em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8-toggle__cloud {
          right: -100%;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .gomrassen {
          top: 0.938em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .hermes {
          top: 2.5em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .chenini {
          top: 2.75em;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container {
          background-position-y: 0;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .tatto-1 {
          top: 100%;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .tatto-2 {
          top: 100%;
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8 {
          left: calc(100% - var(--bb8-diameter) - var(--toggle-offset));
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8__shadow {
          left: calc(100% - var(--bb8-diameter) - var(--toggle-offset) + 0.938em);
          -webkit-transform: skew(70deg);
          -ms-transform: skew(70deg);
          transform: skew(70deg);
        }

        .bb8-toggle__checkbox:checked+.bb8-toggle__container .bb8__body {
          -webkit-transform: rotate(180deg);
          -ms-transform: rotate(180deg);
          transform: rotate(225deg);
        }

        .bb8-toggle__checkbox:hover+.bb8-toggle__container .bb8__head::before {
          left: 100%;
        }

        .bb8-toggle__checkbox:not(:checked):hover+.bb8-toggle__container .bb8__antenna:nth-child(1) {
          right: 1.5em;
        }

        .bb8-toggle__checkbox:hover+.bb8-toggle__container .bb8__antenna:nth-child(2) {
          left: 0.938em;
        }

        .bb8-toggle__checkbox:hover+.bb8-toggle__container .bb8__head::after {
          background-position: 1.375em 0;
        }

        .bb8-toggle__checkbox:checked:hover+.bb8-toggle__container .bb8__head::before {
          left: 0;
        }

        .bb8-toggle__checkbox:checked:hover+.bb8-toggle__container .bb8__antenna:nth-child(2) {
          left: calc(100% - 0.938em);
        }

        .bb8-toggle__checkbox:checked:hover+.bb8-toggle__container .bb8__head::after {
          background-position: -1.375em 0;
        }

        .bb8-toggle__checkbox:active+.bb8-toggle__container .bb8__head-container {
          -webkit-transform: rotate(25deg);
          -ms-transform: rotate(25deg);
          transform: rotate(25deg);
        }

        .bb8-toggle__checkbox:checked:active+.bb8-toggle__container .bb8__head-container {
          -webkit-transform: rotate(-25deg);
          -ms-transform: rotate(-25deg);
          transform: rotate(-25deg);
        }

        .bb8:hover .bb8__head::before,
        .bb8:hover .bb8__antenna:nth-child(2) {
          left: 50% !important;
        }

        .bb8:hover .bb8__antenna:nth-child(1) {
          right: 0.938em !important;
        }

        .bb8:hover .bb8__head::after {
          background-position: 0 0 !important;
        }
      </style>
      <label class="bb8-toggle  " id="themeSwitchBtn">

        <input class="bb8-toggle__checkbox" id="themeState" type="checkbox">



        <div class="bb8-toggle__container">
          <div class="bb8-toggle__scenery">
            <div class="bb8-toggle__star"></div>
            <div class="bb8-toggle__star"></div>
            <div class="bb8-toggle__star"></div>
            <div class="bb8-toggle__star"></div>
            <div class="bb8-toggle__star"></div>
            <div class="bb8-toggle__star"></div>
            <div class="bb8-toggle__star"></div>
            <div class="tatto-1"></div>
            <div class="tatto-2"></div>
            <div class="gomrassen"></div>
            <div class="hermes"></div>
            <div class="chenini"></div>
            <div class="bb8-toggle__cloud"></div>
            <div class="bb8-toggle__cloud"></div>
            <div class="bb8-toggle__cloud"></div>
          </div>
          <div class="bb8">
            <div class="bb8__head-container">
              <div class="bb8__antenna"></div>
              <div class="bb8__antenna"></div>
              <div class="bb8__head"></div>
            </div>
            <div class="bb8__body"></div>
          </div>
          <div class="artificial__hidden">
            <div class="bb8__shadow"></div>
          </div>
        </div>
      </label>


      <script>
        const themeStateBtn = document.getElementById('themeState');

        // Check the stored theme preference on page load
        const storedTheme = sessionStorage.getItem('theme') ? sessionStorage.getItem('theme') : 'light-mode';
        if (storedTheme === 'light-mode') {
          document.body.classList.remove('dark-mode');
          document.body.classList.add('light-mode');
        } else {
          document.body.classList.add('dark-mode');
          document.body.classList.remove('light-mode');
          themeStateBtn.checked = true;
        }

        // Add event listener for theme toggle
        themeStateBtn.addEventListener('change', (e) => {
          if (e.target.checked) {
            // Apply dark theme
            sessionStorage.setItem('theme', 'dark-mode');
          } else {
            // Apply light theme
            sessionStorage.setItem('theme', 'light-mode');
          }

          // Reload the page to apply the theme
          window.location.reload();
        });
      </script>

    </div>



  </ul>

  <!-- End of Sidebar -->



  <!-- Content Wrapper -->

  <div id="content-wrapper" class="d-flex flex-column">



    <!-- Main Content -->



    <!-- Topbar -->

    <nav class="navbar navbar-expand navbar-light  topbar mb-4 static-top shadow" style="background-color: #070A19;">

      <ul class="navbar-nav ml-auto">

        <!-- Sidebar Toggle (Topbar) -->
        <form class="form-inline">
          <a id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" name="sidebarToggleTop">
            <i class="fa fa-bars"></i>
          </a>

        </form>

        <!-- Topbar Navbar -->
        <ul class="navbar-nav ml-auto">
          <!-- Nav Item - Notifications -->





 <?php


        // Get admin ID from session
        $user_id = $_SESSION['admin_id'];

        // Fetch admin info from database
        $query = "SELECT fullname, profile_pic FROM tbl_admin WHERE user_id = '$user_id'";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);

        // Fallback if no image
        $profile_pic = !empty($user['profile_pic']) ?  $user['profile_pic'] : 'img/user.png';
        $user_name = htmlspecialchars($user['fullname']);
        ?>

          <div class="topbar-divider d-none d-sm-block"></div>


          <!-- Nav Item - User Information -->

          <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
              aria-haspopup="true" aria-expanded="false">
              <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $user_name; ?></span>
                <img class="img-profile rounded-circle" src="<?php echo htmlspecialchars($profile_pic); ?>">
            </a>

            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

              <a class="dropdown-item" href="#">
                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                Profile
              </a>

              <a class="dropdown-item" href="#">
                <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                Settings
              </a>
              <a class="dropdown-item" href="time-sheet.php">
                <i class="fas fa-list fa-sm fa-fw mr-2 text-green"></i>
                Time sheet
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i>
                Logout
              </a>


            </div>
          </li>



        </ul>

    </nav>