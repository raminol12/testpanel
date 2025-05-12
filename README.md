# Marzban Web Panel

A web-based management panel for Marzban VPN servers, built with PHP and MySQL.

## Features

- User authentication and authorization
- Panel management
- User management within panels
- Traffic monitoring
- Expiration time management
- Responsive design with Bootstrap
- RTL support for Persian language
- Secure password hashing
- Input sanitization
- Database transactions
- Error handling

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Nginx or Apache web server
- Composer (PHP package manager)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/your-username/marzban-panel.git
cd marzban-panel
```

2. Run the installation script:
```bash
chmod +x install.sh
./install.sh
```

3. Follow the installation prompts to:
   - Set up the database
   - Configure the web server
   - Create an admin user

4. Access the panel through your web browser and log in with your credentials.

## Directory Structure

```
marzban-panel/
├── api/                    # API endpoints
│   ├── add_user.php
│   └── delete_user.php
├── config.php             # Configuration file
├── database.sql           # Database schema
├── index.php             # Login page
├── login.php             # Login handler
├── dashboard.php         # Main dashboard
├── panel.php             # Panel management
├── logout.php            # Logout handler
├── install.sh            # Installation script
└── README.md             # This file
```

## Security

- All passwords are hashed using bcrypt
- Input is sanitized to prevent XSS attacks
- SQL injection prevention using prepared statements
- Session management with secure settings
- CSRF protection
- XSS protection headers

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

If you encounter any issues or have questions, please open an issue in the GitHub repository. 