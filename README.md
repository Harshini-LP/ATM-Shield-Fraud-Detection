# 🛡️ ATM Shield - Real-Time Fraud Detection System

**ATM Shield** is a high-security automated banking simulation ecosystem engineered using a native **PHP** and **MariaDB/MySQL** stack. It implements critical application runtime security measures alongside real-time transaction monitoring matrices to prevent malicious account compromise and card spoofing vectors.

---

## 🚀 Core Anti-Fraud Validation Framework

This software leverages four layered detection algorithms to actively inspect transaction metadata during execution:

1. **Time-Based Transaction Velocity Check:** Traps and rejects rapid back-to-back fund withdrawal attempts occurring within a tight **120-second (2-minute)** time limit constraint.
2. **Dynamic Geolocation Verification (IP API Integration):** Automatically cross-references the client's current network IP address string against established regional baselines (e.g., `Madurai`) using external JSON geocoding lookups.
3. **Daily Volumetric Threshold Aggregation:** Keeps a running log of successful daily transaction amounts via database summation mechanics, strictly terminating any checkout attempts that exceed a maximum **₹50,000 daily boundary limit**.
4. **Step-Up Verification (Adaptive Session OTP Fallback):** Intelligently routes high-volume requests (exceeding **₹20,000 single transaction cap**) or location-shifted transactions to an automated **6-digit SMS-simulated OTP verification gateway** before fund routing takes place.

---

## 🔒 Implemented Production Hardening Standards

* **Defense Against SQL Injection (SQLi):** Replaced structural data interpolation layouts across user input boundaries with hardened **MySQLi Prepared Statements** (`mysqli_prepare`) and tokenized placeholder bindings.
* **Cryptographic Asset Protections:** Reconfigured legacy clear-text storage structures to enforce modern credential protection standards by salting and hashing database account PIN access metrics via PHP's native **`password_hash(PASSWORD_BCRYPT)`** functions.
* **Persistent Cross-Site Scripting (XSS) Mitigation:** Wrapped user data outputs on administration log tables and greeting sections in string mutation routines using native character map abstractions (`htmlspecialchars`).
* **Session Leak Protection & Clear Actions:** Enforced discrete **`logout.php`** session state destruction methods that systematically invalidate session memory tracking keys and flush tracking elements inside the user's browser storage.

---

## 🛠️ Application Tech Stack

* **Server Environment:** Apache Server (Hosted via local local development suites such as WampServer / XAMPP)
* **Programming Scripting Layer:** PHP 8.x
* **Database Ledger Management:** MariaDB / MySQL (Operating on default configuration Port **`3307`**)
* **UI Skin Theme Layer:** Responsive Minimalist Cyber-Dark Aesthetic Template (`style.css`)

---

## 📋 Comprehensive Repository Directory Blueprint

* `config.php` – Primary orchestration center initializing database architectures, verifying active columns, and housing port constraints.
* `index.php` – The main card authentication landing gate equipped with a hidden back-door entryway element for network administrators.
* `register.php` – Form system mapping client usernames to distinct 16-digit card networks with background crypt-hashing engines.
* `dashboard.php` – Core client summary portal that intercepts real-time admin alert payloads.
* `withdraw.php` – Primary execution module where velocity metrics, IP geolocation trackers, and limit boundaries are calculated.
* `verify_otp.php` – 6-digit transaction confirmation guard that isolates suspicious operations.
* `tx_success.php` – Standalone dynamic receipt canvas featuring an animated green confirmation summary.
* `otp_failed.php` – Visual red threat alert flag indicator indicating bad authentication input parameters.
* `admin_login.php` – Encrypted login portal managing administrative entry protocols.
* `admin.php` – Central control panel where admins can audit active fraud files, **unlock locked client cards**, and trigger **Delete & Report actions**.
* `logout.php` – Security module that invalidates all active session keys on the web server.
* `style.css` – Central design sheet managing the uniform presentation theme layout rules.
