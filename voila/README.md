# URL Monitor

A PHP-based URL monitoring tool that notifies users via email when changes are detected on tracked pages.

## Features
- User registration and login.
- Add multiple URLs to monitor.
- Background script to check for changes at intervals.
- Email notifications with snippets of changes (before/after).
- Designed to run in a subdirectory on an Nginx/PHP/MySQL server.

## Installation and Setup

1. **Database:** Create a MySQL database and run `schema.sql` to initialize the tables.
2. **Dependencies:** Install the required Python libraries:
   ```bash
   pip install beautifulsoup4 mysql-connector-python
   ```
3. **Configuration:** Update `config.php` and `config.py` with your database credentials.
4. **Email:** Configure the SMTP server in `monitor.py` for notifications.
5. **Background Monitoring:** Set up a cron job to run the monitor script. For example, to run every minute:
   ```bash
   * * * * * python3 /path/to/your/site/folder/monitor.py >> /var/log/url_monitor.log 2>&1
   ```
