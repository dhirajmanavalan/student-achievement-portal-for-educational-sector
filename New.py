import requests
import mysql.connector
from bs4 import BeautifulSoup
from mysql.connector import Error
from datetime import datetime
import random

# Database config
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
            print("✅ Connected to the database")
        return conn
    except mysql.connector.Error as err:
        print(f"❌ Database connection error: {err}")
        return None

def generate_event_code():
    return f"{random.randint(100, 999)}"

def calculate_status(end_date):
    try:
        end = datetime.strptime(end_date, "%d-%m-%Y")
        return "Active" if end > datetime.now() else "Non-active"
    except:
        return "Non-active"

def estimate_duration(start_date, end_date):
    try:
        start = datetime.strptime(start_date, "%d-%m-%Y")
        end = datetime.strptime(end_date, "%d-%m-%Y")
        return f"{(end - start).days * 8} hrs"
    except:
        return "8 hrs"

def scrape_event_details(event_url):
    response = requests.get(event_url, headers={"User-Agent": "Mozilla/5.0"})
    if response.status_code != 200:
        print(f"❌ Failed to fetch event page: {event_url}")
        return {}

    soup = BeautifulSoup(response.text, "html.parser")
    details = {}

    headers = [
        "About Event", "Events", "PPT Topics", "Event Guests", "Event Caption",
        "Departments", "Accommodation", "Contact Details",
        "Last Dates for Registration", "Registration Fees"
    ]
    for header in headers:
        tag = soup.find("h4", string=lambda s: s and header.lower() in s.lower())
        if tag:
            content = ""
            for sibling in tag.find_next_siblings():
                if sibling.name == "h4":
                    break
                if sibling.name == "p":
                    content += sibling.get_text(strip=True) + "\n"
            details[header] = content.strip() if content else "N/A"
        else:
            details[header] = "N/A"

    dl_section = soup.find("dl")
    if dl_section:
        for dt in dl_section.find_all("dt"):
            key = dt.get_text(strip=True).replace(" :", "").replace(":", "")
            dd = dt.find_next_sibling("dd")
            value = dd.get_text(strip=True) if dd else "N/A"
            details[key] = value

    card_links = {}
    card_body = soup.find("div", class_="card-body")
    if card_body:
        for a in card_body.find_all("a", href=True):
            link_text = a.get_text(strip=True)
            href = a["href"]
            card_links[link_text] = href
    details["Links"] = card_links

    return details

def scrape_knowafest():
    url = "https://www.knowafest.com/explore/upcomingfests"
    headers = {"User-Agent": "Mozilla/5.0"}

    response = requests.get(url, headers=headers)
    if response.status_code != 200:
        print(f"❌ Failed to fetch KnowAFest page. Status: {response.status_code}")
        return []

    soup = BeautifulSoup(response.text, "html.parser")
    events = soup.find_all("tr", {"itemscope": True, "itemtype": "http://schema.org/Event"})

    event_data = []
    for i, event in enumerate(events[:500], start=1):
        try:
            start_date = event.find("td", itemprop="startDate").text.strip()
            end_date_tag = event.find("td", itemprop="endDate")
            end_date = end_date_tag.text.strip() if end_date_tag else start_date
            title_tag = event.find("td", itemprop="name")
            title = title_tag.get_text(strip=True) if title_tag else "N/A"

            event_type_tag = event.find_all("td")[2]
            category = event_type_tag.get_text(strip=True) if event_type_tag else "N/A"

            location_tag = event.find("td", class_="optout")
            if location_tag:
                college = location_tag.find("span", itemprop="name").text.strip()
                address = location_tag.find("span", itemprop="address").text.strip()
                location = f"{college}{address}"
            else:
                location = "N/A"

            onclick_attr = event.get("onclick", "")
            link = "No Link"
            if "window.open" in onclick_attr:
                try:
                    link_part = onclick_attr.split("'")[1]
                    link = f"https://www.knowafest.com/explore/{link_part}"
                except IndexError:
                    print(f"⚠️ Error extracting link from: {onclick_attr}")

            details = scrape_event_details(link) if link != "No Link" else {}

            event_data.append({
                "id": i,
                "max_count": 100,
                "applied_count": random.randint(0, 100),
                "event_code": generate_event_code(),
                "title": title,
                "organizer": details.get("Organizer", "N/A"),
                "registration_link": details.get("Links", {}).get("Register now", link),
                "category": category,
                "status": "Active",
                "start_date": start_date,
                "end_date": end_date,
                "last_date_registration": details.get("Last Dates for Registration", "N/A"),
                "duration": estimate_duration(start_date, end_date),
                "location": location,
                "is_posted": 0,
                "state": address,
                "country": "India",
                "within_bit": "No",
                "department": details.get("Departments", "Nil"),
                "eligible_for_winners": "Yes",
                "winner_awards": "Certificate"
            })

        except Exception as e:
            print(f"⚠️ Error parsing event: {e}")

    print(f"🎯 Found {len(event_data)} events with h4 content")
    return event_data

def save_events_to_db(events):
    conn = connect_db()
    if conn is None:
        return

    try:
        cursor = conn.cursor()
        insert_query = """
            INSERT IGNORE INTO events (
                id, max_count, applied_count, balance_count, event_code,
                title, organizer, registration_link, category, status,
                start_date, end_date, last_date_registration, duration,
                location, is_posted, state, country, within_bit, department,
                eligible_for_winners, winner_awards
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """

        for event in events:
            balance_count = event["max_count"] - event["applied_count"]
            values = (
                event["id"], event["max_count"], event["applied_count"], balance_count,
                event["event_code"], event["title"], event["organizer"], event["registration_link"],
                event["category"], event["status"], event["start_date"], event["end_date"],
                event["last_date_registration"], event["duration"], event["location"],
                event["is_posted"], event["state"], event["country"], event["within_bit"],
                event["department"], event["eligible_for_winners"], event["winner_awards"]
            )
            cursor.execute(insert_query, values)

        conn.commit()
        print(f"✅ Successfully saved {len(events)} event(s) to the database.")
    except Error as e:
        print(f"❌ DB error: {e}")
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()

if __name__ == "__main__":
    print("\n🔍 Scraping KnowAFest events with h4 topics only...")
    events = scrape_knowafest()
    if events:
        save_events_to_db(events)
    else:
        print("⚠️ No matching events found.")
