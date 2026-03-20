# URL Monitor

A PHP-based URL monitoring tool that notifies users via email when changes are detected on tracked pages. This version is designed to detect only **user-visible text changes**, ignoring source code-only modifications (like scripts, styles, and meta tags).

## Features
- User registration and login (hashed passwords, CSRF protection).
- Add multiple URLs to monitor with custom intervals.
- Background script to check for changes and extract only visible text.
- Email notifications with snippets of visible changes (unified diff).
- Designed for an Nginx/PHP/MySQL stack on EC2.

## Installation and Setup

The application is configured to run in `/var/www/tefinitely.com/html/voila` and will be accessible at `https://tefinitely.com/voila`.

1. **Upload Files:** Place all project files in `/var/www/tefinitely.com/html/voila`.
2. **Database:** Create a MySQL database and run `schema.sql` to initialize the tables.
3. **Dependencies:** Create a virtual environment and install the required Python libraries:
   ```bash
   cd /var/www/tefinitely.com/html/voila
   python3 -m venv venv
   source venv/bin/activate
   pip install -r requirements.txt
   ```
4. **Configuration:**
   - Copy `config.php.example` to `config.php` and update it with your MySQL host, database name, user, and password.
   - Copy `config.py.example` to `config.py` and update it with the same MySQL credentials and AWS SES configuration.
5. **Email:** The application uses AWS SES for notifications as configured in `config.py`.
6. **Background Monitoring:** Set up a cron job to run the monitor script. For example, to run every minute:
   ```bash
   * * * * * /var/www/tefinitely.com/html/voila/venv/bin/python3 /var/www/tefinitely.com/html/voila/monitor.py >> /var/log/url_monitor.log 2>&1
   ```

## Usage
- Access the dashboard at `https://tefinitely.com/voila/index.php`.
- Register a new account to start adding URLs to monitor.
- The background script will periodically check the URLs and send notifications to your registered email address.
