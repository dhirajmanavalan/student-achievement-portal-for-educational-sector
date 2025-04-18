import requests
from bs4 import BeautifulSoup
import mysql.connector
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
import time

# Database Connection
def connect_db():
    return mysql.connector.connect(
        host="localhost", user="root", password="", database="events_db", port=3336
    )

# Function to scrape Knowafest
def scrape_knowafest():
    url = "https://www.knowafest.com/explore/fest-type/online"
    headers = {"User-Agent": "Mozilla/5.0"}
    response = requests.get(url, headers=headers)
    soup = BeautifulSoup(response.text, "html.parser")
    
    events = []
    for event in soup.find_all("div", class_="event-title"):
        title = event.text.strip()
        link = event.find("a")["href"]
        events.append((title, link))
    return events

# Function to scrape Unstop (JavaScript Rendered)
def scrape_unstop():
    options = webdriver.ChromeOptions()
    options.add_argument("--headless")
    driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=options)
    driver.get("https://unstop.com/")
    time.sleep(5)  # Wait for JavaScript to load
    
    events = []
    for event in driver.find_elements(By.CLASS_NAME, "event-title"):
        title = event.text
        link = event.find_element(By.TAG_NAME, "a").get_attribute("href")
        events.append((title, link))
    driver.quit()
    return events

# Function to store events in MySQL
def store_events(events, source):
    conn = connect_db()
    cursor = conn.cursor()
    query = "INSERT INTO events (title, registration_link, source, status) VALUES (%s, %s, %s, 'Pending')"
    for event in events:
        cursor.execute(query, (event[0], event[1], source))
    conn.commit()
    cursor.close()
    conn.close()

if __name__ == "__main__":
    knowafest_events = scrape_knowafest()
    store_events(knowafest_events, "Knowafest")
    
    unstop_events = scrape_unstop()
    store_events(unstop_events, "Unstop")
    print(store_events)
    print("Scraping Completed and Data Stored!")
