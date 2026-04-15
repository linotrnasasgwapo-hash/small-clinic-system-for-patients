-- =============================================================
-- SARAET BARANGAY SERVICE CENTER — HIMAMAYLAN CITY
-- Database: saraet_barangay_db
-- Compatible with: XAMPP / MySQL 5.7+
-- =============================================================

CREATE DATABASE IF NOT EXISTS saraet_barangay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saraet_barangay_db;

-- ---------------------------------------------------------------
-- ADMINS (Barangay Staff / Officials)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    admin_id     INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(120) NOT NULL,
    email        VARCHAR(120) UNIQUE NOT NULL,
    password     VARCHAR(255) NOT NULL,
    role         ENUM('captain','secretary','staff','health_worker') DEFAULT 'staff',
    position     VARCHAR(100),
    is_active    TINYINT(1) DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- RESIDENTS (Mobile App Users)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS residents (
    resident_id  INT AUTO_INCREMENT PRIMARY KEY,
    first_name   VARCHAR(60)  NOT NULL,
    last_name    VARCHAR(60)  NOT NULL,
    middle_name  VARCHAR(60),
    email        VARCHAR(120) UNIQUE NOT NULL,
    password     VARCHAR(255) NOT NULL,
    phone        VARCHAR(20),
    address      TEXT,
    birthdate    DATE,
    gender       ENUM('Male','Female','Other'),
    civil_status ENUM('Single','Married','Widowed','Separated') DEFAULT 'Single',
    occupation   VARCHAR(100),
    purok        VARCHAR(60),
    is_verified  TINYINT(1) DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------
-- SERVICES (Barangay Service Types)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS services (
    service_id   INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(150) NOT NULL,
    category     ENUM('document','health','social','financial','legal','other') DEFAULT 'other',
    description  TEXT,
    requirements TEXT,
    fee          DECIMAL(8,2) DEFAULT 0.00,
    duration_min INT DEFAULT 20,
    is_active    TINYINT(1) DEFAULT 1
);

-- ---------------------------------------------------------------
-- SCHEDULES (Available Slots per Service)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS schedules (
    schedule_id   INT AUTO_INCREMENT PRIMARY KEY,
    service_id    INT,
    sched_date    DATE NOT NULL,
    start_time    TIME NOT NULL,
    end_time      TIME NOT NULL,
    max_slots     INT DEFAULT 20,
    booked_slots  INT DEFAULT 0,
    handled_by    INT,
    is_available  TINYINT(1) DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE SET NULL,
    FOREIGN KEY (handled_by) REFERENCES admins(admin_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- APPOINTMENTS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    appt_id          INT AUTO_INCREMENT PRIMARY KEY,
    resident_id      INT NOT NULL,
    service_id       INT,
    schedule_id      INT,
    queue_number     INT NOT NULL DEFAULT 0,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    purpose          TEXT,
    status           ENUM('pending','confirmed','in_queue','serving','done','cancelled','no_show') DEFAULT 'pending',
    remarks          TEXT,
    handled_by       INT,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id)  REFERENCES services(service_id)  ON DELETE SET NULL,
    FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id) ON DELETE SET NULL,
    FOREIGN KEY (handled_by)  REFERENCES admins(admin_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- QUEUE (Live Queue Tracking)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS queue (
    queue_id     INT AUTO_INCREMENT PRIMARY KEY,
    appt_id      INT NOT NULL,
    queue_number INT NOT NULL,
    queue_date   DATE NOT NULL,
    service_id   INT,
    status       ENUM('waiting','serving','done','skipped') DEFAULT 'waiting',
    called_at    TIMESTAMP NULL,
    served_at    TIMESTAMP NULL,
    FOREIGN KEY (appt_id)    REFERENCES appointments(appt_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- CLIENT RECORDS (per service transaction)
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS client_records (
    record_id      INT AUTO_INCREMENT PRIMARY KEY,
    resident_id    INT NOT NULL,
    appt_id        INT,
    service_id     INT,
    record_type    ENUM('document_request','health_record','social_assistance','certificate','complaint','other') DEFAULT 'other',
    details        TEXT,
    documents_needed TEXT,
    documents_submitted TEXT,
    outcome        TEXT,
    status         ENUM('open','processing','released','rejected','archived') DEFAULT 'open',
    recorded_by    INT,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id)  REFERENCES residents(resident_id) ON DELETE CASCADE,
    FOREIGN KEY (appt_id)      REFERENCES appointments(appt_id)  ON DELETE SET NULL,
    FOREIGN KEY (service_id)   REFERENCES services(service_id)   ON DELETE SET NULL,
    FOREIGN KEY (recorded_by)  REFERENCES admins(admin_id)       ON DELETE SET NULL
);

-- ---------------------------------------------------------------
-- NOTIFICATIONS
-- ---------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    notif_id   INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    title      VARCHAR(200) NOT NULL,
    message    TEXT NOT NULL,
    is_read    TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------
-- SEED: Default Admin  (password = Admin@1234)
-- ---------------------------------------------------------------
INSERT INTO admins (full_name, email, password, role, position) VALUES
('Hon. Carlos Mendoza', 'captain@saraet.gov.ph',   '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', 'captain',   'Barangay Captain'),
('Maria Santos',        'secretary@saraet.gov.ph',  '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', 'secretary', 'Barangay Secretary'),
('Ana Reyes',           'staff@saraet.gov.ph',      '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', 'staff',     'Front Desk Staff');

-- ---------------------------------------------------------------
-- SEED: Services
-- ---------------------------------------------------------------
INSERT INTO services (service_name, category, description, requirements, fee, duration_min) VALUES
('Barangay Clearance',          'document', 'Official barangay clearance certificate',       'Valid ID, 1x1 photo, proof of residency',     50.00,  15),
('Certificate of Indigency',    'document', 'Certificate for low-income residents',          'Valid ID, proof of residency',                 0.00,   15),
('Certificate of Residency',    'document', 'Proof of residency in Barangay Saraet',         'Valid ID',                                    30.00,  15),
('Business Permit Clearance',   'document', 'Barangay clearance for business operations',    'Valid ID, business documents',               100.00,  20),
('Certificate of Good Moral',   'document', 'Good moral character certificate',              'Valid ID, 1x1 photo',                         30.00,  15),
('Health Consultation',         'health',   'Basic health assessment by barangay healthworker','None',                                       0.00,  20),
('Philhealth Assistance',       'health',   'Assistance with PhilHealth transactions',       'PhilHealth ID or MDR',                         0.00,  25),
('4Ps / DSWD Assistance',       'social',   'Conditional cash transfer and DSWD concerns',  '4Ps ID or household ID',                       0.00,  30),
('Senior Citizen Services',     'social',   'Services for senior citizens (OSCA)',           'Senior Citizen ID or birth certificate',       0.00,  20),
('Complaint / Blotter Report',  'legal',    'Filing of barangay blotter or complaint',       'Valid ID, written statement',                  0.00,  40),
('Lupon / Mediation',           'legal',    'Barangay mediation and dispute resolution',     'Valid ID, summons or complaint form',          0.00,  60),
('Financial Assistance Request','financial','Emergency financial aid request',               'Valid ID, letter of request, proof of need',   0.00,  30);

-- ---------------------------------------------------------------
-- SEED: Schedules (today + next 7 days)
-- ---------------------------------------------------------------
INSERT INTO schedules (service_id, sched_date, start_time, end_time, max_slots, handled_by) VALUES
(1, CURDATE(), '08:00:00', '12:00:00', 30, 3),
(2, CURDATE(), '08:00:00', '12:00:00', 20, 3),
(3, CURDATE(), '13:00:00', '17:00:00', 20, 3),
(6, CURDATE(), '08:00:00', '12:00:00', 15, 3),
(9, CURDATE(), '13:00:00', '17:00:00', 15, 3),
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:00:00', '12:00:00', 30, 3),
(4, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '12:00:00', 10, 3),
(8, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:00:00', '12:00:00', 20, 3),
(10,DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', '17:00:00', 15, 2),
(5, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '08:00:00', '12:00:00', 25, 3),
(7, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '16:00:00', 20, 3);

-- ---------------------------------------------------------------
-- SEED: Sample Residents (password = User@1234)
-- ---------------------------------------------------------------
INSERT INTO residents (first_name, last_name, email, password, phone, address, birthdate, gender, purok) VALUES
('Juan',  'Dela Cruz', 'juan@email.com',  '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', '09171234567', 'Purok 1, Saraet, Himamaylan', '1990-05-12', 'Male',   'Purok 1'),
('Maria', 'Santos',    'maria@email.com', '$2y$10$TKh8H1.PnD5f2tu5Zg0JouKjpKTQ.sHM2OFLUxVZv8GCuKI9WQAK6', '09281234567', 'Purok 3, Saraet, Himamaylan', '1985-08-20', 'Female', 'Purok 3');
