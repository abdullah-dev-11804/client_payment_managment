<?php
require_once '../../config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hosting Plans Management</title>
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

  .plans-table {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .plan-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
  }

  .plan-card .price {
    font-size: 24px;
    color: var(--secondary-color);
    font-weight: bold;
  }

  .plan-card .features {
    list-style: none;
    padding: 0;
    margin: 15px 0;
  }

  .plan-card .features li {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
  }

  .plan-card .features li i {
    color: var(--secondary-color);
    margin-right: 10px;
  }

  .status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
  }

  .status-active {
    background-color: #d4edda;
    color: #155724;
  }

  .status-inactive {
    background-color: #f8d7da;
    color: #721c24;
  }

  .modal-header {
    background-color: var(--primary-color);
    color: white;
  }

  .btn-primary {
    background-color: var(--secondary-color);
    border-color: var(--secondary-color);
  }

  .btn-primary:hover {
    background-color: #d45419;
    border-color: #d45419;
  }
  </style>
</head>

<body>
  <div class="sidebar">
    <div class="sidebar-brand">
      Admin Panel
    </div>
    <div class="sidebar-menu">
      <a href="dashboard.php" class="menu-item">
        <i class="fas fa-dashboard"></i> Dashboard
      </a>
      <a href="clients.php" class="menu-item">
        <i class="fas fa-users"></i> Clients
      </a>
      <a href="hosting-plans.php" class="menu-item active">
        <i class="fas fa-server"></i> Hosting Plans
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-credit-card"></i> Payments
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-cog"></i> Settings
      </a>
      <a href="../../controllers/AuthController.php?action=logout" class="menu-item">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>

  <div class="main-content">
    <div class="top-bar">
      <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Hosting Plans</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
          <i class="fas fa-plus"></i> Add New Plan
        </button>
      </div>
    </div>

    <div class="row" id="plansContainer">
      <?php
            try {
                $stmt = $pdo->query("SELECT * FROM hosting_plans ORDER BY price ASC");
                while ($plan = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
      <div class="col-md-4">
        <div class="plan-card">
          <span class="status-badge status-<?php echo strtolower($plan['status']); ?>">
            <?php echo ucfirst($plan['status']); ?>
          </span>
          <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
          <div class="price">$<?php echo number_format($plan['price'], 2); ?>/mo</div>
          <ul class="features">
            <li><i class="fas fa-hdd"></i> <?php echo htmlspecialchars($plan['storage']); ?> Storage</li>
            <li><i class="fas fa-network-wired"></i> <?php echo htmlspecialchars($plan['bandwidth']); ?> Bandwidth</li>
            <li><i class="fas fa-globe"></i> <?php echo htmlspecialchars($plan['domains']); ?> Domains</li>
          </ul>
          <p class="text-muted"><?php echo htmlspecialchars($plan['description']); ?></p>
          <div class="d-flex justify-content-end mt-3">
            <button class="btn btn-sm btn-primary me-2" onclick="editPlan(<?php echo $plan['id']; ?>)">
              <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger" onclick="deletePlan(<?php echo $plan['id']; ?>)">
              <i class="fas fa-trash"></i> Delete
            </button>
          </div>
        </div>
      </div>
      <?php
                }
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>Error loading hosting plans</div>";
            }
            ?>
    </div>
  </div>

  <!-- Add Plan Modal -->
  <div class="modal fade" id="addPlanModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Hosting Plan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addPlanForm">
            <div class="mb-3">
              <label class="form-label">Plan Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Price</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control" name="price" step="0.01" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Billing Cycle</label>
              <select class="form-select" name="billing_cycle" id="billingCycle" onchange="updatePriceLabel()" required>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
                <option value="custom">Custom</option>
              </select>
            </div>
            <div class="mb-3" id="customBillingDiv" style="display: none;">
              <label class="form-label">Custom Billing Details</label>
              <input type="text" class="form-control" name="custom_billing_details"
                placeholder="e.g., Quarterly, Bi-annually">
            </div>
            <div class="mb-3">
              <label class="form-label">Storage</label>
              <input type="text" class="form-control" name="storage" required placeholder="e.g., 10GB">
            </div>
            <div class="mb-3">
              <label class="form-label">Bandwidth</label>
              <input type="text" class="form-control" name="bandwidth" required placeholder="e.g., 100GB">
            </div>
            <div class="mb-3">
              <label class="form-label">Number of Domains</label>
              <input type="number" class="form-control" name="domains" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="savePlan()">Save Plan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Plan Modal -->
  <div class="modal fade" id="editPlanModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Hosting Plan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editPlanForm">
            <input type="hidden" name="id">
            <div class="mb-3">
              <label class="form-label">Plan Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Price</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control" name="price" step="0.01" required>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Billing Cycle</label>
              <select class="form-select" name="billing_cycle" id="editBillingCycle" onchange="updateEditPriceLabel()"
                required>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
                <option value="custom">Custom</option>
              </select>
            </div>
            <div class="mb-3" id="editCustomBillingDiv" style="display: none;">
              <label class="form-label">Custom Billing Details</label>
              <input type="text" class="form-control" name="custom_billing_details"
                placeholder="e.g., Quarterly, Bi-annually">
            </div>
            <div class="mb-3">
              <label class="form-label">Storage</label>
              <input type="text" class="form-control" name="storage" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Bandwidth</label>
              <input type="text" class="form-control" name="bandwidth" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Number of Domains</label>
              <input type="number" class="form-control" name="domains" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="updatePlan()">Update Plan</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function updatePriceLabel() {
    const billingCycle = document.getElementById('billingCycle').value;
    const customBillingDiv = document.getElementById('customBillingDiv');

    if (billingCycle === 'custom') {
      customBillingDiv.style.display = 'block';
    } else {
      customBillingDiv.style.display = 'none';
    }
  }

  function updateEditPriceLabel() {
    const billingCycle = document.getElementById('editBillingCycle').value;
    const customBillingDiv = document.getElementById('editCustomBillingDiv');

    if (billingCycle === 'custom') {
      customBillingDiv.style.display = 'block';
    } else {
      customBillingDiv.style.display = 'none';
    }
  }

  function savePlan() {
    const form = document.getElementById('addPlanForm');
    const formData = new FormData(form);
    formData.append('action', 'create');

    fetch('../../controllers/HostingPlanController.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.error || 'Error creating plan');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
  }

  function editPlan(id) {
    fetch(`../../controllers/HostingPlanController.php?action=get&id=${id}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const form = document.getElementById('editPlanForm');
          form.querySelector('[name="id"]').value = data.plan.id;
          form.querySelector('[name="name"]').value = data.plan.name;
          form.querySelector('[name="description"]').value = data.plan.description;
          form.querySelector('[name="price"]').value = data.plan.price;
          form.querySelector('[name="storage"]').value = data.plan.storage;
          form.querySelector('[name="bandwidth"]').value = data.plan.bandwidth;
          form.querySelector('[name="domains"]').value = data.plan.domains;
          form.querySelector('[name="status"]').value = data.plan.status;
          form.querySelector('#editBillingCycle').value = data.plan.billing_cycle;
          form.querySelector('#editCustomBillingDiv input[name="custom_billing_details"]').value = data.plan
            .custom_billing_details || '';
          updateEditPriceLabel();

          new bootstrap.Modal(document.getElementById('editPlanModal')).show();
        } else {
          alert(data.error || 'Error loading plan data');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
  }

  function updatePlan() {
    const form = document.getElementById('editPlanForm');
    const formData = new FormData(form);
    formData.append('action', 'update');

    fetch('../../controllers/HostingPlanController.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.error || 'Error updating plan');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
  }

  function deletePlan(id) {
    if (confirm('Are you sure you want to delete this hosting plan?')) {
      fetch('../../controllers/HostingPlanController.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert(data.error || 'Error deleting plan');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred');
        });
    }
  }
  </script>
</body>

</html>