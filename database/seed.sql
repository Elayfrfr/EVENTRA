-- Demo data. Import AFTER schema.sql.
-- Demo password for EVERY account below:  Eventra@Demo2026
-- Dates are relative to "today", so the demo always looks fresh.

SET NAMES utf8mb4;

INSERT INTO users (id, role, first_name, last_name, email, id_number, password_hash, department, contact, course, year_level) VALUES
(1,'faculty','Zemary','Dumabog','zemdumabog43@uc.edu.ph','FAC 2019 045','$2y$10$/ooErpMmV0ivohavcuOvG.7AJmqIJWqkT09HaHO2eyKTlxL1qexEm','College of Computer Studies','+63 67 6746 7676',NULL,NULL),
(2,'student','Joie','Velasco','joievelasco69@uc.edu.ph','2023-00123','$2y$10$/ooErpMmV0ivohavcuOvG.7AJmqIJWqkT09HaHO2eyKTlxL1qexEm','College of Computer Studies','+63 912 345 6789','BSIT','3rd Year'),
(3,'student','Zwah','Velastico','zwahvelastico@uc.edu.ph','2023-00124','$2y$10$/ooErpMmV0ivohavcuOvG.7AJmqIJWqkT09HaHO2eyKTlxL1qexEm','College of Computer Studies',NULL,'BSIT','3rd Year'),
(4,'student','Felms','Senings','felmssenings@uc.edu.ph','2024-00210','$2y$10$/ooErpMmV0ivohavcuOvG.7AJmqIJWqkT09HaHO2eyKTlxL1qexEm','College of Computer Studies',NULL,'BSIS','2nd Year'),
(5,'student','Simona','Philipia','simonaphilipia@uc.edu.ph','2022-00871','$2y$10$/ooErpMmV0ivohavcuOvG.7AJmqIJWqkT09HaHO2eyKTlxL1qexEm','College of Computer Studies',NULL,'BSIT','4th Year'),
(6,'student','Keithy Pink','Enjambers','keithyenjambers@uc.edu.ph','2023-00389','$2y$10$/ooErpMmV0ivohavcuOvG.7AJmqIJWqkT09HaHO2eyKTlxL1qexEm','College of Computer Studies',NULL,'BSIS','3rd Year');

INSERT INTO events (id, organizer_id, title, category, event_date, event_time, venue, address, description, capacity, price, color, status) VALUES
(1, 1,'Intramurals Opening Ceremony','Sports',  DATE_ADD(CURDATE(), INTERVAL 6 DAY),  '13:00:00','University of Cebu Gym','University of Cebu, Sanciangko St., Cebu City','Kick-off of the university intramurals with parade of teams and opening games.',200,50,'#F2A65A','published'),
(2, 1,'Pickleball',                   'Sports',  DATE_ADD(CURDATE(), INTERVAL 8 DAY),  '09:00:00','GYM','University of Cebu, Sanciangko St., Cebu City','Pickleball tournament open to all students.',50,0,'#F59AB8','published'),
(3, 1,'Ben&Ben Concert',              'Music',   DATE_ADD(CURDATE(), INTERVAL 14 DAY), '18:00:00','Open Quadrangle','University of Cebu, Sanciangko St., Cebu City','An evening concert with Ben&Ben.',500,350,'#B07CE8','published'),
(4, 1,'Intramurals',                  'Sports',  DATE_ADD(CURDATE(), INTERVAL 120 DAY),'18:00:00','Open Quadrangle','University of Cebu, Sanciangko St., Cebu City','Main intramurals games.',50,0,'#F2A65A','published'),
(5, 1,'Quiz Bee',                     'Academic',DATE_ADD(CURDATE(), INTERVAL 3 DAY),  '09:00:00','AVR Old Building','University of Cebu, Sanciangko St., Cebu City','Test your knowledge in the campus-wide quiz bee.',100,0,'#6FC7C0','published'),
(6, 1,'Poster Making',                'Arts',    DATE_ADD(CURDATE(), INTERVAL 22 DAY), '09:30:00','Room 201','University of Cebu, Sanciangko St., Cebu City','Poster making contest. Materials provided.',40,0,'#E8D26A','published'),
(7, 1,'Tech Talk: AI in Education',   'Academic',DATE_ADD(CURDATE(), INTERVAL 5 DAY),  '09:00:00','Room 301','University of Cebu, Sanciangko St., Cebu City','Guest speakers talk about AI in the classroom.',40,0,'#6FC7C0','published'),
(8, 1,'Freshmen Welcome Fair',        'Arts',    DATE_ADD(CURDATE(), INTERVAL 9 DAY),  '10:00:00','Student Center','University of Cebu, Sanciangko St., Cebu City','Booths and performances for incoming freshmen.',100,0,'#E8D26A','published'),
(9, 1,'Battle of the Bands',          'Music',   DATE_ADD(CURDATE(), INTERVAL 24 DAY), '18:00:00','Open Quadrangle','University of Cebu, Sanciangko St., Cebu City','Campus bands compete on stage.',300,0,'#B07CE8','draft'),
(10,1,'CCS Tryouts',                  'Sports',  DATE_SUB(CURDATE(), INTERVAL 49 DAY), '09:00:00','UC South','University of Cebu South Campus, Cebu City','Tryouts for the CCS team.',24,0,'#F59AB8','published'),
(11,1,'Library Orientation',          'Academic',DATE_SUB(CURDATE(), INTERVAL 70 DAY), '10:00:00','Main Library','University of Cebu, Sanciangko St., Cebu City','Learn how to use the library.',30,0,'#6FC7C0','published'),
(12,1,'Coding Workshop',              'Academic',DATE_SUB(CURDATE(), INTERVAL 80 DAY), '14:00:00','Computer Lab 2','University of Cebu, Sanciangko St., Cebu City','Hands-on coding workshop.',20,0,'#6FC7C0','published');

INSERT INTO registrations (id, event_id, user_id, ticket_code, full_name, student_id, email, course, year_level, payment_status, gate, seat, generated_at, checked_in_at) VALUES
(1, 1, 2,'EVT-2026-0001','Joie Velasco','2023-00123','joievelasco69@uc.edu.ph','BSIT','3rd Year','pending','A','Gen. Adm.',NULL,NULL),
(2, 2, 2,'EVT-2026-0002','Joie Velasco','2023-00123','joievelasco69@uc.edu.ph','BSIT','3rd Year','free',   'A','Gen. Adm.',NULL,NULL),
(3, 3, 2,'EVT-2026-0003','Joie Velasco','2023-00123','joievelasco69@uc.edu.ph','BSIT','3rd Year','pending','B','Gen. Adm.',NULL,NULL),
(4,10, 2,'EVT-2026-0004','Joie Velasco','2023-00123','joievelasco69@uc.edu.ph','BSIT','3rd Year','free',   'A','Gen. Adm.',NOW(),DATE_SUB(NOW(), INTERVAL 49 DAY)),
(5, 1, 3,'EVT-2026-0005','Zwah Velastico','2023-00124','zwahvelastico@uc.edu.ph','BSIT','3rd Year','paid','A','Gen. Adm.',NULL,NOW()),
(6, 1, 4,'EVT-2026-0006','Felms Senings','2024-00210','felmssenings@uc.edu.ph','BSIS','2nd Year','paid','A','Gen. Adm.',NULL,NULL),
(7, 1, 5,'EVT-2026-0007','Simona Philipia','2022-00871','simonaphilipia@uc.edu.ph','BSIT','4th Year','paid','A','Gen. Adm.',NULL,NOW()),
(8, 1, 6,'EVT-2026-0008','Keithy Pink Enjambers','2023-00389','keithyenjambers@uc.edu.ph','BSIS','3rd Year','paid','A','Gen. Adm.',NULL,NOW()),
(9,10, 3,'EVT-2026-0009','Zwah Velastico','2023-00124','zwahvelastico@uc.edu.ph','BSIT','3rd Year','free','A','Gen. Adm.',NOW(),DATE_SUB(NOW(), INTERVAL 49 DAY)),
(10,10,4,'EVT-2026-0010','Felms Senings','2024-00210','felmssenings@uc.edu.ph','BSIS','2nd Year','free','A','Gen. Adm.',NOW(),DATE_SUB(NOW(), INTERVAL 49 DAY)),
(11,10,5,'EVT-2026-0011','Simona Philipia','2022-00871','simonaphilipia@uc.edu.ph','BSIT','4th Year','free','A','Gen. Adm.',NOW(),NULL),
(12,10,6,'EVT-2026-0012','Keithy Pink Enjambers','2023-00389','keithyenjambers@uc.edu.ph','BSIS','3rd Year','free','A','Gen. Adm.',NOW(),DATE_SUB(NOW(), INTERVAL 49 DAY)),
(13, 7, 3,'EVT-2026-0013','Zwah Velastico','2023-00124','zwahvelastico@uc.edu.ph','BSIT','3rd Year','free','A','Gen. Adm.',NULL,NULL),
(14, 5, 4,'EVT-2026-0014','Felms Senings','2024-00210','felmssenings@uc.edu.ph','BSIS','2nd Year','free','A','Gen. Adm.',NULL,NULL),
(15, 8, 5,'EVT-2026-0015','Simona Philipia','2022-00871','simonaphilipia@uc.edu.ph','BSIT','4th Year','free','A','Gen. Adm.',NULL,NULL),
(16,11, 6,'EVT-2026-0016','Keithy Pink Enjambers','2023-00389','keithyenjambers@uc.edu.ph','BSIS','3rd Year','free','A','Gen. Adm.',NOW(),DATE_SUB(NOW(), INTERVAL 70 DAY)),
(17,12, 3,'EVT-2026-0017','Zwah Velastico','2023-00124','zwahvelastico@uc.edu.ph','BSIT','3rd Year','free','A','Gen. Adm.',NOW(),DATE_SUB(NOW(), INTERVAL 80 DAY));

INSERT INTO payments (registration_id, method, amount, reference) VALUES
(5,'gcash',50,'PAY-SEED0005'),(6,'maya',50,'PAY-SEED0006'),(7,'card',50,'PAY-SEED0007'),(8,'gcash',50,'PAY-SEED0008');

INSERT INTO notifications (user_id, title, body, is_read, created_at) VALUES
(2,'Ticket confirmed',"You're registered for Intramurals Opening Ceremony. Your QR ticket is ready.",0,NOW()),
(2,'Event reminder','Tech Talk: AI in Education starts tomorrow at 9:00 AM, Room 301.',0,NOW()),
(2,'Event updated','Battle of the Bands moved to the Open Quadrangle. Check the new details.',1,DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2,'New event posted','Freshmen Welcome Fair is now open for registration.',1,DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1,'New registration','Simona Philipia registered for Intramurals Opening Ceremony.',0,NOW()),
(1,'New registration','Zwah Velastico registered for Tech Talk: AI in Education.',0,NOW()),
(1,'Event ended','CCS Tryouts has ended. Your attendance report is ready.',1,DATE_SUB(NOW(), INTERVAL 1 DAY));
