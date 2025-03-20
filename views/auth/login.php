<?php
require_once '../../config/config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../client/dashboard.php');
    }
    exit();
}

// Add this to check if the file exists
$controllerPath = realpath(__DIR__ . '/../../controllers/AuthController.php');
?>
<!DOCTYPE html>
<html>

<head>
  <title>Login | Client Payment System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  :root {
    --primary-color: #170F00;
    --secondary-color: #F45F1E;
  }

  body {
    background-color: #f8f9fa;
    height: 100vh;
  }

  .login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: #f8f9fa;
  }

  .login-card {
    width: 100%;
    max-width: 1000px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }

  .login-sidebar {
    background: #170F00;
    color: white;
    padding: 40px;
  }

  .welcome-text {
    font-size: 2.5rem;
    margin-bottom: 20px;
  }

  .sidebar-text {
    font-size: 1.1rem;
    opacity: 0.8;
  }

  .login-form {
    padding: 40px;
  }

  .login-title {
    font-size: 1.8rem;
    margin-bottom: 30px;
    color: #170F00;
  }

  .form-control {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ddd;
  }

  .btn-primary {
    background: #F45F1E;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
  }

  .btn-primary:hover {
    background: #d45419;
  }
  </style>
</head>

<body class="bg-light">
  <div class="login-container">
    <div class="login-card">
      <div class="row g-0">
        <div class="col-lg-6 login-sidebar d-none d-lg-block">
          <div class="d-flex flex-column h-100 justify-content-center">
            <h1 class="welcome-text">Welcome Back!</h1>
            <p class="sidebar-text">Access your account to manage payments and more.</p>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="login-form">
            <h2 class="login-title">Sign In</h2>
            <?php
            if (isset($_SESSION['error'])) {
              echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
              unset($_SESSION['error']);
            }
            ?>
            <form method="POST" action="../../controllers/AuthController.php" id="loginForm">
              <input type="hidden" name="action" value="login">
              <div class="mb-4">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" id="emailInput" required>
              </div>
              <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="passwordInput" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('../../controllers/AuthController.php', {
        method: 'POST',
        body: formData
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.text();
      })
      .then(data => {
        console.log('Response:', data); // For debugging
        try {
          const result = JSON.parse(data);
          if (result.success) {
            window.location.href = result.redirect;
          } else {
            alert(result.error || 'Login failed');
          }
        } catch (e) {
          console.error('Error parsing JSON:', e, 'Data:', data);
          alert('Login failed. Please try again.');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
      });
  });
  </script>

</body>

</html>

