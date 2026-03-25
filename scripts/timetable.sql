-- Timetable structure for SMS
CREATE TABLE IF NOT EXISTS `timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_no` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `timetable_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timetable_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `timetable_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample Data (assuming class 1, subject 1, teacher 3 exist from previous seeds)
-- Adjusting to likely IDs based on seed.sql (Teacher 3 = Alice, Class 1 = 12th Commerce A)
INSERT IGNORE INTO `timetable` (`class_id`, `subject_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `room_no`) VALUES
(1, 1, 3, 'Monday', '09:00:00', '10:00:00', 'Room 101'),
(1, 2, 4, 'Monday', '10:00:00', '11:00:00', 'Room 101');
