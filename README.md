# 🎓 Student Achievement Portal

A full-stack web application that allows students to upload their academic and non-academic achievements and enables admins to manage the submissions. The system also supports future integration of automated event scraping from reputed sources.

---

## ✅ Project Overview

This portal helps streamline the process of:
- Uploading student achievements
- Verifying and approving events
- Managing and exporting event data

---

## 🎯 Features

- Student achievement upload form with certificate attachment
- Admin login system with dashboard access
- File upload support (PDF, JPG, PNG)
- Responsive design for mobile & desktop
- Future-ready for event scraping and AI-based scoring

---

## 💻 Technologies Used

| Layer       | Technologies                        |
|-------------|-------------------------------------|
| Frontend    | HTML5, CSS3, JavaScript, Bootstrap  |
| Backend     | PHP 7+, MySQL, Apache (XAMPP/WAMP)  |
| Web Scraping| Python, BeautifulSoup, Requests *(optional for enhancement)* |

---

## 🖥️ Frontend

**Student Interface:**
- Form to enter: Name, Roll No, Event Name, Type, Date, Abstract
- Certificate upload field (accepts PDF/JPG/PNG)
- Validations for required fields
- Mobile-friendly UI

**Admin Interface:**
- Styled login page using Bootstrap
- Inputs for admin username and password
- Redirects to dashboard after login

---

## ⚙️ Backend

- `upload.php`: Receives student data and certificate, stores or processes it
- `admin_dashboard.php`: Processes admin login and displays records
- Secure form handling using `POST` method
- Folder structure for saving uploaded files
- (Optional) MySQL database connection for storing and retrieving records

---

## 🔍 Web Scraping (Optional Module)

You can enhance this system by adding a Python script using:
- `BeautifulSoup` and `Requests` to extract events from websites like **KnowAFest**, **Unstop**
- Save scraped data into the database
- Display verified events on the frontend

---

## ⚙️ How to Run the Project

1. **Install XAMPP or WAMP** (for Apache + PHP + MySQL)
2. Copy the project folder into `htdocs` (XAMPP) or `www` (WAMP)
3. Start Apache (and MySQL if using DB)
4. Access the app in your browser:  
   `http://localhost/your-folder/index.html`
5. Run scraper (optional):  
   ```bash
   python scraper.py
   Absolutely! Here's your complete and professional README.md file for the Student Achievement Portal project, covering:

✅ Overview

🎯 Features

💻 Technologies

🖥️ Frontend

⚙️ Backend

🔍 Web Scraping (if used/optional)

⚙️ Setup Instructions

📌 Future Scope

👥 Team



---

# 🎓 Student Achievement Portal

A full-stack web application that allows students to upload their academic and non-academic achievements and enables admins to manage the submissions. The system also supports future integration of automated event scraping from reputed sources.

---

## ✅ Project Overview

This portal helps streamline the process of:
- Uploading student achievements
- Verifying and approving events
- Managing and exporting event data

---

## 🎯 Features

- Student achievement upload form with certificate attachment
- Admin login system with dashboard access
- File upload support (PDF, JPG, PNG)
- Responsive design for mobile & desktop
- Future-ready for event scraping and AI-based scoring

---

## 💻 Technologies Used

| Layer       | Technologies                        |
|-------------|-------------------------------------|
| Frontend    | HTML5, CSS3, JavaScript, Bootstrap  |
| Backend     | PHP 7+, MySQL, Apache (XAMPP/WAMP)  |
| Web Scraping| Python, BeautifulSoup, Requests *(optional for enhancement)* |

---

## 🖥️ Frontend

**Student Interface:**
- Form to enter: Name, Roll No, Event Name, Type, Date, Abstract
- Certificate upload field (accepts PDF/JPG/PNG)
- Validations for required fields
- Mobile-friendly UI

**Admin Interface:**
- Styled login page using Bootstrap
- Inputs for admin username and password
- Redirects to dashboard after login

---

## ⚙️ Backend

- `upload.php`: Receives student data and certificate, stores or processes it
- `admin_dashboard.php`: Processes admin login and displays records
- Secure form handling using `POST` method
- Folder structure for saving uploaded files
- (Optional) MySQL database connection for storing and retrieving records

---

## 🔍 Web Scraping (Optional Module)

You can enhance this system by adding a Python script using:
- `BeautifulSoup` and `Requests` to extract events from websites like **KnowAFest**, **Unstop**
- Save scraped data into the database
- Display verified events on the frontend

---

## ⚙️ How to Run the Project

1. **Install XAMPP or WAMP** (for Apache + PHP + MySQL)
2. Copy the project folder into `htdocs` (XAMPP) or `www` (WAMP)
3. Start Apache (and MySQL if using DB)
4. Access the app in your browser:  
   `http://localhost/your-folder/index.html`
5. Run scraper (optional):  
   ```bash
   python scraper.py


---

🛠️ Folder Structure

/project-folder
│
├── index.html               # Student form
├── admin_login.html         # Admin login
├── upload.php               # Handles student form
├── admin_dashboard.php      # Handles admin functions
├── style.css                # Styling
├── script.js                # Optional validations
├── uploads/                 # Folder to store certificates
└── scraper.py               # Python scraper (optional)


---

🚀 Future Scope

Integrate MySQL database for storing all submissions

Add email confirmation or OTP for students

Implement AI-based event scoring and filtering

Build an Android/iOS mobile version

Create a full admin dashboard with filters and export options



---

👥 Team Members

Dhirajkumar M

Inbashree G

Kavipriya D

Madhumitha K



---

📄 License

This project is part of a final semester academic submission and is open for educational use.


---

> "From form to function — this portal is built to make student achievements recognized and record-keeping effortless."


