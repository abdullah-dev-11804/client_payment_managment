<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 - Page Not Found</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
  }

  .error-container {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  }

  .error-code {
    font-size: 6rem;
    font-weight: bold;
    color: #F45F1E;
    margin-bottom: 20px;
  }

  .error-message {
    font-size: 1.5rem;
    color: #170F00;
    margin-bottom: 30px;
  }

  .btn-primary {
    background: #F45F1E;
    border: none;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
  }

  .btn-primary:hover {
    background: #d45419;
  }
  </style>
</head>

<body>
  <div class="error-container">
    <div class="error-code">404</div>
    <div class="error-message">Page Not Found</div>
    <p class="text-muted mb-4">The page you are looking for might have been removed or is temporarily unavailable.</p>
    <a href="/client-payment-system/views/auth/login.php" class="btn btn-primary">Back to Login</a>
  </div>
</body>

</html>