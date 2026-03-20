-- Database: `userdb` refinements

-- Feedback Forms Table
CREATE TABLE IF NOT EXISTS `feedback_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Feedback Responses Table
CREATE TABLE IF NOT EXISTS `feedback_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Ensure some default subjects exist if table is empty
INSERT IGNORE INTO `subjects` (`id`, `name`) VALUES 
(1, 'Mathematics'),
(2, 'Science'),
(3, 'English'),
(4, 'Computer Science');
