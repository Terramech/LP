SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Submissions;
DROP TABLE IF EXISTS Enrollments;
DROP TABLE IF EXISTS Statuses;
DROP TABLE IF EXISTS Sub_statuses;
DROP TABLE IF EXISTS Task;
DROP TABLE IF EXISTS Article;
DROP TABLE IF EXISTS `Subject-Module`;
DROP TABLE IF EXISTS Student_Groups;
DROP TABLE IF EXISTS Courses;
DROP TABLE IF EXISTS USER;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE Courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    level VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL
);

CREATE TABLE `Subject-Module` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(500),
    course_id INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES Courses(id) ON DELETE CASCADE
);

CREATE TABLE USER (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
    created_at DATE NOT NULL,
    bio TEXT,
    avatar_link VARCHAR(255),
    group_id INT NULL,
    level ENUM('elementary', 'pre-intermediate', 'intermediate', 'upper-intermediate', 'advanced', 'proficient') NULL
);

CREATE TABLE Student_Groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    teacher_id INT NOT NULL,
    student_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES Courses(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES USER(id) ON DELETE RESTRICT
);

ALTER TABLE USER
ADD CONSTRAINT fk_user_group
FOREIGN KEY (group_id) REFERENCES Student_Groups(id) ON DELETE SET NULL;

-- 5. Таблица статей (Уроки внутри курса)
CREATE TABLE Article (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES Courses(id) ON DELETE CASCADE
);

-- 6. Таблица практических заданий к статьям
CREATE TABLE Task (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (article_id) REFERENCES Article(id) ON DELETE CASCADE
);

CREATE TABLE Statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('new', 'in_progress', 'confirmed', 'rejected', 'completed') NOT NULL UNIQUE,
    display_name_rus VARCHAR(100) NOT NULL
);

INSERT INTO Statuses (name, display_name_rus) VALUES
('new', 'Новая'),
('in_progress', 'В обработке'),
('confirmed', 'Подтверждена'),
('rejected', 'Отклонена'),
('completed', 'Завершена');

CREATE TABLE Sub_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('Submitted', 'On_check', 'Accepted', 'Rejected') NOT NULL UNIQUE,
    display_name_rus VARCHAR(100) NOT NULL
);

INSERT INTO Sub_statuses (name, display_name_rus) VALUES
('Submitted', 'Отправлено'),
('On_check', 'На проверке'),
('Accepted', 'Принято'),
('Rejected', 'Отклонено');


CREATE TABLE Enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    status_id INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES USER(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES Courses(id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES Statuses(id) ON DELETE RESTRICT
);

CREATE TABLE Submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    student_id INT NOT NULL,
    content TEXT NOT NULL,
    sub_status_id INT NOT NULL DEFAULT 1,
    submit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    check_time TIMESTAMP NULL DEFAULT NULL,
    grade INT NULL,
    feedback TEXT NULL,
    FOREIGN KEY (task_id) REFERENCES Task(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES USER(id) ON DELETE CASCADE,
    FOREIGN KEY (sub_status_id) REFERENCES Sub_statuses(id) ON DELETE RESTRICT
);