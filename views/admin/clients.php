<?php
require_once '../../config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// If it's an AJAX request for table content
if (isset($_GET['action']) && $_GET['action'] === 'get_table') {
    try {
        $stmt = $pdo->query("
            SELECT u.*, s.server_name, s.specifications, s.monthly_amount, 
                   s.advance_months_paid, s.start_date
            FROM users u
            LEFT JOIN servers s ON u.id = s.client_id
            WHERE u.role = 'client'
            ORDER BY u.id DESC
        ");
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "<td>{$row['company_name']}</td>";
            echo "<td>" . (isset($row['server_name']) ? htmlspecialchars($row['server_name']) : 'N/A') . "</td>";
            echo "<td>" . (isset($row['specifications']) ? htmlspecialchars($row['specifications']) : 'N/A') . "</td>";
            echo "<td>" . (isset($row['monthly_amount']) ? '₨ ' . number_format($row['monthly_amount'], 2) : 'N/A') . "</td>";
            echo "<td>" . (isset($row['advance_months_paid']) ? '₨ ' . number_format($row['advance_months_paid'], 2) : 'N/A') . "</td>";
            echo "<td>" . (isset($row['start_date']) ? date('Y-m-d', strtotime($row['start_date'])) : 'N/A') . "</td>";
            echo "<td>
                    <button class='btn btn-sm btn-primary btn-action' onclick='editClient({$row['id']})'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='btn btn-sm btn-danger btn-action' onclick='deleteClient({$row['id']})'>
                        <i class='fas fa-trash'></i>
                    </button>
                </td>";
            echo "</tr>";
        }
        exit;
    } catch (PDOException $e) {
        echo "<tr><td colspan='10'>Error loading data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client Management</title>
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

  .client-form {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
  }

  .clients-table {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .btn-action {
    padding: 5px 10px;
    margin: 0 2px;
  }

  .status-badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .status-active {
    background-color: #d4edda;
    color: #155724;
  }

  .status-inactive {
    background-color: #f8d7da;
    color: #721c24;
  }

  .table> :not(caption)>*>* {
    padding: 1rem;
  }

  .table tbody tr:hover {
    background-color: #f8f9fa;
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
  <!-- Sidebar - Copy from your dashboard -->
  <div class="sidebar">
    <div class="sidebar-brand">
      Admin Panel
    </div>
    <div class="sidebar-menu">
      <a href="dashboard.php" class="menu-item">
        <i class="fas fa-dashboard"></i> Dashboard
      </a>
      <a href="clients.php" class="menu-item active">
        <i class="fas fa-users"></i> Clients
      </a>
      <a href="#" class="menu-item">
        <i class="fas fa-credit-card"></i> Payments
      </a>
      <a href="#" class="menu-item">
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

  <div class="main-content">
    <div class="top-bar">
      <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Client Management</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
          <i class="fas fa-plus"></i> Add New Client
        </button>
      </div>
    </div>

    <!-- Clients Table -->
    <div class="clients-table">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Company Name</th>
            <th>Server Name</th>
            <th>Specifications</th>
            <th>Amount/Month</th>
            <th>Advance Payment</th>
            <th>Start Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="clientsTableBody">
          <?php
          try {
            $stmt = $pdo->query("
              SELECT u.*, s.server_name, s.specifications, s.monthly_amount, 
                     s.advance_months_paid, s.start_date
              FROM users u
              LEFT JOIN servers s ON u.id = s.client_id
              WHERE u.role = 'client'
              ORDER BY u.id DESC
            ");
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              echo "<tr>";
              echo "<td>{$row['id']}</td>";
              echo "<td>{$row['username']}</td>";
              echo "<td>{$row['email']}</td>";
              echo "<td>{$row['company_name']}</td>";
              echo "<td>" . (isset($row['server_name']) ? htmlspecialchars($row['server_name']) : 'N/A') . "</td>";
              echo "<td>" . (isset($row['specifications']) ? htmlspecialchars($row['specifications']) : 'N/A') . "</td>";
              echo "<td>" . (isset($row['monthly_amount']) ? '₨ ' . number_format($row['monthly_amount'], 2) : 'N/A') . "</td>";
              echo "<td>" . (isset($row['advance_months_paid']) ? '₨ ' . number_format($row['advance_months_paid'], 2) : 'N/A') . "</td>";
              echo "<td>" . (isset($row['start_date']) ? date('Y-m-d', strtotime($row['start_date'])) : 'N/A') . "</td>";
              echo "<td>
                      <button class='btn btn-sm btn-primary btn-action' onclick='editClient({$row['id']})'>
                          <i class='fas fa-edit'></i>
                      </button>
                      <button class='btn btn-sm btn-danger btn-action' onclick='deleteClient({$row['id']})'>
                          <i class='fas fa-trash'></i>
                      </button>
                  </td>";
              echo "</tr>";
            }
          } catch (PDOException $e) {
            echo "<tr><td colspan='10'>Error loading data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add Client Modal -->
  <div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Client</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addClientForm">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="tel" class="form-control" name="phone" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Company Name</label>
              <input type="text" class="form-control" name="company_name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Role</label>
              <select class="form-control" name="role" required>
                <option value="client">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-control" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveClient()">Save Client</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Client Modal -->
  <div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Client</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editClientForm">
            <input type="hidden" name="id">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password"
                placeholder="Leave blank to keep current password">
            </div>
            <div class="mb-3">
              <label class="form-label">phone</label>
              <input type="tel" class="form-control" name="phone"
                placeholder="Phone">
            </div>
            <div class="mb-3">
              <label class="form-label">Company Name</label>
              <input type="text" class="form-control" name="company_name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Role</label>
              <select class="form-control" name="role" required>
                <option value="client">User</option>
                <option value=" admin">Admin</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-control" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="updateClient()">Update Client</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Server Modal -->
  <div class="modal fade" id="addServerModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Server Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addServerForm">
          <input type="hidden" name="action" value="create">
            <input type="hidden" name="client_id" id="server_user_id">
            <div class="mb-3">
              <label class="form-label">Server Name</label>
              <input type="text" class="form-control" name="server_name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Specifications</label>
              <textarea class="form-control" name="specifications" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Amount per Month</label>
              <input type="number" class="form-control" name="monthly_amount" step="0.01" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Advance Payment</label>
              <input type="number" class="form-control" name="advance_months_paid" min="0" max="12" step="1" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Start Date</label>
              <input type="date" class="form-control" name="start_date" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveServer()">Save Server</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function saveClient() {
    const form = document.getElementById('addClientForm');
    const formData = new FormData(form);
    formData.append('action', 'create');

    fetch('../../controllers/ClientController.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Set the user_id for the server form
          document.getElementById('server_user_id').value = data.data.user_id;
          // Hide client modal
          bootstrap.Modal.getInstance(document.getElementById('addClientModal')).hide();
          // Show server modal
          new bootstrap.Modal(document.getElementById('addServerModal')).show();
        } else {
          alert(data.error || 'Error creating client');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
  }

  function saveServer() {
    const form = document.getElementById('addServerForm');
    const formData = new FormData(form);
    formData.append('action', 'create');

    fetch('../../controllers/ServerController.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Reset the form
          form.reset();

          // Ask if user wants to add another server
          if (confirm('Server added successfully. Do you want to add another server for this client?')) {
            // Keep the modal open and keep the same user_id
            const userId = document.getElementById('server_user_id').value;
            document.getElementById('server_user_id').value = userId;
            // Refresh only the table content
            fetch('clients.php?action=get_table')
              .then(response => response.text())
              .then(html => {
                document.getElementById('clientsTableBody').innerHTML = html;
              })
              .catch(error => {
                console.error('Error refreshing table:', error);
              });
          } else {
            // Close the modal and refresh the page
            location.reload();
          }
        } else {
          alert(data.error || 'Error saving server details');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
      });
  }

  function editClient(id) {
    // Add error logging
    console.log('Editing client:', id);

    fetch(`../../controllers/ClientController.php?action=get&id=${id}`)
      .then(response => response.json())
      .then(data => {
        console.log('Received data:', data); // Debug log

        if (data.success) {
          const form = document.getElementById('editClientForm');
          const client = data.data.client;

          // Set form values
          form.elements['id'].value = client.id;
          form.elements['name'].value = client.username;
          form.elements['email'].value = client.email;
          form.elements['company_name'].value = client.company_name;
          form.elements['role'].value = client.role.trim(); // Added trim()
          form.elements['status'].value = client.status;

          // Show modal
          new bootstrap.Modal(document.getElementById('editClientModal')).show();
        } else {
          alert(data.error || 'Error loading client data');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading client data');
      });
  }

  function updateClient() {
    const form = document.getElementById('editClientForm');
    const formData = new FormData(form);
    formData.append('action', 'update');

    // Add error logging
    console.log('Updating client with data:', Object.fromEntries(formData));

    fetch('../../controllers/ClientController.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        console.log('Update response:', data); // Debug log

        if (data.success) {
          location.reload();
        } else {
          alert(data.error || 'Error updating client');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating client');
      });
  }

  function deleteClient(id) {
    if (confirm('Are you sure you want to delete this client?')) {
      console.log('Deleting client:', id); // Debug log

      fetch('../../controllers/ClientController.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'delete',
            id: id
          })
        })
        .then(response => response.json())
        .then(data => {
          console.log('Delete response:', data); // Debug log

          if (data.success) {
            location.reload();
          } else {
            alert(data.error || 'Error deleting client');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while deleting client');
        });
    }
  }
  </script>
</body>

</html>