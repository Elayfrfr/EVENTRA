-- Eventra database schema (MySQL / MariaDB)
-- Import this FIRST, then seed.sql.
-- NOTE: do not add CREATE DATABASE here - on InfinityFree you create the database in the control panel.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role          ENUM('student','faculty') NOT NULL DEFAULT 'student',
  first_name    VARCHAR(60)  NOT NULL,
  last_name     VARCHAR(60)  NOT NULL,
  email         VARCHAR(120) NOT NULL UNIQUE,
  id_number     VARCHAR(40)  NOT NULL UNIQUE,      -- student ID or employee ID
  password_hash VARCHAR(255) NOT NULL,
  department    VARCHAR(120) NULL,
  contact       VARCHAR(40)  NULL,
  course        VARCHAR(80)  NULL,                 -- e.g. BSIT
  year_level    VARCHAR(20)  NULL,                 -- e.g. 3rd Year
  avatar        VARCHAR(200) NULL,                 -- relative path of uploaded photo
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE events (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organizer_id INT UNSIGNED NOT NULL,
  title        VARCHAR(150) NOT NULL,
  category     ENUM('Academic','Music','Sports','Arts') NOT NULL,
  event_date   DATE NOT NULL,
  event_time   TIME NOT NULL,
  venue        VARCHAR(150) NOT NULL,
  address      VARCHAR(200) NULL,
  description  TEXT NULL,
  capacity     INT UNSIGNED NOT NULL DEFAULT 100,
  price        DECIMAL(10,2) NOT NULL DEFAULT 0,
  color        VARCHAR(9) NULL,                    -- card color, e.g. #F59AB8
  status       ENUM('draft','published') NOT NULL DEFAULT 'published',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (event_date), INDEX (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE registrations (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id       INT UNSIGNED NOT NULL,
  user_id        INT UNSIGNED NOT NULL,
  ticket_code    VARCHAR(30) NOT NULL DEFAULT '',
  full_name      VARCHAR(120) NOT NULL,
  student_id     VARCHAR(40)  NOT NULL,
  email          VARCHAR(120) NOT NULL,
  course         VARCHAR(80)  NULL,
  year_level     VARCHAR(20)  NULL,
  status         ENUM('registered','cancelled') NOT NULL DEFAULT 'registered',
  payment_status ENUM('free','pending','paid') NOT NULL DEFAULT 'free',
  gate           VARCHAR(10) NOT NULL DEFAULT 'A',
  seat           VARCHAR(30) NOT NULL DEFAULT 'Gen. Adm.',
  generated_at   DATETIME NULL,                    -- when the student pressed "Generate Ticket"
  checked_in_at  DATETIME NULL,                    -- set by the organizer at the door
  reminded       TINYINT(1) NOT NULL DEFAULT 0,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_per_event (event_id, user_id),
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  registration_id INT UNSIGNED NOT NULL,
  method          ENUM('gcash','maya','card') NOT NULL,
  amount          DECIMAL(10,2) NOT NULL,
  reference       VARCHAR(40) NOT NULL,
  paid_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifications (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  title      VARCHAR(100) NOT NULL,
  body       VARCHAR(255) NOT NULL,
  is_read    TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
