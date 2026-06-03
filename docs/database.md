# Таблицы пользователей
Используется для хранения данных о пользователях
## таблица USER:
поля User:
id (INT, Prim key)
name (VARCHAR)
email (VARCHAR)
password_hash (VARCHAR)
role (ENUM)
created_at (DATE)
bio (TEXT)
avatar_link (VARCHAR)
group (INT, FK)
level (ENUM)
## таблица Student_Groups
id (INT, PK)
course_id (INT, FK)
teacher_id (INT, FK)
student_count (INT)
created_at (TIMESTAMP)
# Таблицы категорий
## таблица Subject-Module
id (INT, PK)
title (VARCHAR)
description (VARCHAR)
course_id (INT, FK)
# таблицы Основных контентных сущностей
## таблица Courses
id (INT, PK)
title (VARCHAR)
description (VARCHAR)
level (VARCHAR)
price (DECIMAL)
## таблица Article
id (INT, PK)
course_id (INT, FK)
title (VARCHAR)
content (TEXT) 
## таблица Task
id (INT, PK)
article_id (INT, FK)
title (VARCHAR)
description (TEXT)
# Таблицы статусов
## Таблица Statuses
id (INT, PK)
name (ENUM)
display_name_rus (VARCHAR)
# Операционные сущности (Заявки)
## Таблица Enrollments
id (INT, PK)
user_id (INT, FK)
course_id (INT, FK)
status_id (INT, FK)
created_at (TIMESTAMP)
## Таблица Submissions
id (INT, PK)
task_id (INT, FK)
student_id (INT, FK)
content (TEXT)
status_id (INT, FK)
submit_time (TIMESTAMP)
check_time (TIMESTAMP)
