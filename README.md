# Client Payment Management System

A PHP-based system for managing client payments and hosting plans.

## Features

- Admin and Client roles
- Secure login system
- Hosting plan management
- Payment tracking
- Email/SMS notifications
- Payment reminders
- Export functionality (CSV/PDF)

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (for dependencies)

## Installation

1. Clone the repository to your web server directory:

```bash
git clone [repository-url]
```

2. Create a MySQL database and import the schema:

```bash
mysql -u root -p < config/database.sql
```

3. Configure the application:

   - Copy `config/config.php` to `config/config.local.php`
   - Update database credentials and other settings

4. Install dependencies:

```bash
composer install
```

5. Set up email and SMS configurations:

   - Update SMTP settings in config file
   - Add Twilio credentials for SMS notifications

6. Set up Stripe integration:
   - Add your Stripe API keys to the config file

## Default Admin Login

- Email: admin@example.com
- Password: password

## Directory Structure

```
/client-payment-system/
│── /config/            # Configuration files
│── /public/            # Public assets
│── /views/             # Frontend pages
│── /controllers/       # Business logic
│── /models/           # Database queries
│── /helpers/          # Utility functions
│── /cron/             # Automated scripts
│── /routes/           # Request routing
│── /vendor/           # Dependencies
│── index.php          # Entry point
│── .env               # Environment variables
│── README.md          # Documentation
```

## Security Considerations

- All passwords are hashed using PHP's password_hash()
- Session security is implemented
- SQL injection prevention using prepared statements
- XSS protection through input sanitization
- CSRF protection on forms

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
