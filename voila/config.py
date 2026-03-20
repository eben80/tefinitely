import mysql.connector
import os

DB_CONFIG = {
    'host': 'localhost',
    'database': 'url_monitor',
    'user': 'root',
    'password': ''
}

# AWS SES Configuration
AWS_CONFIG = {
    'aws_access_key_id': 'YOUR_AWS_ACCESS_KEY',
    'aws_secret_access_key': 'YOUR_AWS_SECRET_KEY',
    'region_name': 'YOUR_AWS_REGION',
    'sender_email': 'support@tefinitely.com',
    'reply_to': 'tefinitely@gmail.com'
}

def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)
