-- ShahkotPK v13.6.0 — Education Network Multi-Teaching LMS
INSERT INTO settings (setting_key,setting_value) VALUES
('installed_app_version','13.6.0'),('runtime_cache_contract_version','13.6.0'),('public_data_cache_version','13.6.0'),('lms_version','13.6.0')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
INSERT IGNORE INTO settings(setting_key,setting_value) VALUES
('lms_enabled','1'),('lms_self_enrollment_enabled','1'),('lms_certificates_enabled','1'),('lms_default_currency','PKR');

CREATE TABLE IF NOT EXISTS lms_institutes_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,name VARCHAR(190) NOT NULL,slug VARCHAR(190) NOT NULL,description TEXT NULL,logo_url VARCHAR(500) NULL,status VARCHAR(24) NOT NULL DEFAULT 'active',created_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_inst_slug(tenant_id,slug),KEY idx_lms_inst_status(tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_teachers_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,user_id BIGINT UNSIGNED NOT NULL,institute_id BIGINT UNSIGNED NULL,display_name VARCHAR(190) NOT NULL,bio TEXT NULL,status VARCHAR(24) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_teacher_user(tenant_id,user_id),KEY idx_lms_teacher_inst(tenant_id,institute_id),KEY idx_lms_teacher_status(tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_students_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,user_id BIGINT UNSIGNED NOT NULL,student_no VARCHAR(64) NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_student_user(tenant_id,user_id),UNIQUE KEY uq_lms_student_no(tenant_id,student_no),KEY idx_lms_student_status(tenant_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_courses_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,institute_id BIGINT UNSIGNED NULL,title VARCHAR(220) NOT NULL,slug VARCHAR(190) NOT NULL,code VARCHAR(80) NULL,summary TEXT NULL,description MEDIUMTEXT NULL,category VARCHAR(120) NULL,level VARCHAR(80) NULL,language VARCHAR(80) NOT NULL DEFAULT 'English',thumbnail_url VARCHAR(500) NULL,price DECIMAL(12,2) NOT NULL DEFAULT 0,currency VARCHAR(8) NOT NULL DEFAULT 'PKR',status VARCHAR(24) NOT NULL DEFAULT 'draft',visibility VARCHAR(24) NOT NULL DEFAULT 'public',enrollment_mode VARCHAR(24) NOT NULL DEFAULT 'open',max_students INT UNSIGNED NOT NULL DEFAULT 0,start_date DATE NULL,end_date DATE NULL,created_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_course_slug(tenant_id,slug),KEY idx_lms_course_status(tenant_id,status,visibility),KEY idx_lms_course_inst(tenant_id,institute_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_course_teachers_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,teacher_id BIGINT UNSIGNED NOT NULL,teaching_role VARCHAR(24) NOT NULL DEFAULT 'instructor',active TINYINT(1) NOT NULL DEFAULT 1,assigned_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_course_teacher(tenant_id,course_id,teacher_id),KEY idx_lms_teacher_courses(tenant_id,teacher_id,active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_batches_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,title VARCHAR(190) NOT NULL,code VARCHAR(80) NULL,schedule_text VARCHAR(500) NULL,start_date DATE NULL,end_date DATE NULL,capacity INT UNSIGNED NOT NULL DEFAULT 0,status VARCHAR(24) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_batches_course(tenant_id,course_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_enrollments_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,batch_id BIGINT UNSIGNED NULL,student_id BIGINT UNSIGNED NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'active',source VARCHAR(24) NOT NULL DEFAULT 'admin',progress_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,enrolled_at DATETIME NOT NULL,completed_at DATETIME NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_enroll_course(tenant_id,course_id,status),KEY idx_lms_enroll_student(tenant_id,student_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_sections_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,title VARCHAR(220) NOT NULL,description TEXT NULL,sort_order INT NOT NULL DEFAULT 0,status VARCHAR(24) NOT NULL DEFAULT 'published',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_sections_course(tenant_id,course_id,status,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_lessons_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,section_id BIGINT UNSIGNED NOT NULL,title VARCHAR(220) NOT NULL,slug VARCHAR(190) NOT NULL,lesson_type VARCHAR(24) NOT NULL DEFAULT 'text',content MEDIUMTEXT NULL,resource_url VARCHAR(800) NULL,duration_minutes INT UNSIGNED NOT NULL DEFAULT 0,sort_order INT NOT NULL DEFAULT 0,is_preview TINYINT(1) NOT NULL DEFAULT 0,status VARCHAR(24) NOT NULL DEFAULT 'published',created_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_lessons_course(tenant_id,course_id,status),KEY idx_lms_lessons_section(tenant_id,section_id,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_lesson_progress_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,enrollment_id BIGINT UNSIGNED NOT NULL,lesson_id BIGINT UNSIGNED NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'completed',completed_at DATETIME NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_lesson_progress(tenant_id,enrollment_id,lesson_id),KEY idx_lms_progress_enroll(tenant_id,enrollment_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_assignments_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,title VARCHAR(220) NOT NULL,instructions MEDIUMTEXT NULL,due_at DATETIME NULL,max_score DECIMAL(10,2) NOT NULL DEFAULT 100,status VARCHAR(24) NOT NULL DEFAULT 'published',created_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_assign_course(tenant_id,course_id,status,due_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_assignment_submissions_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,assignment_id BIGINT UNSIGNED NOT NULL,student_id BIGINT UNSIGNED NOT NULL,body MEDIUMTEXT NULL,status VARCHAR(24) NOT NULL DEFAULT 'submitted',submitted_at DATETIME NULL,score DECIMAL(10,2) NULL,feedback TEXT NULL,graded_by BIGINT UNSIGNED NULL,graded_at DATETIME NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_assignment_student(tenant_id,assignment_id,student_id),KEY idx_lms_submission_status(tenant_id,status,submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_quizzes_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,title VARCHAR(220) NOT NULL,instructions TEXT NULL,time_limit_minutes INT UNSIGNED NOT NULL DEFAULT 0,max_attempts INT UNSIGNED NOT NULL DEFAULT 1,pass_percent DECIMAL(5,2) NOT NULL DEFAULT 50,status VARCHAR(24) NOT NULL DEFAULT 'published',created_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_quiz_course(tenant_id,course_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_quiz_questions_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,quiz_id BIGINT UNSIGNED NOT NULL,question_type VARCHAR(32) NOT NULL DEFAULT 'single_choice',question_text TEXT NOT NULL,options_json JSON NULL,correct_answer VARCHAR(255) NULL,points DECIMAL(8,2) NOT NULL DEFAULT 1,sort_order INT NOT NULL DEFAULT 0,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_question_quiz(tenant_id,quiz_id,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_quiz_attempts_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,quiz_id BIGINT UNSIGNED NOT NULL,student_id BIGINT UNSIGNED NOT NULL,attempt_no INT UNSIGNED NOT NULL DEFAULT 1,answers_json JSON NULL,score DECIMAL(10,2) NOT NULL DEFAULT 0,max_score DECIMAL(10,2) NOT NULL DEFAULT 0,percent DECIMAL(5,2) NOT NULL DEFAULT 0,status VARCHAR(24) NOT NULL DEFAULT 'finished',started_at DATETIME NULL,finished_at DATETIME NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_attempt_student(tenant_id,student_id,quiz_id),KEY idx_lms_attempt_quiz(tenant_id,quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_attendance_sessions_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,batch_id BIGINT UNSIGNED NULL,title VARCHAR(190) NOT NULL,held_on DATE NOT NULL,created_by BIGINT UNSIGNED NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_att_session(tenant_id,course_id,held_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_attendance_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,session_id BIGINT UNSIGNED NOT NULL,student_id BIGINT UNSIGNED NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'present',note VARCHAR(500) NULL,marked_by BIGINT UNSIGNED NULL,marked_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_att_student(tenant_id,session_id,student_id),KEY idx_lms_att_status(tenant_id,student_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_announcements_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,author_user_id BIGINT UNSIGNED NULL,title VARCHAR(220) NOT NULL,body MEDIUMTEXT NOT NULL,audience VARCHAR(24) NOT NULL DEFAULT 'all',status VARCHAR(24) NOT NULL DEFAULT 'published',published_at DATETIME NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_announcement_course(tenant_id,course_id,status,published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_certificates_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,enrollment_id BIGINT UNSIGNED NOT NULL,certificate_no VARCHAR(100) NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'issued',issued_at DATETIME NOT NULL,created_at DATETIME NOT NULL,PRIMARY KEY(id),UNIQUE KEY uq_lms_cert_enroll(tenant_id,enrollment_id),UNIQUE KEY uq_lms_cert_no(tenant_id,certificate_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS lms_discussions_v1360 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,course_id BIGINT UNSIGNED NOT NULL,lesson_id BIGINT UNSIGNED NULL,user_id BIGINT UNSIGNED NOT NULL,parent_id BIGINT UNSIGNED NULL,body TEXT NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'published',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,PRIMARY KEY(id),KEY idx_lms_discussion_course(tenant_id,course_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO access_features_v1300(feature_key,label,description,audience,module_group,route_pattern,admin_only,default_guest,default_customer,default_shopkeeper,active,source,created_at,updated_at) VALUES
('admin.education_lms','Education Network LMS','Manage institutes, courses, teachers, curriculum, assessments, attendance, grades and certificates','Staff','Education','/admin/lms*.php*',1,0,0,0,1,'core',NOW(),NOW()),
('education.lms','Education Network','Public LMS course catalog and enrollment','Public','Education','/education*.php*',0,1,1,1,1,'core',NOW(),NOW())
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),audience=VALUES(audience),module_group=VALUES(module_group),route_pattern=VALUES(route_pattern),admin_only=VALUES(admin_only),default_guest=VALUES(default_guest),default_customer=VALUES(default_customer),default_shopkeeper=VALUES(default_shopkeeper),active=1,updated_at=NOW();
INSERT IGNORE INTO access_role_features_v1300(tenant_id,role_key,feature_key,enabled,updated_at)
SELECT r.tenant_id,r.role_key,'admin.education_lms',1,NOW() FROM access_roles_v1300 r WHERE r.role_key='tenant_admin' AND r.active=1;
