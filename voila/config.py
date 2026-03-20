import mysql.connector
import os

DB_CONFIG = {
    'host': 'localhost',
    'database': 'url_monitor',
    'user': 'root',
    'password': ''
}

def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)
