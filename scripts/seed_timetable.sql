-- Clear existing timetable to avoid duplicates during seed
TRUNCATE TABLE `timetable`;

-- Seed for Class 1 (12th Commerce A)
INSERT INTO `timetable` (`class_id`, `subject_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `room_no`) VALUES
-- Monday
(1, 1, 3, 'Monday', '09:00:00', '10:00:00', 'Room 101'),
(1, 2, 6, 'Monday', '10:00:00', '11:00:00', 'Room 101'),
(1, 3, 7, 'Monday', '11:15:00', '12:15:00', 'Lab A'),
(1, 4, 8, 'Monday', '12:15:00', '13:15:00', 'Lab A'),
-- Tuesday
(1, 5, 3, 'Tuesday', '09:00:00', '10:00:00', 'Room 101'),
(1, 6, 6, 'Tuesday', '10:00:00', '11:00:00', 'Room 101'),
(1, 7, 7, 'Tuesday', '11:15:00', '12:15:00', 'Room 101'),
-- Wednesday
(1, 8, 8, 'Wednesday', '09:00:00', '10:00:00', 'Room 101'),
(1, 9, 3, 'Wednesday', '10:00:00', '11:00:00', 'Room 101'),
(1, 10, 6, 'Wednesday', '11:15:00', '12:15:00', 'Room 101'),
-- Thursday
(1, 1, 7, 'Thursday', '09:00:00', '10:00:00', 'Room 101'),
(1, 2, 8, 'Thursday', '10:00:00', '11:00:00', 'Room 101'),
-- Friday
(1, 3, 3, 'Friday', '09:00:00', '10:00:00', 'Room 101'),
(1, 4, 6, 'Friday', '10:00:00', '11:00:00', 'Room 101'),
-- Saturday
(1, 5, 7, 'Saturday', '09:00:00', '11:00:00', 'Lab B');

-- Seed for Class 3 (11th Science A)
INSERT INTO `timetable` (`class_id`, `subject_id`, `teacher_id`, `day_of_week`, `start_time`, `end_time`, `room_no`) VALUES
-- Monday
(3, 4, 6, 'Monday', '09:00:00', '10:00:00', 'Room 302'),
(3, 3, 8, 'Monday', '10:00:00', '11:00:00', 'Room 302'),
-- Tuesday
(3, 2, 3, 'Tuesday', '09:00:00', '10:00:00', 'Room 302'),
(3, 1, 7, 'Tuesday', '10:00:00', '11:00:00', 'Room 302'),
-- Wednesday
(3, 5, 6, 'Wednesday', '09:00:00', '10:00:00', 'Room 302'),
(3, 6, 8, 'Wednesday', '10:00:00', '11:00:00', 'Room 302'),
-- Thursday
(3, 7, 3, 'Thursday', '09:00:00', '10:00:00', 'Room 302'),
(3, 8, 7, 'Thursday', '10:00:00', '11:00:00', 'Room 302'),
-- Friday
(3, 9, 6, 'Friday', '09:00:00', '10:00:00', 'Room 302'),
(3, 10, 8, 'Friday', '10:00:00', '11:00:00', 'Room 302'),
-- Saturday
(3, 1, 3, 'Saturday', '09:00:00', '11:00:00', 'Physics Lab');
