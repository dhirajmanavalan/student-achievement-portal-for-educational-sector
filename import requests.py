import requests
import mysql.connector
from bs4 import BeautifulSoup
from mysql.connector import Error
import datetime
import logging
import os
import sys
import time

# Set up logging configuration
log_directory = "scraper_logs"
if not os.path.exists(log_directory):
    os.makedirs(log_directory)

log_filename = os.path.join(log_directory, f"scraper_{datetime.datetime.now().strftime('%Y%m%d_%H%M%S')}.log")

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(log_filename),
        logging.StreamHandler(sys.stdout)
    ]
)

logger = logging.getLogger("event_scraper")

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
        logger.info("Attempting to connect to database...")
        conn = mysql.connector.connect(**db_config)
        if conn.is_connected():
            logger.info("✅ Connected to the database successfully")
            db_info = conn.get_server_info()
            logger.info(f"Server version: {db_info}")

            cursor = conn.cursor()
            cursor.execute("SELECT DATABASE()")
            db_name = cursor.fetchone()[0]
            logger.info(f"Connected to database: {db_name}")
            cursor.close()
        return conn
    except mysql.connector.Error as err:
        logger.error(f"❌ Database connection error: {err}")
        return None


def scrape_event_details(event_url):
    logger.info(f"Scraping details from: {event_url}")
    try:
        start_time = time.time()
        response = requests.get(event_url, headers={"User-Agent": "Mozilla/5.0"}, timeout=30)
        request_time = time.time() - start_time

        logger.info(f"Request completed in {request_time:.2f} seconds with status code: {response.status_code}")

        if response.status_code != 200:
            logger.error(f"❌ Failed to fetch event page: {event_url}")
            return {}

        soup = BeautifulSoup(response.text, "html.parser")
        details = {}

        headers = [
            "About Event", "Events", "PPT Topics", "Event Guests", "Event Caption",
            "Departments", "Accommodation", "Contact Details",
            "Last Dates for Registration", "Registration Fees"
        ]

        found_headers = []
        for header in headers:
            tag = soup.find("h4", string=lambda s: s and header.lower() in s.lower())
            if tag:
                found_headers.append(header)
                content = ""
                for sibling in tag.find_next_siblings():
                    if sibling.name == "h4":
                        break
                    if sibling.name == "p":
                        content += sibling.get_text(strip=True) + "\n"
                details[header] = content.strip() if content else "N/A"
            else:
                details[header] = "N/A"

        logger.info(f"Found {len(found_headers)} headers: {', '.join(found_headers) if found_headers else 'None'}")

        # Extract definition list items
        dl_section = soup.find("dl")
        dl_items = 0
        if dl_section:
            for dt in dl_section.find_all("dt"):
                key = dt.get_text(strip=True).replace(" :", "").replace(":", "")
                dd = dt.find_next_sibling("dd")
                value = dd.get_text(strip=True) if dd else "N/A"
                details[key] = value
                dl_items += 1

        logger.info(f"Extracted {dl_items} definition list items")

        # Extract links
        card_links = {}
        card_body = soup.find("div", class_="card-body")
        link_count = 0
        if card_body:
            for a in card_body.find_all("a", href=True):
                link_text = a.get_text(strip=True)
                href = a["href"]
                card_links[link_text] = href
                link_count += 1
        details["Links"] = card_links

        logger.info(f"Found {link_count} links in card body")
        logger.info(f"Successfully scraped details from {event_url}")

        return details
    except Exception as e:
        logger.error(f"Error during event detail scraping: {str(e)}")
        return {}


def scrape_knowafest():
    logger.info("Starting KnowAFest scraping process...")
    url = "https://www.knowafest.com/explore/upcomingfests"
    headers = {"User-Agent": "Mozilla/5.0"}

    try:
        logger.info(f"Fetching main page: {url}")
        start_time = time.time()
        response = requests.get(url, headers=headers, timeout=30)
        request_time = time.time() - start_time

        logger.info(
            f"Main page request completed in {request_time:.2f} seconds with status code: {response.status_code}")

        if response.status_code != 200:
            logger.error(f"❌ Failed to fetch KnowAFest page. Status: {response.status_code}")
            return []

        soup = BeautifulSoup(response.text, "html.parser")
        events = soup.find_all("tr", {"itemscope": True, "itemtype": "http://schema.org/Event"})
        logger.info(f"Found {len(events)} event entries on the main page")

        event_data = []
        processed = 0
        failures = 0

        for index, event in enumerate(events[:500]):
            try:
                logger.info(f"Processing event {index + 1}/{min(500, len(events))}...")

                # Extract start date
                start_date_tag = event.find("td", itemprop="startDate")
                if not start_date_tag:
                    logger.warning(f"Event {index + 1}: No start date found, skipping")
                    failures += 1
                    continue

                start_date = start_date_tag.text.strip()
                logger.info(f"Event {index + 1}: Start date: {start_date}")

                # Extract end date
                end_date_tag = event.find("td", itemprop="endDate")
                end_date = end_date_tag.text.strip() if end_date_tag else start_date
                logger.info(f"Event {index + 1}: End date: {end_date}")

                # Calculate duration in days
                try:
                    start = datetime.datetime.strptime(start_date, "%Y-%m-%d")
                    end = datetime.datetime.strptime(end_date, "%Y-%m-%d")
                    duration = (end - start).days + 1  # Including both start and end days
                    logger.info(f"Event {index + 1}: Duration: {duration} days")
                except Exception as e:
                    duration = 1  # Default if dates cannot be parsed
                    logger.warning(f"Event {index + 1}: Could not calculate duration, using default. Error: {str(e)}")

                # Extract title
                title_tag = event.find("td", itemprop="name")
                title = title_tag.get_text(strip=True) if title_tag else "N/A"
                logger.info(f"Event {index + 1}: Title: {title}")

                # Extract event type
                event_type_tag = event.find_all("td")[2] if len(event.find_all("td")) > 2 else None
                event_type = event_type_tag.get_text(strip=True) if event_type_tag else "N/A"
                logger.info(f"Event {index + 1}: Event type: {event_type}")

                # Extract location
                location_tag = event.find("td", class_="optout")
                if location_tag:
                    college_tag = location_tag.find("span", itemprop="name")
                    college = college_tag.text.strip() if college_tag else "N/A"

                    address_tag = location_tag.find("span", itemprop="address")
                    address = address_tag.text.strip() if address_tag else "N/A"

                    location = f"{college}, {address}"
                    logger.info(f"Event {index + 1}: Location: {location}")

                    # Extract state and country from address if possible
                    address_parts = address.split(',')
                    state = address_parts[-2].strip() if len(address_parts) >= 2 else "Unknown"
                    country = address_parts[-1].strip() if len(address_parts) >= 1 else "Unknown"
                    logger.info(f"Event {index + 1}: State: {state}, Country: {country}")
                else:
                    location = "N/A"
                    college = "N/A"
                    state = "Unknown"
                    country = "Unknown"
                    logger.warning(f"Event {index + 1}: No location information found")

                # Extract link
                onclick_attr = event.get("onclick", "")
                link = "No Link"
                if "window.open" in onclick_attr:
                    try:
                        link_part = onclick_attr.split("'")[1]
                        link = f"https://www.knowafest.com/explore/{link_part}"
                        logger.info(f"Event {index + 1}: Link: {link}")
                    except IndexError:
                        logger.warning(f"Event {index + 1}: Error extracting link from: {onclick_attr}")

                # Get detailed event information
                if link != "No Link":
                    logger.info(f"Event {index + 1}: Fetching detailed event information...")
                    event_details = scrape_event_details(link)
                    logger.info(f"Event {index + 1}: Found {len(event_details)} detail fields")
                else:
                    logger.warning(f"Event {index + 1}: No link available for detailed information")
                    event_details = {}

                # Generate a unique event_code
                event_code = f"EVT-{hash(title) % 10000:04d}"
                logger.info(f"Event {index + 1}: Generated event code: {event_code}")

                # Default values for new fields
                is_within_bit = "No"  # Default assumption
                related_to_special_lab = "N/A"
                competition_name = title  # Use event title as default competition name
                total_competition_levels = "1"  # Default level
                event_level = "College"  # Default level assumption
                status = "Active"  # Default status
                eligible_for_rewards = "No"  # Default value
                winner_rewards = "N/A"
                participation_rewards = "N/A"

                # Extract departments - could be used to determine if related to special lab
                departments = event_details.get("Departments", "N/A")

                # Additional event details
                event_data.append((
                    title,
                    0,  # max_count (default)
                    0,  # applied_count (default)
                    0,  # balance_count (default)
                    event_code,
                    title,  # event_name
                    college,  # event_organizer
                    link,  # web_link
                    event_type,  # event_category
                    status,
                    start_date,
                    end_date,
                    duration,
                    location,  # event_location
                    event_level,
                    state,
                    country,
                    is_within_bit,
                    related_to_special_lab,
                    departments,
                    competition_name,
                    total_competition_levels,
                    eligible_for_rewards,
                    winner_rewards,
                    participation_rewards,
                    event_details
                ))

                processed += 1
                logger.info(f"Event {index + 1}: Successfully processed")

            except Exception as e:
                failures += 1
                logger.error(f"⚠ Error parsing event {index + 1}: {str(e)}")

        success_rate = (processed / (processed + failures)) * 100 if (processed + failures) > 0 else 0
        logger.info(f"🎯 Scraping completed: Found {len(event_data)} events")
        logger.info(f"📊 Processing stats: {processed} successful, {failures} failed, {success_rate:.1f}% success rate")

        return event_data

    except Exception as e:
        logger.error(f"❌ Critical error during scraping: {str(e)}")
        return []


def save_events_to_db(events):
    logger.info(f"Starting database save process for {len(events)} events...")
    conn = connect_db()
    if conn is None:
        logger.error("Cannot proceed with saving - database connection failed")
        return

    try:
        cursor = conn.cursor()

        # First, create or update the table structure if needed
        logger.info("Ensuring database table structure is up to date...")
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255),
                max_count INT DEFAULT 0,
                applied_count INT DEFAULT 0,
                balance_count INT DEFAULT 0,
                event_code VARCHAR(20),
                event_name VARCHAR(255),
                organizer VARCHAR(255),
                link VARCHAR(512),
                category VARCHAR(100),
                status VARCHAR(20) DEFAULT 'Active',
                start_date VARCHAR(50),
                end_date VARCHAR(50),
                duration INT DEFAULT 1,
                location VARCHAR(255),
                event_level VARCHAR(50),
                state VARCHAR(100),
                country VARCHAR(100),
                within_bit VARCHAR(5) DEFAULT 'No',
                related_to_special_lab VARCHAR(255),
                departments TEXT,
                competition_name VARCHAR(255),
                total_competition_levels VARCHAR(20),
                eligible_for_winners VARCHAR(5) DEFAULT 'No',
                winner_awards TEXT,
                participation_rewards TEXT,

                about_event TEXT,
                event_list TEXT,
                ppt_topics TEXT,
                event_guests TEXT,
                event_caption TEXT,
                accommodation TEXT,
                contact_details TEXT,
                last_date_registration VARCHAR(100),
                registration_fees VARCHAR(100),
                events TEXT,
                event_type VARCHAR(100),
                eligibility TEXT,
                registration_link VARCHAR(512),
                is_posted TINYINT DEFAULT 0
            )
        """)
        logger.info("Database structure check completed")

        insert_query = """
            INSERT IGNORE INTO events (
                title, max_count, applied_count, balance_count, event_code, event_name, 
                organizer, link, category, status, start_date, end_date, duration,
                location, event_level, state, country, within_bit, related_to_special_lab,
                departments, competition_name, total_competition_levels, eligible_for_winners,
                winner_awards, participation_rewards,

                about_event, event_list, ppt_topics, event_guests, event_caption,
                accommodation, contact_details, last_date_registration,
                registration_fees, events, event_type, eligibility, registration_link
            )
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """

        count = 0
        failed = 0

        for index, event in enumerate(events):
            try:
                logger.info(f"Saving event {index + 1}/{len(events)}: {event[0]}")

                (
                    title, max_count, applied_count, balance_count, event_code, event_name,
                    organizer, link, category, status, start_date, end_date, duration,
                    location, event_level, state, country, within_bit, related_to_special_lab,
                    departments, competition_name, total_competition_levels, eligible_for_rewards,
                    winner_rewards, participation_rewards, details
                ) = event

                about_event = details.get("About Event", "")
                event_list = details.get("Events", "")
                ppt_topics = details.get("PPT Topics", "")
                event_guests = details.get("Event Guests", "")
                event_caption = details.get("Event Caption", "")
                accommodation = details.get("Accommodation", "")
                contact_details = details.get("Contact Details", "")
                last_date_registration = details.get("Last Dates for Registration", "")
                registration_fees = details.get("Registration Fees", "")
                events_detail = details.get("Events", "")
                event_type = details.get("Event Type", category)  # Fallback to category
                eligibility = departments
                registration_link = details.get("Links", {}).get("Register now", "")

                cursor.execute(insert_query, (
                    title, max_count, applied_count, balance_count, event_code, event_name,
                    organizer, link, category, status, start_date, end_date, duration,
                    location, event_level, state, country, within_bit, related_to_special_lab,
                    departments, competition_name, total_competition_levels, eligible_for_rewards,
                    winner_rewards, participation_rewards,

                    about_event, event_list, ppt_topics, event_guests, event_caption,
                    accommodation, contact_details, last_date_registration,
                    registration_fees, events_detail, event_type, eligibility, registration_link
                ))

                logger.info(f"Event {index + 1}: Successfully saved to database")
                count += 1

            except Error as e:
                failed += 1
                logger.error(f"❌ Database error for event {index + 1}: {str(e)}")

        conn.commit()
        save_success_rate = (count / len(events)) * 100 if len(events) > 0 else 0

        logger.info(f"✅ Database commit successful")
        logger.info(f"✅ Database stats: {count} events saved, {failed} failed, {save_success_rate:.1f}% success rate")
    except Error as e:
        logger.error(f"❌ Critical database error: {str(e)}")
    finally:
        if conn.is_connected():
            cursor.close()
            conn.close()
            logger.info("Database connection closed")


if __name__ == "__main__":
    logger.info("=" * 50)
    logger.info("EVENT SCRAPER STARTED")
    logger.info("=" * 50)

    start_time = time.time()

    logger.info("\n🔍 Scraping KnowAFest events...")
    events = scrape_knowafest()

    if events:
        logger.info(f"Found {len(events)} events, proceeding to save to database...")
        save_events_to_db(events)
    else:
        logger.warning("⚠ No events found to save.")

    total_time = time.time() - start_time
    logger.info(f"Total execution time: {total_time:.2f} seconds")
    logger.info("=" * 50)
    logger.info("EVENT SCRAPER FINISHED")
    logger.info("=" * 50)