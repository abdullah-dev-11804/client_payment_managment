<?php
require_once '../../config/config.php';

// For debugging - remove in production
error_log("Session data in dashboard: " . print_r($_SESSION, true));

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: /client-payment-system/views/auth/login.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /client-payment-system/views/auth/login.php');
    exit();
}
// Fetch total clients from the servers table

if (!isset($pdo)) {
  die("Database connection failed!");
}

// Query to count unique clients in the `servers` table
$query = "SELECT COUNT(DISTINCT client_id) AS total FROM servers";
$stmt = $pdo->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$totalClients = $row ? $row['total'] : 0;

echo "Total Clients: " . $totalClients;

// Query to count active servers (join `users` table where `status` is 'active')
$queryActiveServers = "
    SELECT COUNT(s.id) AS active_servers 
    FROM servers s
    JOIN users u ON s.user_id = u.id
    WHERE u.status = 'active'
";
$stmtActive = $pdo->prepare($queryActiveServers);
$stmtActive->execute();
$rowActive = $stmtActive->fetch(PDO::FETCH_ASSOC);
$activeServers = $rowActive ? $rowActive['active_servers'] : 0;

// Query to fetch recent payments
$queryPayments = "
    SELECT p.amount, p.payment_date, p.status, u.name, u.email, s.server_name 
    FROM payments p
    JOIN users u ON p.user_id = u.id
    JOIN servers s ON p.server_id = s.id
    ORDER BY p.payment_date DESC
    LIMIT 5
";
$query = "SELECT * FROM client_payments"; 
$stmt = $pdo->prepare($query);
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the most recent registered client
$queryRecentClient = "SELECT name, email, created_at FROM users ORDER BY created_at DESC LIMIT 1";
$stmtRecentClient = $pdo->prepare($queryRecentClient);
$stmtRecentClient->execute();
$recentClient = $stmtRecentClient->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../../public/css/style.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Custom CSS -->
  <style>
  :root {
    --primary-color: #170F00;
    --secondary-color: #F45F1E;
    --sidebar-width: 250px;
  }

  body {
    background-color: #f8f9fa;
  }

  /* Sidebar Styles */
  .sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background-color: var(--primary-color);
    color: white;
    padding: 20px 0;
    transition: all 0.3s ease;
  }

  .sidebar-brand {
    padding: 20px;
    font-size: 1.5rem;
    font-weight: bold;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .sidebar-menu {
    padding: 20px 0;
  }

  .menu-item {
    display: block;
    padding: 15px 20px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .menu-item:hover,
  .menu-item.active {
    background-color: var(--secondary-color);
    color: white;
    text-decoration: none;
  }

  .menu-item i {
    margin-right: 10px;
    width: 20px;
  }

  .main-content {
    margin-left: var(--sidebar-width);
    padding: 20px;
  }

  .top-bar {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .stat-card {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
  }

  .stat-card:hover {
    transform: translateY(-5px);
  }

  .stat-icon {
    width: 50px;
    height: 50px;
    background-color: rgba(244, 95, 30, 0.1);
    color: var(--secondary-color);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 15px;
  }

  .stat-value {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--primary-color);
  }

  .stat-label {
    color: #666;
    font-size: 0.9rem;
  }

  .user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .profile-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--secondary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .recent-activity {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
  }

  .activity-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
  }

  .activity-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
  }

  .dropdown-item:hover {
    background-color: #F77F2E;
    color: white;
  }

  .bg-soft-primary {
    background-color: rgba(244, 95, 30, 0.1);
    color: var(--secondary-color);
  }

  .table-responsive {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .status-badge {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85rem;
  }

  .chart-container {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
  }

  .billing-cycle {
    margin-top: 10px;
  }

  .billing-cycle .badge {
    font-size: 0.8rem;
    padding: 5px 10px;
    background-color: var(--secondary-color) !important;
  }
  </style>
</head>

<body class="bg-light">
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Top Bar -->
    <div class="top-bar d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Dashboard Overview</h4>
      <div class="user-profile">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
        <div class="profile-image">
          <i class="fas fa-user"></i>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4">
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-value"><?php echo $totalClients; ?></div>
          <div class="stat-label">Total Clients</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="stat-value">$12,450</div>
          <div class="stat-label">Monthly Revenue</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-server"></i>
          </div>
          <div class="stat-value"><?php echo $activeServers; ?></div>
          <div class="stat-label">Active Servers</div>
        </div>
      </div>
    </div>

    <div class="table-responsive mt-4">
      <h5 class="mb-4">Recent Payments</h5>
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Server</th>
            <th>Amount</th>
            <th>Previous Payment</th>
            <th>Next Payment</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
            require_once '../../config/config.php'; // Ensure DB connection

            $query = "SELECT 
                u.name AS client_name,
                u.email AS client_email,
                hp.name AS server_name,
                cp.amount,
                cp.previous_payment_date,
                cp.next_payment_date,
                cp.status
            FROM client_payments cp
            JOIN users u ON cp.client_id = u.id
            JOIN hosting_plans hp ON cp.plan_id = hp.id
            ORDER BY cp.next_payment_date DESC
            LIMIT 10";

            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($payments)) {
                foreach ($payments as $payment) {
                    $statusClass = ($payment['status'] == 'paid') ? 'success' : (($payment['status'] == 'overdue') ? 'danger' : 'warning');

                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($payment['client_name'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($payment['client_email'] ?? 'N/A') . '</td>';
                    echo '<td>' . htmlspecialchars($payment['server_name'] ?? 'N/A') . '</td>';
                    echo '<td>$' . number_format($payment['amount'], 2) . '</td>';
                    echo '<td>' . (!empty($payment['previous_payment_date']) ? date('Y-m-d', strtotime($payment['previous_payment_date'])) : 'N/A') . '</td>';
                    echo '<td>' . (!empty($payment['next_payment_date']) ? date('Y-m-d', strtotime($payment['next_payment_date'])) : 'N/A') . '</td>';
                    echo '<td><span class="badge bg-' . $statusClass . ' status-badge">' . ucfirst($payment['status']) . '</span></td>';
                    echo '<td><button class="btn btn-sm btn-outline-primary">View</button></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="8" class="text-center">No recent payments found.</td></tr>';
            }
            ?>
        </tbody>
      </table>
    </div>

    <!-- Export Dropdown -->
    <div class="dropdown mb-3 d-flex justify-content-center">
      <button class="btn btn-success dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown"
        aria-expanded="false">
        Export To
      </button>
      <ul class="dropdown-menu" aria-labelledby="exportDropdown">
        <li><a class="dropdown-item" href="../../controllers/export_pdf.php">Export to PDF</a></li>
        <li><a class="dropdown-item" href="../../controllers/export.php">Export to Excel</a></li>
      </ul>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity">
      <h5 class="mb-4">Recent Activity</h5>
      <div class="activity-item">
        <div class="activity-icon bg-soft-primary">
          <i class="fas fa-user-plus"></i>
        </div>
        <?php if ($recentClient): ?>
        <div>
          <h6 class="mb-1">New client registered: <?php echo htmlspecialchars($recentClient['name']); ?></h6>
          <small class="text-muted"><?php echo date('F j, Y, g:i a', strtotime($recentClient['created_at'])); ?></small>
        </div>
      </div>
      <?php else: ?>
      <div class="activity-item">
        <div>
          <h6 class="mb-1">No recent client registrations.</h6>
        </div>
      </div>
      <?php endif; ?>
      <div class="activity-item">
        <div class="activity-icon bg-soft-primary">
          <i class="fas fa-credit-card"></i>
        </div>
        <div>
          <h6 class="mb-1">Payment received from John Doe</h6>
          <small class="text-muted">2 hours ago</small>
        </div>
      </div>
      <div class="activity-item">
        <div class="activity-icon bg-soft-primary">
          <i class="fas fa-server"></i>
        </div>
        <div>
          <h6 class="mb-1">Server upgrade completed</h6>
          <small class="text-muted">1 day ago</small>
        </div>
      </div>
    </div>
  </div>

  </div>

  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  // Add this function for billing cycle handling
  function updatePriceLabel() {
    const billingCycle = document.getElementById('billingCycle').value;
    const customBillingDiv = document.getElementById('customBillingDiv');
    const priceLabel = document.querySelector('label[for="price"]');

    if (billingCycle === 'custom') {
      customBillingDiv.style.display = 'block';
    } else {
      customBillingDiv.style.display = 'none';
    }
  }

  // Update the display of plans
  function displayPlan(plan) {
    let billingText;
    switch (plan.billing_cycle) {
      case 'monthly':
        billingText = '/month';
        break;
      case 'yearly':
        billingText = '/year';
        break;
      case 'custom':
        billingText = '/' + plan.custom_billing_details;
        break;
      default:
        billingText = '';
    }

    return `
      <div class="col-md-4">
          <div class="plan-card">
              <span class="status-badge status-${plan.status.toLowerCase()}">
                  ${plan.status.charAt(0).toUpperCase() + plan.status.slice(1)}
              </span>
              <h3>${plan.name}</h3>
              <div class="price">$${parseFloat(plan.price).toFixed(2)}${billingText}</div>
              <ul class="features">
                  <li><i class="fas fa-hdd"></i> ${plan.storage} Storage</li>
                  <li><i class="fas fa-network-wired"></i> ${plan.bandwidth} Bandwidth</li>
                  <li><i class="fas fa-globe"></i> ${plan.domains} Domains</li>
              </ul>
              <p class="text-muted">${plan.description}</p>
              <div class="billing-cycle">
                  <span class="badge bg-info">${plan.billing_cycle}</span>
              </div>
              <div class="d-flex justify-content-end mt-3">
                  <button class="btn btn-sm btn-primary me-2" onclick="editPlan(${plan.id})">
                      <i class="fas fa-edit"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-danger" onclick="deletePlan(${plan.id})">
                      <i class="fas fa-trash"></i> Delete
                  </button>
              </div>
          </div>
      </div>`;
  }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>