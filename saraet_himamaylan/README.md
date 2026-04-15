# Saraet Barangay Service Center — Appointment Queue Management System
## Himamaylan City, Negros Occidental
### Capstone Project — PHP + MySQL (XAMPP) + Mobile-Responsive Web App

---

## 📁 PROJECT STRUCTURE

```
saraet_himamaylan/
├── index.php                  ← Public landing page
├── database.sql               ← Full database schema + seed data
├── config/
│   └── db.php                 ← Database connection settings
├── includes/
│   ├── auth.php               ← Session & authentication helpers
│   └── queue_api.php          ← AJAX API for queue management
├── admin/                     ← Staff / Admin Panel
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php          ← Main dashboard with live queue
│   ├── queue.php              ← Full-screen live queue display board
│   ├── appointments.php       ← Appointment management + walk-in
│   ├── records.php            ← Client records management
│   ├── residents.php          ← Resident registry
│   ├── services.php           ← Services CRUD
│   ├── schedules.php          ← Schedule management
│   ├── staff.php              ← Staff accounts
│   └── reports.php            ← Monthly analytics & reports
└── mobile/                    ← Resident Mobile Web App
    ├── login.php
    ├── logout.php
    ├── register.php
    ├── home.php               ← Dashboard with queue status
    ├── book.php               ← Book appointment
    ├── get_schedules.php      ← AJAX: fetch available schedules
    ├── queue_status.php       ← Live queue tracker (auto-refresh)
    ├── appointments.php       ← Appointment history & cancellation
    └── profile.php            ← Resident profile management
```

---

## 🚀 INSTALLATION (XAMPP)

### Step 1 — Start XAMPP
1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL**

### Step 2 — Copy Project Files
1. Copy the entire `saraet_himamaylan/` folder to:
   ```
   C:\xampp\htdocs\saraet_himamaylan\
   ```

### Step 3 — Create the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **"New"** → name it `saraet_barangay_db` → Click **Create**
3. Click on `saraet_barangay_db` → click **"Import"** tab
4. Click **"Choose File"** → select `database.sql`
5. Click **"Go"** to import

### Step 4 — Configure Database (if needed)
Edit `config/db.php` if your MySQL settings differ:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // your MySQL username
define('DB_PASS', '');          // your MySQL password (empty by default in XAMPP)
define('DB_NAME', 'saraet_barangay_db');
```

### Step 5 — Open the System
| URL | Description |
|-----|-------------|
| `http://localhost/saraet_himamaylan/` | Public landing page |
| `http://localhost/saraet_himamaylan/admin/login.php` | Staff/Admin portal |
| `http://localhost/saraet_himamaylan/mobile/login.php` | Resident mobile portal |

---

## 🔑 DEFAULT LOGIN CREDENTIALS

### Admin / Staff Accounts
| Name | Email | Password | Role |
|------|-------|----------|------|
| Hon. Carlos Mendoza | captain@saraet.gov.ph | Admin@1234 | Barangay Captain |
| Maria Santos | secretary@saraet.gov.ph | Admin@1234 | Secretary |
| Ana Reyes | staff@saraet.gov.ph | Admin@1234 | Front Desk Staff |

### Resident Accounts
| Name | Email | Password |
|------|-------|----------|
| Juan Dela Cruz | juan@email.com | User@1234 |
| Maria Santos | maria@email.com | User@1234 |

---

## 📱 MOBILE ACCESS (For Residents)

The mobile portal is **fully responsive** and works on any smartphone browser.

To access from a mobile phone on the same WiFi network:
1. Find your PC's local IP address (run `ipconfig` in Command Prompt)
2. On the mobile browser, go to: `http://[YOUR-PC-IP]/saraet_himamaylan/mobile/login.php`
   - Example: `http://192.168.1.5/saraet_himamaylan/mobile/login.php`

---

## ✅ SYSTEM MODULES

### Admin Panel Features
- **Dashboard** — Live stats, today's queue, recent appointments
- **Live Queue Board** — Full-screen TV display for the waiting area (auto-refreshes every 15s)
- **Appointment Management** — View, filter, update status, add walk-ins
- **Client Records** — Create, track, and update resident service records
- **Resident Registry** — Add and search registered residents
- **Services Management** — Add/edit/enable/disable barangay services
- **Schedule Management** — Create appointment slots per service
- **Reports & Analytics** — Monthly stats, top services, daily chart

### Mobile Resident App Features
- **Register / Login** — Secure account creation
- **Home Dashboard** — Shows active queue number with live status
- **Book Appointment** — Choose service → pick schedule → get queue number
- **Live Queue Tracker** — Real-time position, ahead count, estimated wait (auto-refresh 30s)
- **Appointment History** — View upcoming/past appointments, cancel bookings
- **Profile Management** — Update personal information

---

## 🛡️ SECURITY NOTES (For Capstone Defense)
- Passwords are hashed using **bcrypt** (`password_hash()` / `password_verify()`)
- All inputs are sanitized using `mysqli_real_escape_string()` and `strip_tags()`
- Sessions are used for authentication state management
- Prepared statements are used for all parameterized queries

---

## 📊 DATABASE TABLES
| Table | Purpose |
|-------|---------|
| `admins` | Barangay staff and officials |
| `residents` | Registered resident users |
| `services` | Available barangay services |
| `schedules` | Available appointment slots per service |
| `appointments` | All appointment bookings |
| `queue` | Live queue tracking per day |
| `client_records` | Service transaction records per resident |
| `notifications` | In-app notifications for residents |

---

*Developed for capstone purposes — Barangay Saraet, Himamaylan City, Negros Occidental*
