-- ShahkotPK v7.0.1 Super App Route & Schema Repair
-- Replays v7.0.0 additive CREATE TABLE IF NOT EXISTS statements so partial migrations self-heal.
-- ShahkotPK v7.0.0 Multi-Tenant Super App Expansion Suite
-- Education + Transport/Ride/Delivery + Tourism/Hotel/Restaurant + Full PWA Super App
-- Additive/idempotent migration. No DROP/TRUNCATE statements.

CREATE TABLE IF NOT EXISTS tenant_superapp_settings_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 education_enabled TINYINT(1) NOT NULL DEFAULT 1, transport_enabled TINYINT(1) NOT NULL DEFAULT 1,
 tourism_enabled TINYINT(1) NOT NULL DEFAULT 1, pwa_enabled TINYINT(1) NOT NULL DEFAULT 1,
 education_approval_required TINYINT(1) NOT NULL DEFAULT 1, transport_approval_required TINYINT(1) NOT NULL DEFAULT 1,
 tourism_approval_required TINYINT(1) NOT NULL DEFAULT 1, ride_enabled TINYINT(1) NOT NULL DEFAULT 1,
 delivery_enabled TINYINT(1) NOT NULL DEFAULT 1, hotel_booking_enabled TINYINT(1) NOT NULL DEFAULT 1,
 restaurant_booking_enabled TINYINT(1) NOT NULL DEFAULT 1, app_name VARCHAR(190) NOT NULL DEFAULT 'ShahkotPK Super App',
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_tenant_settings(tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO tenant_superapp_settings_v700(tenant_id) VALUES (0);

CREATE TABLE IF NOT EXISTS superapp_staff_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NOT NULL,
 access_role VARCHAR(80) NOT NULL DEFAULT 'analyst', scope_type VARCHAR(40) NULL, scope_id BIGINT UNSIGNED NULL,
 permissions_json LONGTEXT NULL, status TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_staff(tenant_id,user_id,access_role,scope_type,scope_id), KEY ix_v700_staff(tenant_id,status,access_role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS superapp_activity_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NULL,
 module VARCHAR(60) NOT NULL, action VARCHAR(80) NOT NULL, entity_type VARCHAR(80) NULL, entity_id BIGINT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id), KEY ix_v700_activity(tenant_id,module,action,created_at), KEY ix_v700_activity_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS superapp_devices_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NULL,
 device_key CHAR(64) NOT NULL, platform VARCHAR(40) NULL, app_version VARCHAR(30) NULL, push_endpoint TEXT NULL,
 last_seen_at DATETIME NULL, status TINYINT(1) NOT NULL DEFAULT 1, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_device(tenant_id,device_key), KEY ix_v700_device_user(user_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EDUCATION
CREATE TABLE IF NOT EXISTS edu_institutions_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, city_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 owner_user_id BIGINT UNSIGNED NULL, name VARCHAR(220) NOT NULL, slug VARCHAR(240) NOT NULL, institution_type ENUM('school','college','academy','university','training_center','online_academy') NOT NULL DEFAULT 'academy',
 tagline VARCHAR(255) NULL, description TEXT NULL, logo_url VARCHAR(600) NULL, cover_url VARCHAR(600) NULL, phone VARCHAR(80) NULL, email VARCHAR(190) NULL, website VARCHAR(300) NULL,
 address TEXT NULL, area VARCHAR(190) NULL, latitude DECIMAL(10,7) NULL, longitude DECIMAL(10,7) NULL,
 verified TINYINT(1) NOT NULL DEFAULT 0, featured TINYINT(1) NOT NULL DEFAULT 0, subscription_status ENUM('trial','active','expired','waived','suspended') NOT NULL DEFAULT 'trial', subscription_plan VARCHAR(100) NULL, subscription_expires_at DATETIME NULL,
 status ENUM('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_edu_slug(tenant_id,slug), KEY ix_v700_edu_inst(tenant_id,status,featured,institution_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS edu_programs_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, institution_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(220) NOT NULL, program_type ENUM('class','degree','course','diploma','certificate','tuition','online_course') NOT NULL DEFAULT 'course',
 description TEXT NULL, duration_text VARCHAR(120) NULL, fee_amount DECIMAL(14,2) NOT NULL DEFAULT 0, admission_fee DECIMAL(14,2) NOT NULL DEFAULT 0,
 seats INT NOT NULL DEFAULT 0, start_date DATE NULL, end_date DATE NULL, admission_open TINYINT(1) NOT NULL DEFAULT 1, status ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY ix_v700_edu_program(tenant_id,institution_id,status,admission_open)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS edu_applications_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, city_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 reference_no VARCHAR(70) NOT NULL, user_id BIGINT UNSIGNED NOT NULL, institution_id BIGINT UNSIGNED NOT NULL, program_id BIGINT UNSIGNED NULL,
 applicant_name VARCHAR(190) NOT NULL, phone VARCHAR(80) NOT NULL, email VARCHAR(190) NULL, previous_qualification VARCHAR(255) NULL, message TEXT NULL,
 status ENUM('submitted','under_review','interview','approved','rejected','enrolled','withdrawn') NOT NULL DEFAULT 'submitted', assigned_user_id BIGINT UNSIGNED NULL, admin_notes TEXT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_edu_app_ref(reference_no), KEY ix_v700_edu_app(tenant_id,institution_id,status,created_at), KEY ix_v700_edu_app_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS edu_enrollments_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, institution_id BIGINT UNSIGNED NOT NULL, program_id BIGINT UNSIGNED NULL,
 user_id BIGINT UNSIGNED NOT NULL, application_id BIGINT UNSIGNED NULL, roll_no VARCHAR(80) NULL, start_date DATE NULL, end_date DATE NULL,
 status ENUM('active','completed','paused','cancelled') NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_roll(tenant_id,institution_id,roll_no), KEY ix_v700_enroll(tenant_id,institution_id,status,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS edu_attendance_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, enrollment_id BIGINT UNSIGNED NOT NULL, attendance_date DATE NOT NULL,
 status ENUM('present','absent','late','leave') NOT NULL DEFAULT 'present', note VARCHAR(500) NULL, marked_by BIGINT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_attendance(enrollment_id,attendance_date), KEY ix_v700_attendance_tenant(tenant_id,attendance_date,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS edu_fee_invoices_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, enrollment_id BIGINT UNSIGNED NOT NULL, invoice_no VARCHAR(70) NOT NULL,
 title VARCHAR(190) NOT NULL DEFAULT 'Fee Invoice', amount DECIMAL(14,2) NOT NULL DEFAULT 0, paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0, due_date DATE NULL,
 status ENUM('unpaid','partial','paid','waived','overdue') NOT NULL DEFAULT 'unpaid', notes VARCHAR(500) NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_fee_no(invoice_no), KEY ix_v700_fee(tenant_id,enrollment_id,status,due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TRANSPORT, RIDE & DELIVERY
CREATE TABLE IF NOT EXISTS transport_operators_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, city_id BIGINT UNSIGNED NOT NULL DEFAULT 0, owner_user_id BIGINT UNSIGNED NULL,
 name VARCHAR(220) NOT NULL, operator_type ENUM('bus','van','rickshaw','taxi','bike','delivery','mixed') NOT NULL DEFAULT 'mixed', phone VARCHAR(80) NULL, email VARCHAR(190) NULL,
 address TEXT NULL, logo_url VARCHAR(600) NULL, verified TINYINT(1) NOT NULL DEFAULT 0, subscription_status ENUM('trial','active','expired','waived','suspended') NOT NULL DEFAULT 'trial', subscription_expires_at DATETIME NULL,
 status ENUM('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY ix_v700_operator(tenant_id,status,operator_type,verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transport_vehicles_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, operator_id BIGINT UNSIGNED NULL, assigned_user_id BIGINT UNSIGNED NULL,
 vehicle_type ENUM('bus','van','car','rickshaw','bike','truck','loader') NOT NULL DEFAULT 'car', registration_no VARCHAR(100) NOT NULL, title VARCHAR(190) NULL, seats INT NOT NULL DEFAULT 4,
 make_model VARCHAR(190) NULL, color VARCHAR(80) NULL, image_url VARCHAR(600) NULL, current_latitude DECIMAL(10,7) NULL, current_longitude DECIMAL(10,7) NULL,
 status ENUM('active','available','busy','maintenance','inactive') NOT NULL DEFAULT 'available', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_vehicle(tenant_id,registration_no), KEY ix_v700_vehicle_status(tenant_id,status,vehicle_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transport_routes_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, operator_id BIGINT UNSIGNED NULL, name VARCHAR(220) NOT NULL,
 origin VARCHAR(190) NOT NULL, destination VARCHAR(190) NOT NULL, stops_json LONGTEXT NULL, distance_km DECIMAL(10,2) NULL, estimated_minutes INT NULL, base_fare DECIMAL(14,2) NOT NULL DEFAULT 0,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY ix_v700_route(tenant_id,status,origin,destination)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transport_trips_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, route_id BIGINT UNSIGNED NOT NULL, vehicle_id BIGINT UNSIGNED NULL, driver_user_id BIGINT UNSIGNED NULL,
 departure_at DATETIME NOT NULL, arrival_at DATETIME NULL, fare DECIMAL(14,2) NOT NULL DEFAULT 0, available_seats INT NOT NULL DEFAULT 0,
 status ENUM('scheduled','boarding','departed','completed','cancelled') NOT NULL DEFAULT 'scheduled', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY ix_v700_trip(tenant_id,status,departure_at,route_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transport_bookings_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NOT NULL, trip_id BIGINT UNSIGNED NOT NULL,
 booking_no VARCHAR(70) NOT NULL, seats INT NOT NULL DEFAULT 1, pickup_point VARCHAR(190) NULL, total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 payment_status ENUM('unpaid','paid','waived','refunded') NOT NULL DEFAULT 'unpaid', status ENUM('booked','confirmed','boarded','completed','cancelled') NOT NULL DEFAULT 'booked',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_trip_booking(booking_no), KEY ix_v700_booking(tenant_id,user_id,status,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ride_requests_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NOT NULL, request_no VARCHAR(70) NOT NULL,
 service_type ENUM('ride','rickshaw','taxi','bike') NOT NULL DEFAULT 'ride', assigned_operator_id BIGINT UNSIGNED NULL, assigned_vehicle_id BIGINT UNSIGNED NULL, assigned_driver_user_id BIGINT UNSIGNED NULL,
 pickup_address TEXT NOT NULL, destination_address TEXT NOT NULL, pickup_latitude DECIMAL(10,7) NULL, pickup_longitude DECIMAL(10,7) NULL, destination_latitude DECIMAL(10,7) NULL, destination_longitude DECIMAL(10,7) NULL,
 scheduled_at DATETIME NULL, estimated_fare DECIMAL(14,2) NOT NULL DEFAULT 0, final_fare DECIMAL(14,2) NOT NULL DEFAULT 0, notes TEXT NULL,
 status ENUM('requested','assigned','driver_arriving','picked_up','in_trip','completed','cancelled') NOT NULL DEFAULT 'requested',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_ride_no(request_no), KEY ix_v700_ride(tenant_id,status,created_at), KEY ix_v700_ride_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS delivery_orders_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NOT NULL, delivery_no VARCHAR(70) NOT NULL,
 delivery_type ENUM('parcel','document','food','grocery','commerce') NOT NULL DEFAULT 'parcel', assigned_operator_id BIGINT UNSIGNED NULL, assigned_vehicle_id BIGINT UNSIGNED NULL, assigned_driver_user_id BIGINT UNSIGNED NULL,
 pickup_name VARCHAR(190) NULL, pickup_phone VARCHAR(80) NULL, pickup_address TEXT NOT NULL, dropoff_name VARCHAR(190) NULL, dropoff_phone VARCHAR(80) NULL, dropoff_address TEXT NOT NULL,
 item_description TEXT NULL, cod_amount DECIMAL(14,2) NOT NULL DEFAULT 0, delivery_fee DECIMAL(14,2) NOT NULL DEFAULT 0,
 status ENUM('requested','assigned','picked_up','in_transit','delivered','failed','cancelled') NOT NULL DEFAULT 'requested', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_delivery_no(delivery_no), KEY ix_v700_delivery(tenant_id,status,created_at), KEY ix_v700_delivery_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TOURISM, HOTELS & RESTAURANTS
CREATE TABLE IF NOT EXISTS tourism_attractions_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, city_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
 name VARCHAR(220) NOT NULL, slug VARCHAR(240) NOT NULL, category VARCHAR(100) NULL, description TEXT NULL, image_url VARCHAR(600) NULL,
 address TEXT NULL, area VARCHAR(190) NULL, latitude DECIMAL(10,7) NULL, longitude DECIMAL(10,7) NULL, opening_hours VARCHAR(255) NULL, ticket_price DECIMAL(14,2) NOT NULL DEFAULT 0,
 featured TINYINT(1) NOT NULL DEFAULT 0, status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_attraction_slug(tenant_id,slug), KEY ix_v700_attraction(tenant_id,status,featured,category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tourism_hotels_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, city_id BIGINT UNSIGNED NOT NULL DEFAULT 0, owner_user_id BIGINT UNSIGNED NULL,
 name VARCHAR(220) NOT NULL, slug VARCHAR(240) NOT NULL, description TEXT NULL, star_rating DECIMAL(2,1) NOT NULL DEFAULT 0, image_url VARCHAR(600) NULL, gallery_json LONGTEXT NULL,
 phone VARCHAR(80) NULL, email VARCHAR(190) NULL, address TEXT NULL, area VARCHAR(190) NULL, latitude DECIMAL(10,7) NULL, longitude DECIMAL(10,7) NULL, amenities_json LONGTEXT NULL,
 verified TINYINT(1) NOT NULL DEFAULT 0, featured TINYINT(1) NOT NULL DEFAULT 0, subscription_status ENUM('trial','active','expired','waived','suspended') NOT NULL DEFAULT 'trial', subscription_expires_at DATETIME NULL,
 status ENUM('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_hotel_slug(tenant_id,slug), KEY ix_v700_hotel(tenant_id,status,featured,verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tourism_hotel_rooms_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, hotel_id BIGINT UNSIGNED NOT NULL, name VARCHAR(190) NOT NULL,
 room_type VARCHAR(100) NULL, capacity INT NOT NULL DEFAULT 2, quantity INT NOT NULL DEFAULT 1, price_per_night DECIMAL(14,2) NOT NULL DEFAULT 0, image_url VARCHAR(600) NULL, amenities_json LONGTEXT NULL,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), KEY ix_v700_room(tenant_id,hotel_id,status,price_per_night)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hotel_bookings_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NOT NULL, hotel_id BIGINT UNSIGNED NOT NULL, room_id BIGINT UNSIGNED NOT NULL,
 booking_no VARCHAR(70) NOT NULL, check_in DATE NOT NULL, check_out DATE NOT NULL, guests INT NOT NULL DEFAULT 1, total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 payment_status ENUM('unpaid','paid','waived','refunded') NOT NULL DEFAULT 'unpaid', status ENUM('requested','confirmed','checked_in','completed','cancelled') NOT NULL DEFAULT 'requested', guest_name VARCHAR(190) NULL, guest_phone VARCHAR(80) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_hotel_booking(booking_no), KEY ix_v700_hotel_book(tenant_id,hotel_id,status,check_in), KEY ix_v700_hotel_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tourism_restaurants_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, city_id BIGINT UNSIGNED NOT NULL DEFAULT 0, owner_user_id BIGINT UNSIGNED NULL,
 name VARCHAR(220) NOT NULL, slug VARCHAR(240) NOT NULL, cuisine VARCHAR(190) NULL, description TEXT NULL, image_url VARCHAR(600) NULL, gallery_json LONGTEXT NULL,
 phone VARCHAR(80) NULL, address TEXT NULL, area VARCHAR(190) NULL, latitude DECIMAL(10,7) NULL, longitude DECIMAL(10,7) NULL, opening_hours VARCHAR(255) NULL,
 verified TINYINT(1) NOT NULL DEFAULT 0, featured TINYINT(1) NOT NULL DEFAULT 0, subscription_status ENUM('trial','active','expired','waived','suspended') NOT NULL DEFAULT 'trial', subscription_expires_at DATETIME NULL,
 status ENUM('pending','active','suspended','rejected') NOT NULL DEFAULT 'pending', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_rest_slug(tenant_id,slug), KEY ix_v700_rest(tenant_id,status,featured,cuisine)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS restaurant_bookings_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NOT NULL, restaurant_id BIGINT UNSIGNED NOT NULL,
 booking_no VARCHAR(70) NOT NULL, booking_date DATE NOT NULL, booking_time TIME NOT NULL, guests INT NOT NULL DEFAULT 2, customer_name VARCHAR(190) NOT NULL, customer_phone VARCHAR(80) NOT NULL,
 special_request TEXT NULL, status ENUM('requested','confirmed','seated','completed','cancelled','no_show') NOT NULL DEFAULT 'requested', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_rest_booking(booking_no), KEY ix_v700_rest_book(tenant_id,restaurant_id,status,booking_date), KEY ix_v700_rest_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tourism_packages_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, title VARCHAR(220) NOT NULL, slug VARCHAR(240) NOT NULL,
 description TEXT NULL, image_url VARCHAR(600) NULL, duration_text VARCHAR(120) NULL, includes_json LONGTEXT NULL, price_per_person DECIMAL(14,2) NOT NULL DEFAULT 0, capacity INT NOT NULL DEFAULT 0,
 start_location VARCHAR(190) NULL, featured TINYINT(1) NOT NULL DEFAULT 0, status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_tour_slug(tenant_id,slug), KEY ix_v700_tour(tenant_id,status,featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tourism_package_bookings_v700 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 0, user_id BIGINT UNSIGNED NOT NULL, package_id BIGINT UNSIGNED NOT NULL,
 booking_no VARCHAR(70) NOT NULL, travel_date DATE NOT NULL, people INT NOT NULL DEFAULT 1, total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
 payment_status ENUM('unpaid','paid','waived','refunded') NOT NULL DEFAULT 'unpaid', status ENUM('requested','confirmed','completed','cancelled') NOT NULL DEFAULT 'requested', customer_name VARCHAR(190) NOT NULL, customer_phone VARCHAR(80) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_v700_tour_booking(booking_no), KEY ix_v700_tour_book(tenant_id,package_id,status,travel_date), KEY ix_v700_tour_user(user_id,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
