-- Eventra database (MySQL). Import this file inside an EMPTY database
-- (local: create a database named "eventra" in phpMyAdmin first;
--  InfinityFree: use the database you created in the control panel).

CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  first_name    VARCHAR(50)  NOT NULL,
  last_name     VARCHAR(50)  NOT NULL,
  email         VARCHAR(100) NOT NULL UNIQUE,
  id_number     VARCHAR(30)  NOT NULL UNIQUE,
  role          ENUM('student','faculty') NOT NULL DEFAULT 'student',
  password_hash VARCHAR(255) NOT NULL,
  course        VARCHAR(100) NULL,
  year_level    VARCHAR(20)  NULL,
  department    VARCHAR(100) NULL,
  contact_no    VARCHAR(30)  NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE events (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(120) NOT NULL,
  category    ENUM('Academic','Music','Sports','Arts') NOT NULL,
  event_date  DATETIME NOT NULL,
  venue       VARCHAR(120) NOT NULL,
  description TEXT NULL,
  price       DECIMAL(8,2) NOT NULL DEFAULT 0,
  capacity    INT UNSIGNED NOT NULL DEFAULT 100,
  color       CHAR(7) NOT NULL DEFAULT '#F599B8',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE registrations (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  event_id      INT UNSIGNED NOT NULL,
  full_name     VARCHAR(100) NOT NULL,
  student_id    VARCHAR(30)  NOT NULL,
  email         VARCHAR(100) NOT NULL,
  course        VARCHAR(100) NOT NULL,
  year_level    VARCHAR(20)  NOT NULL,
  ticket_code   VARCHAR(30)  NULL UNIQUE,
  status        ENUM('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
  registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_event (user_id, event_id),
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample events (dates are inside the project window; edit them any time)
INSERT INTO events (title, category, event_date, venue, price, capacity, color) VALUES
('Pickleball',                   'Sports',   '2026-11-27 09:00:00', 'GYM',             0,   60,  '#F599B8'),
('Campus Music Night',           'Music',    '2026-12-05 18:00:00', 'Open Quadrangle', 350, 300, '#B07CE6'),
('Intramurals Opening Ceremony', 'Sports',   '2026-12-18 13:00:00', 'University Gym',  50,  500, '#F2A65A'),
('Quiz Bee',                     'Academic', '2026-11-21 09:00:00', 'AVR Old Building',0,   120, '#72C7C0'),
('Poster Making',                'Arts',     '2026-12-13 09:30:00', 'Room 201',        0,   40,  '#E6D36A');
