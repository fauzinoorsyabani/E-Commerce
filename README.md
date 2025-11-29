# SQL Injection Demonstration, Forensic Logging, and Security Analysis on eCommerce Website (PHP)

## 1. Introduction

This repository contains a modified version of an open-source PHP eCommerce Website, adapted specifically for academic and cybersecurity analysis. The purpose of this project is to identify, exploit, and analyze SQL Injection vulnerabilities found in the system, while implementing a complete forensic logging mechanism to track attack patterns and authentication behavior.

The final objective includes:
- Demonstrating SQL Injection attacks.
- Capturing detailed logs of attempted and successful intrusions.
- Enhancing security visibility through an admin-based log viewer.
- Generating PDF-based forensic reports.
- Providing structured documentation suitable for academic submission.

This project is used strictly for educational purposes.

---

## 2. Project Overview

The base application is a fully functional eCommerce website built using:

- PHP (Backend)
- MySQL (Database)
- HTML/CSS (Frontend)
- Bootstrap
- JavaScript / jQuery

The website includes customer-facing pages and a complete admin panel containing product management, order tracking, category management, shipping settings, profile management, and other eCommerce functionalities.

This modified version includes additional components for security testing.

---

## 3. SQL Injection Vulnerability Analysis

### 3.1 Admin Login Vulnerability

The original login mechanism used raw string concatenation:

```php
$query = "SELECT * FROM tbl_user WHERE email='$email' AND status='Active'";
```

Issues identified:
- No input sanitization.
- No prepared statements or parameter binding.
- Password validation bypassed due to insecure query construction.

### 3.2 Customer Login Vulnerability

Similar pattern found in customer authentication:

```php
$query = "SELECT * FROM tbl_customer WHERE cust_email='$email'";
```

This allows direct SQL Injection through the email field.

### 3.3 Example SQL Injection Payloads

To bypass admin login:

```
' OR 1=1 --
```

or

```
' OR 1=1 LIMIT 1 OFFSET 0 -- 
```

The system grants access without validating the password.

---

## 4. Exploitation Impact

By exploiting SQL Injection:
1. Attackers can gain unauthorized access to the admin panel.
2. Session data is created improperly.
3. The system executes raw SQL without validation.
4. Attackers can impersonate any registered user.
5. Database content exposure may occur depending on payload complexity.

All these events are recorded using the custom logging system for forensic purposes.

---

## 5. Forensic Logging System

A dedicated logging system was implemented to capture:
- Timestamp
- IP Address
- Login Attempt Type
- SQL Query used (RAW SQL)
- Success or Failure
- Suspicious Inputs

### 5.1 Logger for Admin Login

A function named `write_log()` records activity:

```php
$file = dirname(__DIR__) . "/inc/logs/security.log";
$log  = "[$time] [IP:$ip] $event";
file_put_contents($file, $log, FILE_APPEND);
```

### 5.2 Logger for Customer Login

A separate logger exists in:

```
inc/logger.php
```

Both logging systems generate entries following a unified format.

---

## 6. Security Log Viewer (Admin Panel)

A new page was integrated:

```
admin/logs.php
```

### Features:
- Fully structured log parsing
- Column-based filters:
  - Timestamp
  - IP Address
  - Status (Success, Failed, Attempt)
  - Details
- Search and sorting using DataTables
- Download raw log file
- Clear log file
- Export forensic report as PDF

This tool allows administrators to analyze attacks directly from the dashboard.

---

## 7. PDF Report Generation

A script named:

```
admin/logs-pdf.php
```

Automatically generates a professional forensic report that includes:
- Cover page
- Summary
- Full log table
- Timestamped evidence entries

This is used for academic documentation or incident reporting.

---

## 8. Directory Structure (Modified Files)

```
/admin
    login.php               ← Vulnerable login + admin logger
    logs.php                ← Log viewer with DataTables
    logs-pdf.php            ← PDF generator

/inc
    logger.php              ← Customer login logger
    config.php              ← Database configuration
    functions.php           ← Utility functions
    /logs
        security.log        ← Auto-generated forensic log

/database
    ecommerceweb.sql        ← Main database file
```

---

## 9. Demonstration Steps

### 9.1 Performing SQL Injection
1. Navigate to the admin login page:
   ```
   /admin/login.php
   ```
2. Enter payload:
   ```
   ' OR 1=1 --
   ```
3. Leave password empty.
4. Submit.
5. Unauthorized admin access will be granted.
6. System logs will record this event.

### 9.2 Viewing Logs
Open:

```
/admin/logs.php
```

### 9.3 Generating PDF Report
Open:

```
/admin/logs-pdf.php
```

---

## 10. Mitigation Recommendations

After testing, the system should be secured by:
- Replacing all queries with PDO prepared statements.
- Validating inputs using server-side sanitization.
- Removing unnecessary SQLi testing bypasses.
- Hashing passwords using `password_hash()`.
- Implementing rate limiting on login endpoints.
- Moving log files outside web-accessible directories.

---

## 11. Installation Guide

Follow these steps to run the system:

1. Install Apache and MySQL (XAMPP, Laragon, or WAMP).
2. Clone the repository into your web server directory.
3. Import the database file (`ecommerceweb.sql`) into phpMyAdmin.
4. Configure database credentials in:
   ```
   inc/config.php
   ```
5. Access the application via:
   ```
   http://localhost/eCommerce-website-in-PHP/
   ```

---

## 12. Disclaimer

This project is intended for cybersecurity training and academic research only.  
It must not be used in a production environment without security hardening.  
Unauthorized exploitation of live systems is strictly prohibited.

---

# 13. Academic Submission Information
This repository is prepared as part of SQL Injection Analysis and Digital Forensic coursework at Universitas Siliwangi (Sistem Informasi).

The objectives are:
1. Identify SQLi vulnerability
2. Execute SQL Injection attack
3. Capture forensic logs
4. Analyze authentication tampering
5. Produce structured incident report (PDF)

---

## 14. Author

Fauzi Noorsyabani
Information Systems 
Universitas Siliwangi  
2025

