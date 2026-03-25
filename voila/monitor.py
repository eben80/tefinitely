import os
import urllib.request
import hashlib
import difflib
import boto3
from botocore.exceptions import BotoCoreError, ClientError
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.application import MIMEApplication
from datetime import datetime, timedelta
import mysql.connector
from bs4 import BeautifulSoup
from playwright.sync_api import sync_playwright
from config import get_db_connection, AWS_CONFIG

def log(message):
    timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{timestamp}] {message}")

def fetch_url(url):
    try:
        req = urllib.request.Request(
            url,
            data=None,
            headers={
            'User-Agent': 'Mozilla/5.0 (Voila! Bot)'
            }
        )
        with urllib.request.urlopen(req) as response:
            html = response.read().decode('utf-8', errors='ignore')
            return html
    except Exception as e:
        log(f"Error fetching {url}: {e}")
        return None

def extract_visible_text(html):
    """
    Extracts only the user-visible text from an HTML document,
    ignoring scripts, styles, and other non-visible elements.
    """
    try:
        soup = BeautifulSoup(html, 'html.parser')

        # Remove script and style elements
        for script_or_style in soup(["script", "style", "meta", "link", "noscript"]):
            script_or_style.decompose()

        # Get text
        text = soup.get_text(separator=' ')

        # Clean up whitespace
        import re
        text = re.sub(r' +', ' ', text) # Replace multiple spaces with a single space
        lines = [line.strip() for line in text.splitlines() if line.strip()]
        text = '\n'.join(lines)

        return text
    except Exception as e:
        log(f"Error extracting text: {e}")
        return html # Fallback to raw HTML if extraction fails

def get_hash(content):
    return hashlib.sha256(content.encode('utf-8')).hexdigest()

def get_diff_snippet(old_content, new_content):
    old_lines = old_content.splitlines()
    new_lines = new_content.splitlines()
    diff = list(difflib.unified_diff(old_lines, new_lines, n=3, lineterm=''))
    if not diff:
        return ""
    return "\n".join(diff)

def delete_screenshot(filename):
    if not filename:
        return
    filepath = os.path.join(os.path.dirname(__file__), 'screenshots', filename)
    if os.path.exists(filepath):
        try:
            os.remove(filepath)
            log(f"Deleted old screenshot: {filename}")
        except Exception as e:
            log(f"Error deleting screenshot {filename}: {e}")

def take_screenshot(url, filename):
    filepath = os.path.join(os.path.dirname(__file__), 'screenshots', filename)
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            page = browser.new_page(viewport={'width': 1280, 'height': 800})
            page.goto(url, wait_until="networkidle")
            page.screenshot(path=filepath, full_page=True)
            browser.close()
            return filename
    except Exception as e:
        log(f"Error taking screenshot for {url}: {e}")
        return None

def send_notification(to_email, url, snippet, screenshot_filename=None):
    subject = f"Visible Change Detected - Voila!"
    current_year = datetime.now().year

    body_text = f"A visible text change was detected on {url}.\n\n--- Snippet of visible changes ---\n\n{snippet}\n\n"
    body_html = f"""
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;'>
        <div style='background: #007bff; padding: 20px; text-align: center;'>
            <h1 style='color: white; margin: 0;'>Voila!</h1>
        </div>
        <div style='padding: 30px; line-height: 1.6; color: #333;'>
            <h2 style='color: #dc3545;'>Visible Change Detected</h2>
            <p>Voila! has detected a visible text change on the following URL:</p>
            <p style='background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 4px solid #007bff;'>
                <a href='{url}' style='color: #007bff; text-decoration: none;'>{url}</a>
            </p>

            <h3 style='margin-top: 25px;'>Changes detected:</h3>
            <div style='background: #333; color: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 13px; overflow-x: auto;'>
                <pre style='margin: 0; white-space: pre-wrap;'>{snippet}</pre>
            </div>

            {f"<p style='margin-top: 20px;'>A screenshot of the current page is attached to this email.</p>" if screenshot_filename else ""}

            <div style='text-align: center; margin: 30px 0;'>
                <a href='https://tefinitely.com/voila/index.php' style='background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Dashboard</a>
            </div>
        </div>
        <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d;'>
            &copy; {current_year} Voila! URL Monitoring Service
        </div>
    </div>
    """

    client = boto3.client(
        'ses',
        region_name=AWS_CONFIG['region_name'],
        aws_access_key_id=AWS_CONFIG['aws_access_key_id'],
        aws_secret_access_key=AWS_CONFIG['aws_secret_access_key']
    )

    try:
        msg = MIMEMultipart()
        msg['Subject'] = subject
        msg['From'] = AWS_CONFIG['sender_email']
        msg['To'] = to_email
        msg.add_header('Reply-To', AWS_CONFIG['reply_to'])

        msg_body = MIMEMultipart('alternative')
        msg_body.attach(MIMEText(body_text, 'plain'))
        msg_body.attach(MIMEText(body_html, 'html'))
        msg.attach(msg_body)

        if screenshot_filename:
            filepath = os.path.join(os.path.dirname(__file__), 'screenshots', screenshot_filename)
            if os.path.exists(filepath):
                with open(filepath, 'rb') as f:
                    part = MIMEApplication(f.read())
                    part.add_header('Content-Disposition', 'attachment', filename=screenshot_filename)
                    msg.attach(part)

        response = client.send_raw_email(
            Source=AWS_CONFIG['sender_email'],
            Destinations=[to_email],
            RawMessage={'Data': msg.as_string()}
        )
        log(f"Email sent! Message ID: {response['MessageId']}")
    except (BotoCoreError, ClientError, Exception) as e:
        log(f"Error sending email to {to_email}: {e}")
        log(f"--- MOCK EMAIL ---")
        log(f"Subject: {subject}")
        log(f"Body:\n{body_text}")

def check_monitors():
    conn = get_db_connection()
    if conn is None:
        return
    cursor = conn.cursor(dictionary=True)

    cursor.execute("""
        SELECT m.id, m.user_id, m.url, m.last_content, m.last_hash, u.email, m.emails_sent_this_hour, m.hour_start_time, m.last_screenshot
        FROM monitors m
        JOIN users u ON m.user_id = u.id
        WHERE m.is_paused = 0 AND (
            m.last_checked IS NULL
            OR DATE_ADD(m.last_checked, INTERVAL m.interval_minutes MINUTE) <= NOW()
        )
    """)
    monitors = cursor.fetchall()

    for monitor in monitors:
        log(f"Checking {monitor['url']}...")
        html = fetch_url(monitor['url'])
        if html is None:
            continue

        # Extract visible text before hashing and diffing
        visible_text = extract_visible_text(html)
        new_hash = get_hash(visible_text)

        now = datetime.now()
        hour_ago = now - timedelta(hours=1)

        emails_sent = monitor['emails_sent_this_hour']
        hour_start = monitor['hour_start_time']

        if hour_start is None or hour_start < hour_ago:
            emails_sent = 0
            hour_start = now
            cursor.execute("UPDATE monitors SET emails_sent_this_hour = 0, hour_start_time = %s WHERE id = %s", (hour_start, monitor['id']))

        if monitor['last_hash'] is None:
            filename = f"monitor_{monitor['id']}_{int(datetime.now().timestamp())}.png"
            screenshot = take_screenshot(monitor['url'], filename)
            if screenshot:
                delete_screenshot(monitor.get('last_screenshot'))
            cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW(), last_screenshot = %s WHERE id = %s", (visible_text, new_hash, screenshot, monitor['id']))
        elif new_hash != monitor['last_hash']:
            snippet = get_diff_snippet(monitor['last_content'], visible_text)
            if snippet: # Only notify if there's an actual difference in text
                filename = f"change_{monitor['id']}_{int(datetime.now().timestamp())}.png"
                screenshot = take_screenshot(monitor['url'], filename)
                if screenshot:
                    delete_screenshot(monitor.get('last_screenshot'))

                if emails_sent < 6:
                    send_notification(monitor['email'], monitor['url'], snippet, screenshot)
                    cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW(), last_changed = NOW(), last_screenshot = %s, emails_sent_this_hour = emails_sent_this_hour + 1 WHERE id = %s", (visible_text, new_hash, screenshot, monitor['id']))
                else:
                    log(f"Email cap reached for {monitor['url']}. Notification skipped.")
                    cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW(), last_changed = NOW(), last_screenshot = %s WHERE id = %s", (visible_text, new_hash, screenshot, monitor['id']))
            else:
                cursor.execute("UPDATE monitors SET last_checked = NOW() WHERE id = %s", (monitor['id'],))
        else:
            cursor.execute("UPDATE monitors SET last_checked = NOW() WHERE id = %s", (monitor['id'],))

    conn.commit()
    conn.close()

def cleanup_screenshots():
    """
    Scans the screenshots directory and deletes files that are not referenced in the database.
    """
    log("Starting orphaned screenshot cleanup...")
    conn = get_db_connection()
    if conn is None:
        return
    cursor = conn.cursor(dictionary=True)

    cursor.execute("SELECT last_screenshot FROM monitors WHERE last_screenshot IS NOT NULL")
    referenced_files = {row['last_screenshot'] for row in cursor.fetchall()}
    conn.close()

    screenshots_dir = os.path.join(os.path.dirname(__file__), 'screenshots')
    if not os.path.exists(screenshots_dir):
        return

    now = datetime.now().timestamp()
    deleted_count = 0

    for filename in os.listdir(screenshots_dir):
        if filename in ('.gitignore', '.htaccess'):
            continue

        if filename not in referenced_files:
            filepath = os.path.join(screenshots_dir, filename)
            # Only delete if older than 10 minutes to avoid deleting a file currently being written
            if now - os.path.getmtime(filepath) > 600:
                try:
                    os.remove(filepath)
                    deleted_count += 1
                except Exception as e:
                    log(f"Error cleaning up orphaned file {filename}: {e}")

    if deleted_count > 0:
        log(f"Cleanup complete. Deleted {deleted_count} orphaned screenshot(s).")

if __name__ == "__main__":
    check_monitors()
    cleanup_screenshots()
