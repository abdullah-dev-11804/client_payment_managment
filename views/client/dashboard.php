<?php
require_once '../../config/config.php';

// Check if user is logged in and is client
if (!isset($_SESSION['user_id'])) {
    header('Location: /client-payment-system/views/auth/login.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header('Location: /client-payment-system/views/auth/login.php');
    exit();
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
          <div class="profile-image">
            <i class="fas fa-user"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="stats-card">
          <h3>Active Services</h3>
          <p>2</p>
          <small class="text-muted">Web Hosting, Domain</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stats-card">
          <h3>Due Payment</h3>
          <p>$150</p>
          <small class="text-muted">Next due: 15th Aug 2024</small>
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

    <div class="payment-history">
      <h4 class="mb-4">Recent Payments</h4>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Invoice #</th>
              <th>Date</th>
              <th>Amount</th>
              <th>Service</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>INV-2024-001</td>
              <td>2024-01-15</td>
              <td>$99.00</td>
              <td>Web Hosting (Annual)</td>
              <td><span class="status-badge status-paid">Paid</span></td>
            </tr>
            <tr>
              <td>INV-2024-002</td>
              <td>2024-02-01</td>
              <td>$150.00</td>
              <td>Domain Renewal</td>
              <td><span class="status-badge status-pending">Pending</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>