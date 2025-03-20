<?php
require_once '../../config/config.php';
// For debugging - remove in production
error_log("Session data in dashboard: " . print_r($_SESSION, true));

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
  header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: ' . SITE_URL . '/views/auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
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
    padding: 20px;
    color: white;
    transition: all 0.3s ease;
    z-index: 1000;
  }

  .sidebar-brand {
    font-size: 1.5rem;
    font-weight: 600;
    padding: 20px 0;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  .sidebar-menu {
    padding: 20px 0;
  }

  .menu-item {
    padding: 12px 20px;
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    border-radius: 5px;
    margin-bottom: 5px;
  }

  .menu-item:hover {
    background-color: var(--secondary-color);
    color: white;
    text-decoration: none;
  }

  .menu-item.active {
    background-color: var(--secondary-color);
  }

  .menu-item i {
    margin-right: 10px;
    width: 20px;
  }

  /* Main Content Styles */
  .main-content {
    margin-left: var(--sidebar-width);
    padding: 20px;
  }

  .top-bar {
    background-color: white;
    padding: 15px 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
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
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-brand">
      Admin Panel
    </div>
    <div class="sidebar-menu">
      <a href="#" class="menu-item active">
        <i class="fas fa-dashboard"></i> Dashboard
      </a>
      <a href="clients.php" class="menu-item">
        <i class="fas fa-users"></i> Clients
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-credit-card"></i> Payments
      </a>
      <a href="hosting-plans.php" class="menu-item">
        <i class="fas fa-server"></i> Hosting Plans
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-cog"></i> Settings
      </a>
      <a href="../../controllers/AuthController.php?action=logout" class="menu-item">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>

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
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-value">150</div>
          <div class="stat-label">Total Clients</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
          <div class="stat-value">$12,450</div>
          <div class="stat-label">Monthly Revenue</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-server"></i>
          </div>
          <div class="stat-value">85</div>
          <div class="stat-label">Active Servers</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card">
          <div class="stat-icon">
            <i class="fas fa-ticket"></i>
          </div>
          <div class="stat-value">24</div>
          <div class="stat-label">Support Tickets</div>
        </div>
      </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="table-responsive mt-4">
      <h5 class="mb-4">Recent Payments</h5>
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Client</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>John Doe</td>
            <td>$299.99</td>
            <td>2024-01-15</td>
            <td><span class="badge bg-success status-badge">Paid</span></td>
            <td>
              <button class="btn btn-sm btn-outline-primary">View</button>
            </td>
          </tr>
          <tr>
            <td>Jane Smith</td>
            <td>$199.99</td>
            <td>2024-01-14</td>
            <td><span class="badge bg-warning status-badge">Pending</span></td>
            <td>
              <button class="btn btn-sm btn-outline-primary">View</button>
            </td>
          </tr>
          <tr>
            <td>Mike Johnson</td>
            <td>$499.99</td>
            <td>2024-01-13</td>
            <td><span class="badge bg-success status-badge">Paid</span></td>
            <td>
              <button class="btn btn-sm btn-outline-primary">View</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity">
      <h5 class="mb-4">Recent Activity</h5>
      <div class="activity-item">
        <div class="activity-icon bg-soft-primary">
          <i class="fas fa-user-plus"></i>
        </div>
        <div>
          <h6 class="mb-1">New client registered</h6>
          <small class="text-muted">5 minutes ago</small>
        </div>
      </div>
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
</body>

</html>