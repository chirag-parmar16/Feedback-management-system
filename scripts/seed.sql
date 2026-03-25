-- SMS Seed Data for Testing
-- This script adds dummy users, classes, subjects, and extensive feedback data.

USE `userdb`;

-- 1. Add more Subjects
INSERT INTO `subjects` (`name`) VALUES 
('Advanced Web Tech'),
('Cloud Computing'),
('Cyber Security'),
('Machine Learning'),
('Mobile App Dev');

-- 2. Add more Teachers (Password: 'teacher123')
INSERT INTO `users` (`username`, `password`, `role`, `email`) VALUES
('amit_sir', '$2y$10$e.qehVQi9UdaFh68CioPKOhCOG6Jy9D/p12DUv4zR5asmWbz3m7qW', 'teacher', 'amit@school.com'),
('sneha_mam', '$2y$10$e.qehVQi9UdaFh68CioPKOhCOG6Jy9D/p12DUv4zR5asmWbz3m7qW', 'teacher', 'sneha@school.com'),
('rahul_sir', '$2y$10$e.qehVQi9UdaFh68CioPKOhCOG6Jy9D/p12DUv4zR5asmWbz3m7qW', 'teacher', 'rahul@school.com');

-- 3. Add more Students (Password: 'student123')
INSERT INTO `users` (`username`, `password`, `role`, `email`) VALUES
('riya_patel', '$2y$10$5AtYrg8snkEE8Dp8J0zelemmdXHkQLBVhjB6QNkrwLflHrH1D1Zge', 'student', 'riya@student.com'),
('arjun_shah', '$2y$10$5AtYrg8snkEE8Dp8J0zelemmdXHkQLBVhjB6QNkrwLflHrH1D1Zge', 'student', 'arjun@student.com'),
('neha_sharma', '$2y$10$5AtYrg8snkEE8Dp8J0zelemmdXHkQLBVhjB6QNkrwLflHrH1D1Zge', 'student', 'neha@student.com'),
('vikram_rao', '$2y$10$5AtYrg8snkEE8Dp8J0zelemmdXHkQLBVhjB6QNkrwLflHrH1D1Zge', 'student', 'vikram@student.com');

-- 4. Add more Classes
INSERT INTO `classes` (`name`, `section`) VALUES
('10th Standard', 'B'),
('11th Science', 'A'),
('9th Standard', 'C');

-- 5. Assign Teachers to Subjects/Classes
-- Assuming IDs: amit_sir=5, sneha_mam=6, rahul_sir=7
-- Assuming Subjects: Cloud=7, PHP=1, Java=2
-- Assuming Classes: 10th B=2, 11th A=3
INSERT INTO `teacher_assignment` (`teacher_id`, `subject_id`, `class_id`) VALUES
(5, 7, 2), -- Amit -> Cloud -> 10th B
(6, 1, 3), -- Sneha -> PHP -> 11th A
(7, 2, 3); -- Rahul -> Java -> 11th A

-- 6. Enroll Students
-- IDs: riya=8, arjun=9, neha=10, vikram=11
INSERT INTO `student_enrollment` (`student_id`, `class_id`) VALUES
(8, 2), (9, 2), (10, 3), (11, 3);

-- 7. Create Feedback Forms
-- ID 1 was already there, let's add more
INSERT INTO `feedback_forms` (`teacher_id`, `subject_id`, `title`, `status`, `visibility_to_teacher`) VALUES
(5, 7, 'Quarterly Feedback - Cloud Computing', 'active', 1),
(6, 1, 'Mid-Term Evaluation - PHP', 'active', 1),
(7, 2, 'Core Java Feedback', 'active', 0); -- Confidential

-- 8. Add Feedback Responses (Dummy sentiment)
-- For Form 2 (Amit Sir)
INSERT INTO `feedback_responses` (`form_id`, `student_id`, `rating`, `comments`) VALUES
(2, 8, 5, 'Excellent teaching style, very clear concepts.'),
(2, 9, 4, 'Good lectures, but needs more practical examples.'),
(2, 10, 5, 'Highly knowledgeable and supportive.');

-- For Form 3 (Sneha Mam)
INSERT INTO `feedback_responses` (`form_id`, `student_id`, `rating`, `comments`) VALUES
(3, 10, 3, 'The pace is a bit fast, hard to keep up.'),
(3, 11, 2, 'Needs to explain the basics more clearly.'),
(3, 1, 4, 'Good course material.');

-- For Form 4 (Rahul Sir - Confidential)
INSERT INTO `feedback_responses` (`form_id`, `student_id`, `rating`, `comments`) VALUES
(4, 10, 1, 'Very strict and difficult to reach for doubts.'),
(4, 11, 2, 'Boring lectures, mostly reading from slides.');
