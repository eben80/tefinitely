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
    subject = f"Visible change detected on: {url}"
    body_text = f"A visible text change was detected on {url}.\n\n--- Snippet of visible changes ---\n\n{snippet}\n\n"
    body_html = f"""
    <html>
    <head></head>
    <body>
      <h1>Visible change detected</h1>
      <p>A visible text change was detected on <a href="{url}">{url}</a>.</p>
      <p>--- Snippet of visible changes ---</p>
      <pre>{snippet}</pre>
    </body>
    </html>
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
        SELECT m.id, m.user_id, m.url, m.last_content, m.last_hash, u.email
        FROM monitors m
        JOIN users u ON m.user_id = u.id
        WHERE m.last_checked IS NULL
        OR DATE_ADD(m.last_checked, INTERVAL m.interval_minutes MINUTE) <= NOW()
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

        if monitor['last_hash'] is None:
            cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW() WHERE id = %s", (visible_text, new_hash, monitor['id']))
        elif new_hash != monitor['last_hash']:
            snippet = get_diff_snippet(monitor['last_content'], visible_text)
            if snippet: # Only notify if there's an actual difference in text
                send_notification(monitor['email'], monitor['url'], snippet)
                cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW() WHERE id = %s", (visible_text, new_hash, monitor['id']))
            else:
                cursor.execute("UPDATE monitors SET last_checked = NOW() WHERE id = %s", (monitor['id'],))
        else:
            cursor.execute("UPDATE monitors SET last_checked = NOW() WHERE id = %s", (monitor['id'],))

    conn.commit()
    conn.close()

if __name__ == "__main__":
    check_monitors()
