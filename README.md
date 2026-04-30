# 🎓 Cainta Scholarship Management System

> A Web-Based Scholarship Management System with Automated Application Processing for the Cainta Scholarship Program — Municipality of Cainta, Rizal

---

## 📋 Table of Contents

- [About the Project](#about-the-project)
- [Features](#features)
- [User Roles](#user-roles)
- [Technologies Used](#technologies-used)
- [Project Structure](#project-structure)
- [Installation & Setup](#installation--setup)
- [Default Login Credentials](#default-login-credentials)
- [Screenshots](#screenshots)
- [Team Members](#team-members)

---

## 📖 About the Project

The **Cainta Scholarship Management System** is a web-based platform developed for the Municipality of Cainta to modernize and automate the management of their scholarship program. It replaces the manual, spreadsheet-based processes previously used by the Scholarship Office.

The system provides a centralized digital platform for students to submit applications online, upload required documents, and track their application status in real time — while administrators, officers, and cashiers manage the entire scholarship lifecycle from a dedicated dashboard.

---

## ✅ Features

### Student Portal
- Account registration with personal, academic, and family details
- Online scholarship application form with auto-filled information from registration
- Document upload (Grade Slip, Enrollment Receipt, Enrollment Form)
- Real-time application status tracking (Pending, For Review, Approved, Rejected)
- Re-application support for next semester after approval
- Disbursement history viewing
- AI-powered chatbot for FAQs and guidance

### Admin Dashboard
- Overview stats: Total Scholars, Pending Applications, Total Disbursed
- Scholar management: Add, Edit, Archive, Restore, Delete
- Application management: Review, Approve, Reject, Mark For Review/Incomplete
- Automated email notifications on status changes
- Disbursement management: Add, Release, Track per scholar per semester
- Reports: Applications by Status, by Barangay, Disbursement Report, Print function
- User management: Add/Edit/Delete staff accounts with role assignment
- Archived scholars management with restore functionality

### Officer Dashboard
- View and filter all incoming applications
- Verify uploaded student documents
- Update application status with remarks
- Automated email notifications triggered on status updates

### Cashier Counter
- Scholar lookup by name (approved scholars only)
- View pending disbursements per scholar
- Release allowance with confirmation dialog
- Auto-record transaction with timestamp and cashier name
- View full disbursement history

---

## 👥 User Roles

| Role | Access Level | Key Actions |
|------|-------------|-------------|
| **Student** | Student portal only | Register, Apply, Upload docs, Track status, View disbursements |
| **Admin** | Full system access | Manage scholars, Review apps, Disbursements, Reports, User management |
| **Officer** | Applications only | View apps, Verify docs, Update status, Add remarks |
| **Cashier** | Disbursements only | Look up scholars, Release allowances, View transactions |

---

## 🛠 Technologies Used

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons |
| Backend | PHP 8.x |
| Database | MySQL |
| Local Server | XAMPP (Apache + MySQL) |
| AI Chatbot | Anthropic Claude API |
| Email | PHPMailer (Gmail SMTP) |

---

## 📁 Project Structure

```
cainta_scholarship/
├── admin/
│   ├── dashboard.php
│   ├── scholars.php
│   ├── archived_scholars.php
│   ├── applications.php
│   ├── disbursements.php
│   ├── reports.php
│   ├── users.php
│   ├── add_scholar.php
│   ├── edit_scholar.php
│   └── get_documents.php
├── cashier/
│   └── dashboard.php
├── officer/
│   └── dashboard.php
├── student/
│   ├── dashboard.php
│   ├── application.php
│   ├── disbursements.php
│   ├── status.php
│   └── uploads/
├── includes/
│   └── db.php
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── login.php
├── login_process.php
├── logout.php
├── student_login.php
├── student_login_process.php
├── student_logout.php
├── student_register.php
├── chatbot.php
├── chatbot_widget.php
├── mailer.php
├── database.sql
└── README.md
```

---

## ⚙️ Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any Apache + MySQL + PHP environment)
- PHP 8.0 or higher
- MySQL 5.7 or higher
- A modern web browser

### Steps

**1. Clone or download the project**
```bash
git clone https://github.com/your-username/cainta_scholarship.git
```
Or download and extract the ZIP file.

**2. Move to your server's web root**
```
C:/xampp/htdocs/cainta_scholarship/
```

**3. Import the database**
- Open your browser and go to `http://localhost/phpmyadmin`
- Create a new database named `cainta_scholarship`
- Click **Import** and select the `database.sql` file from the project root
- Click **Go**

**4. Configure the database connection**

Open `includes/db.php` and update if needed:
```php
$host = 'localhost';
$dbname = 'cainta_scholarship';
$username = 'root';
$password = '';
```

**5. Configure email notifications (optional)**

Open `mailer.php` and update with your Gmail credentials:
```php
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
```
> Note: Use a Gmail App Password, not your regular password.

**6. Start XAMPP**
- Start **Apache** and **MySQL** in the XAMPP Control Panel

**7. Open the application**
- Student portal: `http://localhost/cainta_scholarship/student_login.php`
- Staff portal: `http://localhost/cainta_scholarship/login.php`

---

## 🔐 Default Login Credentials

### Staff (Admin)
| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `password` |

> ⚠️ Change the default password immediately after first login.

---

## 📸 Screenshots

> *(Add screenshots of your system here)*

| Page | Description |
|------|-------------|
| Student Login | `screenshots/student_login.png` |
| Student Dashboard | `screenshots/student_dashboard.png` |
| Application Form | `screenshots/application_form.png` |
| Admin Dashboard | `screenshots/admin_dashboard.png` |
| Scholars Management | `screenshots/scholars.png` |
| Applications Review | `screenshots/applications.png` |
| Disbursements | `screenshots/disbursements.png` |
| Reports | `screenshots/reports.png` |
| Cashier Counter | `screenshots/cashier.png` |

---

## 👨‍💻 Team Members

| Name | Role |
|------|------|
| Diaz, Nathaniel | Developer |
| Esteban, Chezter John | research/Papers |
| Lobramonte, Jazzel | Research/Papers |
| Villar, James Brian | Developer |

**Adviser:** Mr. Guerrero, Norman
**Institution:** STI College Ortigas-Cainta
**Program:** Bachelor of Science in Information Technology
**Capstone Project:** 2025–2026

---

## 📄 License

This project was developed as a Capstone Project for STI College Ortigas-Cainta. All rights reserved by the Municipality of Cainta Scholarship Office and the project team.

---

<p align="center">
  Made with ❤️ for the Municipality of Cainta, Rizal
</p>
