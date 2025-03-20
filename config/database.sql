CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') NOT NULL DEFAULT 'client',
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    company_name VARCHAR(100), -- Added column for clients company name
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    server_name VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    specifications TEXT NOT NULL,
    monthly_amount DECIMAL(10,2) NOT NULL,
    start_date DATE NOT NULL,
    next_due_date DATE NOT NULL,
    advance_months_paid INT DEFAULT 0, -- Added column to track months paid in advance
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
);

CREATE TABLE notification_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    due_date DATE NOT NULL,
    notification_type ENUM('email', 'sms') NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_notification (server_id, due_date, notification_type),
    FOREIGN KEY (server_id) REFERENCES servers(id)
);

CREATE TABLE settings (
    `key` VARCHAR(50) PRIMARY KEY,
    value VARCHAR(50)
);