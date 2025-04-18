import time
import requests
import flask
from flask import Flask, request, jsonify, render_template, redirect, url_for, session, flash
import mysql.connector
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager
import re
import urllib.parse

app = Flask(__name__)
app.secret_key = 'your_secret_key'

db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'events_db',
    'port': 3306
}

def connect_db():
    try:
        conn = mysql.connector.connect(**db_config)
        if conn.is_connected():
            app.logger.debug("Connected to the database")
        return conn
    except mysql.connector.Error as err:
        app.logger.error(f"Database connection error: {err}")
        return None

def create_slug(text):
    return urllib.parse.quote(text.lower().replace(' ', '-').replace('.', ''))

def scrape_unstop():
    options = webdriver.ChromeOptions()
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--window-size=1920,1080")

    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)

    driver.get("https://unstop.com/college-fests")
    time.sleep(8)  # Allow time for the page to load

    event_data = []
    events = driver.find_elements(By.CLASS_NAME, "single_profile")

    for event in events[:40]:  # Limit to the first 40 events
        try:
            title = event.find_element(By.TAG_NAME, "h2").text.strip()
            college = event.find_element(By.TAG_NAME, "p").text.strip()

            # Extract event ID from the 'id' attribute
            event_id = re.search(r"opp_(\d+)", event.get_attribute("id")).group(1)

            # Construct event URL
            event_url = f"https://unstop.com/college-fests/{create_slug(title)}-{create_slug(college)}-{event_id}"

            event_data.append((title, "N/A", college, "Online", "N/A", event_url))

        except Exception as e:
            print(f"⚠️ Error parsing Unstop event: {e}")

    driver.quit()
    return event_data

def save_to_db(events):
    db = connect_db()
    if not db:
        print("❌ Database connection failed. Data not saved.")
        return

    cursor = db.cursor()

    sql = """
    INSERT IGNORE INTO events (title, date, college, location, description, link)
    VALUES (%s, %s, %s, %s, %s, %s)
    """

    cursor.executemany(sql, events)
    db.commit()
    print(f"✅ {cursor.rowcount} Unstop events added to DB (duplicates skipped).")

    cursor.close()
    db.close()

if __name__ == "__main__":
    print("\n🎉 Scraping Unstop Events...")
    events = scrape_unstop()
    print(events)
    if events:
        save_to_db(events)
    else:
        print("⚠️ No events found!")
