import urllib.request
import hashlib
import difflib
import boto3
from botocore.exceptions import BotoCoreError, ClientError
from datetime import datetime, timedelta
import mysql.connector
from bs4 import BeautifulSoup
from config import get_db_connection, AWS_CONFIG

def fetch_url(url):
    try:
        req = urllib.request.Request(
            url,
            data=None,
            headers={
                'User-Agent': 'Mozilla/5.0 (URL Monitor Script)'
            }
        )
        with urllib.request.urlopen(req) as response:
            html = response.read().decode('utf-8', errors='ignore')
            return html
    except Exception as e:
        print(f"Error fetching {url}: {e}")
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
        print(f"Error extracting text: {e}")
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

def send_notification(to_email, url, snippet):
    subject = f"Visible Change Detected - Voila!"
    body_text = f"A visible text change was detected on {url}.\n\n--- Snippet of visible changes ---\n\n{snippet}\n\n"

    # Format the current year for the footer
    current_year = datetime.now().year

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
        response = client.send_email(
            Destination={
                'ToAddresses': [to_email],
            },
            Message={
                'Body': {
                    'Html': {
                        'Charset': 'UTF-8',
                        'Data': body_html,
                    },
                    'Text': {
                        'Charset': 'UTF-8',
                        'Data': body_text,
                    },
                },
                'Subject': {
                    'Charset': 'UTF-8',
                    'Data': subject,
                },
            },
            Source=AWS_CONFIG['sender_email'],
            ReplyToAddresses=[AWS_CONFIG['reply_to']]
        )
        print(f"Email sent! Message ID: {response['MessageId']}")
    except (BotoCoreError, ClientError) as e:
        print(f"Error sending email to {to_email}: {e}")
        print(f"--- MOCK EMAIL ---")
        print(f"Subject: {subject}")
        print(f"Body:\n{body_text}")

def check_monitors():
    conn = get_db_connection()
    if conn is None:
        return
    cursor = conn.cursor(dictionary=True)

    cursor.execute("""
        SELECT m.id, m.user_id, m.url, m.last_content, m.last_hash, u.email, m.emails_sent_this_hour, m.hour_start_time
        FROM monitors m
        JOIN users u ON m.user_id = u.id
        WHERE m.is_paused = 0 AND (
            m.last_checked IS NULL
            OR DATE_ADD(m.last_checked, INTERVAL m.interval_minutes MINUTE) <= NOW()
        )
    """)
    monitors = cursor.fetchall()

    for monitor in monitors:
        print(f"Checking {monitor['url']}...")
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
            cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW() WHERE id = %s", (visible_text, new_hash, monitor['id']))
        elif new_hash != monitor['last_hash']:
            snippet = get_diff_snippet(monitor['last_content'], visible_text)
            if snippet: # Only notify if there's an actual difference in text
                if emails_sent < 6:
                    send_notification(monitor['email'], monitor['url'], snippet)
                    cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW(), emails_sent_this_hour = emails_sent_this_hour + 1 WHERE id = %s", (visible_text, new_hash, monitor['id']))
                else:
                    print(f"Email cap reached for {monitor['url']}. Notification skipped.")
                    cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW() WHERE id = %s", (visible_text, new_hash, monitor['id']))
            else:
                cursor.execute("UPDATE monitors SET last_checked = NOW() WHERE id = %s", (monitor['id'],))
        else:
            cursor.execute("UPDATE monitors SET last_checked = NOW() WHERE id = %s", (monitor['id'],))

    conn.commit()
    conn.close()

if __name__ == "__main__":
    check_monitors()
