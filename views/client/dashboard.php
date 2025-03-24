<?php
require_once '../../config/config.php';

// Check if user is logged in and is client
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
  header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit();
}
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM servers WHERE client_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_due = 0;
$active_services = count($servers);
$today = new DateTime('2025-03-21'); // Hardcoded for testing; use new DateTime() in production
foreach ($servers as $server) {
    $due_date = new DateTime($server['next_due_date']);
    if ($today >= $due_date) {
        $total_due += $server['monthly_amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  :root {
    --primary-color: #170F00;
    --secondary-color: #F45F1E;
    --sidebar-width: 250px;
  }

  body {
    background-color: #f8f9fa;
  }

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

  .user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .profile-image {
    width: 40px;
    height: 40px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }

  .stats-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    height: 100%;
  }

  .stats-card h3 {
    color: var(--primary-color);
    margin-bottom: 10px;
  }

  .stats-card p {
    font-size: 2rem;
    font-weight: bold;
    color: var(--secondary-color);
    margin: 0;
  }

  .payment-history {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
  }

  .status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
  }

  .status-paid {
    background: #d4edda;
    color: #155724;
  }

  .status-pending {
    background: #fff3cd;
    color: #856404;
  }
  </style>
</head>

<body>
  <div class="sidebar">
    <div class="sidebar-brand">
      Client Panel
    </div>
    <div class="sidebar-menu">
      <a href="#" class="menu-item active">
        <i class="fas fa-dashboard"></i> Dashboard
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-file-invoice"></i> Invoices
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-credit-card"></i> Payments
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-server"></i> Services
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-ticket"></i> Support
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-user"></i> Profile
      </a>
      <a href="../../controllers/AuthController.php?action=logout" class="menu-item">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>

  <div class="main-content">
    <div class="top-bar">
      <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">My Dashboard</h4>
        <div class="user-profile">
          <span>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
          <div class="profile-image"><i class="fas fa-user"></i></div>
        </div>
      </div>
    </div>

    <?php if (isset($_GET['payment']) && $_GET['payment'] === 'success'): ?>
        <div class="alert alert-success mt-3">Payment successful!</div>
    <?php elseif (isset($_GET['payment']) && $_GET['payment'] === 'cancelled'): ?>
        <div class="alert alert-warning mt-3">Payment was cancelled.</div>
    <?php endif; ?>

    <div class="row g-4 mt-3">
      <div class="col-md-4">
        <div class="stats-card">
          <h3>Active Services</h3>
          <p><?php echo $active_services; ?></p>
          <small class="text-muted">Servers</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <h3>Due Payment</h3>
          <p>$<?php echo number_format($total_due, 2); ?></p>
          <small class="text-muted">Total due now</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <h3>Support Tickets</h3>
          <p>1</p>
          <small class="text-muted">1 Open ticket</small>
        </div>
      </div>
    </div>

    <div class="payment-history mt-4">
      <h4 class="mb-4">Your Servers</h4>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Server Name</th>
              <th>Next Due Date</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($servers as $server): ?>
                <?php
                $due_date = new DateTime($server['next_due_date']);
                $due_date->modify('-1 day'); // Consider due the day before
                $is_due = $today >= $due_date;// Due if today is on or after due date
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($server['server_name']); ?></td>
                    <td><?php echo $server['next_due_date']; ?></td>
                    <td>$<?php echo number_format($server['monthly_amount'], 2); ?></td>
                    <td>
                        <span class="status-badge <?php echo $is_due ? 'status-due' : 'status-paid'; ?>">
                            <?php echo $is_due ? 'Due' : 'Paid'; ?>
                        </span>
                    </td>
                    <td>
                        <form action="/client-payment-system/public/payment_initiate.php" method="GET" class="d-inline">
                            <input type="hidden" name="server_id" value="<?php echo $server['id']; ?>">
                            <select name="months" class="form-select d-inline w-auto">
                                <option value="1">1 Month</option>
                                <option value="2">2 Months</option>
                                <option value="3">3 Months</option>
                                <option value="6">6 Months</option>
                                <option value="12">12 Months</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm"><?php echo $is_due ? 'Pay Now' : 'Pay in Advance'; ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>