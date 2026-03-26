import urllib.request
import hashlib
import difflib
import boto3
from botocore.exceptions import BotoCoreError, ClientError
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from datetime import datetime, timedelta
import mysql.connector
from bs4 import BeautifulSoup
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
        with urllib.request.urlopen(req, timeout=10) as response:
            html = response.read().decode('utf-8', errors='ignore')
            return html
    except Exception as e:
        log(f"Skipping {url} due to error: {e}")
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

def get_diff_snippets(old_content, new_content, max_snippets=4):
    """
    Returns up to max_snippets diff hunks between old and new content.
    """
    old_lines = old_content.splitlines()
    new_lines = new_content.splitlines()
    diff_lines = list(difflib.unified_diff(old_lines, new_lines, n=1, lineterm=''))

    if not diff_lines:
        return []

    snippets = []
    current_snippet = []

    # Skip the header (--- and +++)
    for line in diff_lines[2:]:
        if line.startswith('@@'):
            if current_snippet:
                snippets.append("\n".join(current_snippet))
            current_snippet = [line]
        else:
            current_snippet.append(line)

    if current_snippet:
        snippets.append("\n".join(current_snippet))

    return snippets[:max_snippets]


def send_resumption_notification(to_email, url):
    subject = f"Monitoring Resumed - Voila!"
    current_year = datetime.now().year

    body_text = f"Rate-limiting period has ended for {url}. Voila! will now resume sending change notifications.\n\n"
    body_html = f"""
    <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 8px; overflow: hidden;'>
        <div style='background: #007bff; padding: 20px; text-align: center;'>
            <h1 style='color: white; margin: 0;'>Voila!</h1>
        </div>
        <div style='padding: 30px; line-height: 1.6; color: #333;'>
            <h2 style='color: #28a745;'>Notifications Resumed</h2>
            <p>The hourly rate-limit for the following URL has expired:</p>
            <p style='background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 4px solid #28a745;'>
                <a href='{url}' style='color: #007bff; text-decoration: none;'>{url}</a>
            </p>
            <p>Voila! will now resume sending real-time notifications for any further visible changes.</p>

            <div style='text-align: center; margin: 30px 0;'>
                <a href='https://tefinitely.com/voila/index.php' style='background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>View Dashboard</a>
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
        msg = MIMEMultipart('alternative')
        msg['Subject'] = subject
        msg['From'] = AWS_CONFIG['sender_email']
        msg['To'] = to_email
        msg.add_header('Reply-To', AWS_CONFIG['reply_to'])
        msg.attach(MIMEText(body_text, 'plain'))
        msg.attach(MIMEText(body_html, 'html'))

        client.send_raw_email(
            Source=AWS_CONFIG['sender_email'],
            Destinations=[to_email],
            RawMessage={'Data': msg.as_string()}
        )
        log(f"Resumption email sent to {to_email} for {url}")
    except Exception as e:
        log(f"Error sending resumption email: {e}")

def send_notification(to_email, url, diff_html, is_capped=False):
    subject = f"Visible Change Detected - Voila!"
    current_year = datetime.now().year

    body_text = f"A visible text change was detected on {url}.\n\nPlease login to your dashboard to view changes.\n\n"
    body_html = f"""
    <!doctype html>
    <html lang="en">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style type="text/css">
          body {{ margin:0; padding:0; font-family: Ubuntu, Helvetica, Arial, sans-serif; background-color: #141928; color: #BFC3D5; }}
          .container {{ background:#141928; margin:0px auto; max-width:800px; padding: 20px; }}
          .header {{ background:#141928; text-align:center; padding: 20px 0; border-bottom: 1px solid #1e2538; }}
          .content {{ padding: 30px; line-height: 1.6; }}
          .diff-section {{ background:#141940; border-radius: 8px; overflow: hidden; margin-top: 20px; }}
          .diff-header {{ padding: 10px 20px; font-weight: bold; border-bottom: 1px solid #1e2538; }}
          .diff-body {{ padding: 20px; font-family: monospace; font-size: 13px; line-height: 1.4; }}
          .removed {{ background: #ff7c80; color: black; padding: 2px 5px; border-radius: 3px; }}
          .added {{ background: #2ee0bc; color: black; padding: 2px 5px; border-radius: 3px; }}
          .btn {{ display: inline-block; background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }}
          .footer {{ background:#282246; padding: 20px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }}
          .warning {{ background: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; text-align: center; }}
        </style>
      </head>
      <body>
        <div class="container">
          <div class="header">
            <h1 style="color: white; margin: 0;">Voila!</h1>
          </div>
          <div class="content">
            <h2 style="color: #6EC6CA; text-align: center;">Visible Change Detected</h2>

            {f"""<div class="warning">
                <strong>Rate Limit Reached:</strong> This is the 6th notification this hour.
                Notifications for this URL will be paused for the remainder of the hour.
            </div>""" if is_capped else ""}

            <p style="text-align: center;">Voila! has detected a visible text change on: <br>
                <a href="{url}" style="color: #6EC6CA; text-decoration: none; font-weight: bold;">{url}</a>
            </p>

            <div style="margin: 20px 0; text-align: center; font-size: 12px;">
                <span style="margin-right: 15px;"><span class="added" style="padding: 2px 8px;">+ Added</span></span>
                <span style="margin-right: 15px;"><span class="removed" style="padding: 2px 8px;">- Deleted</span></span>
                <span style="color: #6c757d;">(Changes show as deleted followed by added)</span>
            </div>

            <div class="diff-section">
              <div class="diff-header" style="color: #6EC6CA;">Comparison Snippet:</div>
              <div class="diff-body">
                {diff_html}
              </div>
            </div>

            <div style="text-align: center;">
                <a href="https://tefinitely.com/voila/index.php" class="btn">Go to Dashboard</a>
            </div>
          </div>
          <div class="footer">
            &copy; {current_year} Voila! URL Monitoring Service
          </div>
        </div>
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
        msg = MIMEMultipart('alternative')
        msg['Subject'] = subject
        msg['From'] = AWS_CONFIG['sender_email']
        msg['To'] = to_email
        msg.add_header('Reply-To', AWS_CONFIG['reply_to'])

        msg.attach(MIMEText(body_text, 'plain'))
        msg.attach(MIMEText(body_html, 'html'))

        response = client.send_raw_email(
            Source=AWS_CONFIG['sender_email'],
            Destinations=[to_email],
            RawMessage={'Data': msg.as_string()}
        )
        log(f"Email sent! Message ID: {response['MessageId']}")
    except (BotoCoreError, ClientError, Exception) as e:
        log(f"Error sending email to {to_email}: {e}")

def check_monitors():
    conn = get_db_connection()
    if conn is None:
        return
    cursor = conn.cursor(dictionary=True)

    cursor.execute("""
        SELECT m.id, m.user_id, m.url, m.last_content, m.last_hash, u.email, m.emails_sent_this_hour, m.hour_start_time, m.was_throttled
        FROM monitors m
        JOIN users u ON m.user_id = u.id
        WHERE m.is_paused = 0 AND (
            m.last_checked IS NULL
            OR DATE_ADD(m.last_checked, INTERVAL m.interval_minutes MINUTE) <= NOW()
        )
    """)
    monitors = cursor.fetchall()

    for monitor in monitors:
        try:
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
                if monitor.get('was_throttled') == 1:
                    send_resumption_notification(monitor['email'], monitor['url'])

                emails_sent = 0
                hour_start = now
                cursor.execute("UPDATE monitors SET emails_sent_this_hour = 0, hour_start_time = %s, was_throttled = 0 WHERE id = %s", (hour_start, monitor['id']))

            if monitor['last_hash'] is None:
                cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW() WHERE id = %s", (visible_text, new_hash, monitor['id']))
            elif new_hash != monitor['last_hash']:
                snippets = get_diff_snippets(monitor['last_content'], visible_text, max_snippets=4)
                if snippets: # Only notify if there's an actual difference in text
                    # Generate HTML for the diff
                    diff_html = ""
                    for i, snippet in enumerate(snippets):
                        if i > 0:
                            diff_html += '<hr style="border: 0; border-top: 1px dashed #1e2538; margin: 15px 0;">'

                        for line in snippet.splitlines():
                            if line.startswith('+') and not line.startswith('+++'):
                                diff_html += f'<div class="added">{line}</div>'
                            elif line.startswith('-') and not line.startswith('---'):
                                diff_html += f'<div class="removed">{line}</div>'
                            elif line.startswith('@@'):
                                diff_html += f'<div style="color: #6c757d; font-style: italic;">{line}</div>'
                            else:
                                diff_html += f'<div>{line}</div>'

                    if emails_sent < 6:
                        reached_limit = (emails_sent == 5)
                        send_notification(monitor['email'], monitor['url'], diff_html, reached_limit)

                        throttled_flag = 1 if reached_limit else 0
                        cursor.execute("""
                            UPDATE monitors
                            SET last_content = %s, last_hash = %s, last_checked = NOW(),
                                last_changed = NOW(),
                                emails_sent_this_hour = emails_sent_this_hour + 1,
                                was_throttled = %s
                            WHERE id = %s
                        """, (visible_text, new_hash, throttled_flag, monitor['id']))
                    else:
                        log(f"Email cap reached for {monitor['url']}. Notification skipped.")
                        cursor.execute("UPDATE monitors SET last_content = %s, last_hash = %s, last_checked = NOW(), last_changed = NOW(), was_throttled = 1 WHERE id = %s", (visible_text, new_hash, monitor['id']))
                else:
                    cursor.execute("UPDATE monitors SET last_checked = NOW() WHERE id = %s", (monitor['id'],))
            else:
                cursor.execute("UPDATE monitors SET last_checked = NOW() WHERE id = %s", (monitor['id'],))

            # Commit after each monitor to avoid long-held locks
            conn.commit()
        except Exception as e:
            log(f"Error processing monitor {monitor['id']} ({monitor['url']}): {e}")
            conn.rollback()

    conn.close()

if __name__ == "__main__":
    check_monitors()
