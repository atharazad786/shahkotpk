-- ShahkotPK v5.8.0 Doctor Online — Digital Healthcare Platform Pro
-- Additive/idempotent schema. No DROP/TRUNCATE statements.

CREATE TABLE IF NOT EXISTS health_specialties_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  icon VARCHAR(40) NULL,
  description VARCHAR(500) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_specialty_tenant_slug(tenant_id,slug), KEY ix_health_specialty_status(tenant_id,status,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_organizations_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  city_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  owner_user_id BIGINT UNSIGNED NULL,
  org_type ENUM('hospital','clinic','eclinic','diagnostic','other') NOT NULL DEFAULT 'clinic',
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  registration_no VARCHAR(120) NULL,
  phone VARCHAR(80) NULL,
  emergency_phone VARCHAR(80) NULL,
  email VARCHAR(190) NULL,
  address TEXT NULL,
  area VARCHAR(190) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  map_url VARCHAR(600) NULL,
  logo_url VARCHAR(600) NULL,
  cover_url VARCHAR(600) NULL,
  facilities_json LONGTEXT NULL,
  opening_hours_json LONGTEXT NULL,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  emergency_available TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('pending','active','inactive','rejected') NOT NULL DEFAULT 'pending',
  created_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_org_tenant_slug(tenant_id,slug), KEY ix_health_org_city(tenant_id,city_id,status), KEY ix_health_org_owner(owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_doctors_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  city_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  specialty_id BIGINT UNSIGNED NULL,
  primary_org_id BIGINT UNSIGNED NULL,
  display_name VARCHAR(190) NOT NULL,
  slug VARCHAR(220) NOT NULL,
  gender VARCHAR(40) NULL,
  qualification VARCHAR(400) NULL,
  registration_no VARCHAR(150) NULL,
  registration_body VARCHAR(120) NULL,
  experience_years INT NOT NULL DEFAULT 0,
  languages VARCHAR(300) NULL,
  bio TEXT NULL,
  photo_url VARCHAR(600) NULL,
  online_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  clinic_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  followup_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  consultation_minutes INT NOT NULL DEFAULT 20,
  online_status ENUM('offline','available','busy','away') NOT NULL DEFAULT 'offline',
  instant_consult TINYINT(1) NOT NULL DEFAULT 0,
  video_enabled TINYINT(1) NOT NULL DEFAULT 1,
  audio_enabled TINYINT(1) NOT NULL DEFAULT 1,
  chat_enabled TINYINT(1) NOT NULL DEFAULT 1,
  verified TINYINT(1) NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  approval_status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  rating DECIMAL(3,2) NOT NULL DEFAULT 0,
  review_count INT NOT NULL DEFAULT 0,
  total_consultations INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_doctor_user_tenant(tenant_id,user_id), UNIQUE KEY uq_health_doctor_slug(tenant_id,slug), KEY ix_health_doctor_search(tenant_id,city_id,specialty_id,approval_status,online_status), KEY ix_health_doctor_org(primary_org_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_doctor_orgs_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  doctor_id BIGINT UNSIGNED NOT NULL,
  organization_id BIGINT UNSIGNED NOT NULL,
  role_title VARCHAR(160) NULL,
  fee DECIMAL(12,2) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_doctor_org(doctor_id,organization_id), KEY ix_health_doctor_org_tenant(tenant_id,organization_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_doctor_availability_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  doctor_id BIGINT UNSIGNED NOT NULL,
  organization_id BIGINT UNSIGNED NULL,
  weekday TINYINT UNSIGNED NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  slot_minutes INT NOT NULL DEFAULT 20,
  consultation_mode ENUM('online','in_person','both') NOT NULL DEFAULT 'both',
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_health_availability(doctor_id,weekday,status), KEY ix_health_availability_org(organization_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_patient_profiles_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  date_of_birth DATE NULL,
  gender VARCHAR(40) NULL,
  city VARCHAR(150) NULL,
  emergency_contact_name VARCHAR(190) NULL,
  emergency_contact_phone VARCHAR(80) NULL,
  consent_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_patient_user(tenant_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_family_members_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  owner_user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(190) NOT NULL,
  relation VARCHAR(80) NULL,
  date_of_birth DATE NULL,
  gender VARCHAR(40) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_health_family_owner(tenant_id,owner_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_appointments_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  city_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  appointment_no VARCHAR(40) NOT NULL,
  doctor_id BIGINT UNSIGNED NOT NULL,
  organization_id BIGINT UNSIGNED NULL,
  patient_user_id BIGINT UNSIGNED NOT NULL,
  family_member_id BIGINT UNSIGNED NULL,
  patient_name VARCHAR(190) NOT NULL,
  patient_phone VARCHAR(80) NULL,
  patient_email VARCHAR(190) NULL,
  consultation_mode ENUM('video','audio','chat','in_person') NOT NULL DEFAULT 'video',
  scheduled_at DATETIME NOT NULL,
  duration_minutes INT NOT NULL DEFAULT 20,
  reason VARCHAR(600) NULL,
  private_patient_note TEXT NULL,
  fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  fee_source ENUM('doctor','admin_manual','subscription','promo') NOT NULL DEFAULT 'doctor',
  payment_method VARCHAR(60) NULL,
  payment_status ENUM('unpaid','pending','paid','waived','refunded') NOT NULL DEFAULT 'unpaid',
  payment_order_id BIGINT UNSIGNED NULL,
  subscription_id BIGINT UNSIGNED NULL,
  status ENUM('requested','confirmed','waiting','in_progress','completed','cancelled','no_show','rejected') NOT NULL DEFAULT 'requested',
  cancellation_reason VARCHAR(500) NULL,
  confirmed_at DATETIME NULL,
  started_at DATETIME NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_appointment_no(appointment_no), KEY ix_health_appt_doctor(tenant_id,doctor_id,scheduled_at,status), KEY ix_health_appt_patient(patient_user_id,scheduled_at,status), KEY ix_health_appt_org(organization_id,scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_consultations_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  appointment_id BIGINT UNSIGNED NOT NULL,
  room_token VARCHAR(80) NOT NULL,
  doctor_user_id BIGINT UNSIGNED NOT NULL,
  patient_user_id BIGINT UNSIGNED NOT NULL,
  status ENUM('waiting','active','ended') NOT NULL DEFAULT 'waiting',
  doctor_joined_at DATETIME NULL,
  patient_joined_at DATETIME NULL,
  ended_at DATETIME NULL,
  doctor_summary TEXT NULL,
  followup_note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_consultation_appt(appointment_id), UNIQUE KEY uq_health_room_token(room_token), KEY ix_health_consultation_users(doctor_user_id,patient_user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_chat_messages_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  consultation_id BIGINT UNSIGNED NOT NULL,
  sender_user_id BIGINT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  message_type ENUM('text','system') NOT NULL DEFAULT 'text',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_health_chat_room(consultation_id,id), KEY ix_health_chat_sender(sender_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_call_signals_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  consultation_id BIGINT UNSIGNED NOT NULL,
  sender_user_id BIGINT UNSIGNED NOT NULL,
  signal_type ENUM('offer','answer','ice','hangup') NOT NULL,
  payload_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_health_signal_room(consultation_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_prescriptions_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  appointment_id BIGINT UNSIGNED NOT NULL,
  doctor_id BIGINT UNSIGNED NOT NULL,
  patient_user_id BIGINT UNSIGNED NOT NULL,
  diagnosis_note TEXT NULL,
  medicines_json LONGTEXT NULL,
  tests_json LONGTEXT NULL,
  advice TEXT NULL,
  followup_date DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_health_rx_patient(patient_user_id,created_at), KEY ix_health_rx_doctor(doctor_id,created_at), UNIQUE KEY uq_health_rx_appointment(appointment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_reviews_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  doctor_id BIGINT UNSIGNED NOT NULL,
  appointment_id BIGINT UNSIGNED NOT NULL,
  patient_user_id BIGINT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  review TEXT NULL,
  status ENUM('pending','published','hidden') NOT NULL DEFAULT 'published',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_review_appt(appointment_id), KEY ix_health_review_doctor(doctor_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_subscription_plans_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  audience ENUM('patient','doctor','organization') NOT NULL,
  name VARCHAR(190) NOT NULL,
  slug VARCHAR(210) NOT NULL,
  description VARCHAR(600) NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  duration_days INT NOT NULL DEFAULT 30,
  consultation_credits INT NOT NULL DEFAULT 0,
  features_json LONGTEXT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_plan_slug(tenant_id,audience,slug), KEY ix_health_plan_active(tenant_id,audience,status,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_subscriptions_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  plan_id BIGINT UNSIGNED NOT NULL,
  audience ENUM('patient','doctor','organization') NOT NULL,
  user_id BIGINT UNSIGNED NULL,
  doctor_id BIGINT UNSIGNED NULL,
  organization_id BIGINT UNSIGNED NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_method VARCHAR(80) NULL,
  payment_status ENUM('unpaid','pending','paid','waived','refunded') NOT NULL DEFAULT 'unpaid',
  payment_order_id BIGINT UNSIGNED NULL,
  starts_at DATETIME NULL,
  ends_at DATETIME NULL,
  remaining_consults INT NOT NULL DEFAULT 0,
  status ENUM('pending','active','expired','cancelled') NOT NULL DEFAULT 'pending',
  assigned_by BIGINT UNSIGNED NULL,
  notes VARCHAR(600) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_health_sub_user(tenant_id,user_id,status,ends_at), KEY ix_health_sub_doctor(doctor_id,status), KEY ix_health_sub_org(organization_id,status), KEY ix_health_sub_plan(plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_staff_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NOT NULL,
  doctor_id BIGINT UNSIGNED NULL,
  organization_id BIGINT UNSIGNED NULL,
  access_role ENUM('health_admin','doctor','hospital_manager','clinic_manager','receptionist','subscription_manager','analyst') NOT NULL DEFAULT 'receptionist',
  permissions_json LONGTEXT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  assigned_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY(id), UNIQUE KEY uq_health_staff_scope(tenant_id,user_id,doctor_id,organization_id,access_role), KEY ix_health_staff_user(tenant_id,user_id,status), KEY ix_health_staff_org(organization_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS health_activity_v580 (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
  user_id BIGINT UNSIGNED NULL,
  visitor_hash VARCHAR(80) NULL,
  event_type VARCHAR(80) NOT NULL,
  entity_type VARCHAR(60) NULL,
  entity_id BIGINT UNSIGNED NULL,
  meta_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id), KEY ix_health_activity(tenant_id,event_type,created_at), KEY ix_health_activity_entity(entity_type,entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO health_specialties_v580(tenant_id,name,slug,icon,description,status,sort_order) VALUES
(0,'General Physician','general-physician','✚','Primary care and general medical consultations.',1,10),
(0,'Pediatrics','pediatrics','◉','Child and adolescent health consultations.',1,20),
(0,'Gynecology','gynecology','♡','Women health consultations.',1,30),
(0,'Dermatology','dermatology','✦','Skin, hair and nail consultations.',1,40),
(0,'Cardiology','cardiology','♥','Heart and cardiovascular consultations.',1,50),
(0,'ENT','ent','◌','Ear, nose and throat consultations.',1,60),
(0,'Orthopedics','orthopedics','◆','Bone, joint and musculoskeletal consultations.',1,70),
(0,'Psychology','psychology','◈','Mental wellbeing consultations.',1,80),
(0,'Nutrition','nutrition','◎','Diet and nutrition consultations.',1,90),
(0,'Dentistry','dentistry','◇','Dental consultations and referrals.',1,100);

INSERT IGNORE INTO health_subscription_plans_v580(tenant_id,audience,name,slug,description,price,duration_days,consultation_credits,features_json,status,sort_order) VALUES
(0,'patient','Doctor Online Basic','doctor-online-basic','Patient plan with one online consultation credit.',499,30,1,'["Online appointment booking","Secure consultation room","Digital prescription history"]',1,10),
(0,'patient','Family Care','family-care','Patient/family plan with three consultation credits.',1299,30,3,'["3 consultation credits","Family profiles","Priority booking"]',1,20),
(0,'doctor','Doctor Starter','doctor-starter','Doctor listing and online consultation profile.',1499,30,0,'["Doctor portal","Appointment calendar","Online consultation room","E-prescriptions"]',1,10),
(0,'doctor','Doctor Pro','doctor-pro','Featured doctor tools and analytics.',2999,30,0,'["Featured profile","Doctor analytics","Priority listing","Online consultations"]',1,20),
(0,'organization','Clinic Digital','clinic-digital','Clinic/hospital digital access plan.',4999,30,0,'["Multi-doctor organization","Reception desk","Appointments","Organization analytics"]',1,10);
