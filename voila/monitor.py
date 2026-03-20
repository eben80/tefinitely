import urllib.request
import hashlib
import difflib
import smtplib
from email.mime.text import MIMEText
from datetime import datetime, timedelta
import mysql.connector
from bs4 import BeautifulSoup
from config import get_db_connection

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
    body = f"A visible text change was detected on {url}.\n\n--- Snippet of visible changes ---\n\n{snippet}\n\n"

    msg = MIMEText(body)
    msg['Subject'] = subject
    msg['From'] = 'monitor@example.com'
    msg['To'] = to_email

    try:
        with smtplib.SMTP('localhost') as s:
            s.send_message(msg)
    except Exception as e:
        print(f"Error sending email to {to_email}: {e}")
        print(f"--- MOCK EMAIL ---")
        print(f"Subject: {subject}")
        print(f"Body:\n{body}")

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
