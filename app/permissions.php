<?php
declare(strict_types=1);

function permission_registry(): array {
    return [
        'dashboard.view'=>'Dashboard Overview','widgets.manage'=>'Dashboard Widgets','themes.manage'=>'Theme Studio','homepage.manage'=>'Homepage Builder','banners.manage'=>'Banner Manager','ticker.manage'=>'Information Ticker',
        'businesses.manage'=>'Businesses','categories.manage'=>'Categories','cities.manage'=>'Cities','maps.manage'=>'Maps & Location',
        'city_content.manage'=>'All City Content (legacy combined)','city_guide.manage'=>'City Guide','deals.manage'=>'Deals & Offers','events.manage'=>'Events','jobs.manage'=>'Jobs','property.manage'=>'Property',
        'bloodbank.dashboard'=>'Blood Bank Dashboard','bloodbank.banks.manage'=>'Blood Bank Locations','bloodbank.donors.manage'=>'Blood Donors','bloodbank.inventory.manage'=>'Blood Inventory','bloodbank.donations.manage'=>'Blood Donations','bloodbank.requests.manage'=>'Blood Requests','bloodbank.appointments.manage'=>'Donation Appointments','bloodbank.camps.manage'=>'Donation Camps','bloodbank.analytics'=>'Blood Bank Analytics','bloodbank.settings'=>'Blood Bank Settings',
        'health.dashboard'=>'Doctor Online Dashboard','health.doctors.manage'=>'Doctors','health.organizations.manage'=>'Hospitals & Clinics','health.specialties.manage'=>'Medical Specialties','health.appointments.manage'=>'Healthcare Appointments','health.consultations.manage'=>'Online Consultations','health.patients.manage'=>'Patient Desk','health.subscriptions.manage'=>'Healthcare Subscriptions','health.staff.manage'=>'Healthcare Staff & Roles','health.analytics'=>'Healthcare Analytics','health.settings'=>'Doctor Online Settings',
        'healthnet.dashboard'=>'Healthcare Network Dashboard','healthnet.pharmacies.manage'=>'Pharmacy Network','healthnet.labs.manage'=>'Diagnostic Labs','healthnet.ambulance.manage'=>'Ambulance & Dispatch','healthnet.orders.manage'=>'Pharmacy Orders','healthnet.bookings.manage'=>'Lab Bookings','healthnet.reports.manage'=>'Lab Reports','healthnet.subscriptions.manage'=>'Healthcare Network Plans','healthnet.staff.manage'=>'Healthcare Network Staff','healthnet.analytics'=>'Healthcare Network Analytics','healthnet.settings'=>'Healthcare Network Settings',
        'smartcity.dashboard'=>'Smart City Command Center','smartcity.municipal.manage'=>'Municipal Services & Complaints','smartcity.wallet.manage'=>'Citizen Wallet','smartcity.loyalty.manage'=>'Loyalty & Rewards','smartcity.notifications.manage'=>'Smart Notifications','smartcity.ai.manage'=>'AI Assistant & Recommendations','smartcity.staff.manage'=>'Smart City Staff & Roles','smartcity.analytics'=>'Smart City Analytics','smartcity.settings'=>'Smart City Settings',
        'superapp.dashboard'=>'v7 Super App Command Center','education.institutions.manage'=>'Education Institutions & Programs','education.admissions.manage'=>'Education Admissions','education.students.manage'=>'Education Students & Attendance','education.finance.manage'=>'Education Fees & Finance','transport.operators.manage'=>'Transport Operators & Fleet','transport.dispatch.manage'=>'Ride & Delivery Dispatch','transport.bookings.manage'=>'Transport Bookings','tourism.places.manage'=>'Tourism Places, Hotels & Restaurants','tourism.bookings.manage'=>'Tourism Bookings','superapp.staff.manage'=>'v7 Staff & Role Assignments','superapp.tenant.manage'=>'v7 Tenant Module Settings','superapp.analytics'=>'v7 Super App Analytics',
        'news.manage'=>'News Portal','live.manage'=>'Live Broadcast Center','engagement.manage'=>'Popups & Notifications',
        'blog.dashboard'=>'Blog Dashboard','blog.posts.create'=>'Blog: Create Posts','blog.posts.edit_own'=>'Blog: Edit Own Posts','blog.posts.edit_all'=>'Blog: Edit All Posts','blog.posts.delete_own'=>'Blog: Delete Own Posts','blog.posts.delete_all'=>'Blog: Delete All Posts','blog.posts.publish'=>'Blog: Publish/Schedule','blog.categories.manage'=>'Blog Categories','blog.tags.manage'=>'Blog Tags','blog.comments.manage'=>'Blog Comments','blog.widgets.manage'=>'Blog Homepage Widgets',
        'users.manage'=>'Users','users.assign_staff_role'=>'Assign Staff / Custom Roles','users.assign_admin'=>'Manage Admin Core Accounts','roles.manage'=>'Roles & Permissions',
        'shop.manage'=>'Marketplace Products & Categories','shop.orders.manage'=>'Marketplace Orders','shop.auctions.manage'=>'Marketplace Auctions',
        'reviews.manage'=>'Ratings & Reviews','verification.manage'=>'Business Verification','bookings.manage'=>'Bookings & Appointments','leads.manage'=>'Leads & Inquiry CRM','loyalty.manage'=>'Loyalty & Wallet','referrals.manage'=>'Referrals & Affiliates','whatsapp.manage'=>'WhatsApp Automation','analytics.manage'=>'Advanced Analytics',
        'emergency.manage'=>'Emergency & Public Services','restaurants.manage'=>'Restaurant Menus','services.manage'=>'Service Marketplace','classifieds.manage'=>'Classified Ads','entitlements.manage'=>'Subscription Entitlements','seller_staff.manage'=>'Seller Staff Accounts','moderation.manage'=>'Moderation & Fraud Center','seo.manage'=>'SEO Automation','push.manage'=>'PWA & Push','multi_city.manage'=>'Multi-City Management','reports.manage'=>'Commercial Reports',
        'operations.dashboard'=>'Commercial Operations','payouts.manage'=>'Seller Payouts','commerce_invoices.manage'=>'Commerce Invoices','support.manage'=>'Support Tickets','disputes.manage'=>'Disputes & Refunds','delivery.manage'=>'Delivery Management','inventory.manage'=>'Advanced Inventory','purchasing.manage'=>'Vendor Purchasing','audit.manage'=>'Audit Logs','backups.manage'=>'Backup Manager','queues.manage'=>'Queue & Health',
        'security.manage'=>'Security Command Center','health.manage'=>'System Health & Reliability','logs.manage'=>'Logs & Location History','ai.manage'=>'AI Studio & Smart Search','ai.dashboard'=>'AI Command Center Dashboard','ai.providers.manage'=>'AI Provider Connections','ai.integrations.manage'=>'AI Platform API Integrations','ai.knowledge.manage'=>'AI Knowledge Base','ai.training.manage'=>'AI Manual Training','ai.agents.manage'=>'AI Agents & Tools','ai.actions.approve'=>'AI Action Approvals','ai.actions.execute'=>'AI Controlled Actions','ai.analytics'=>'AI Analytics','ai.settings'=>'AI Tenant Settings','enterprise.dashboard'=>'Enterprise v8 Command Center','voice.manage'=>'Voice AI & IP-PBX','bi.manage'=>'Advanced BI & Forecasting','ops.manage'=>'Reliability, Queue & Monitoring','security.ops.manage'=>'Enterprise Security Operations','enterprise.whitelabel.manage'=>'Enterprise White-Label Controls','enterprise.api.manage'=>'Enterprise API Clients','enterprise.analytics'=>'Enterprise Analytics','franchise.manage'=>'Multi-City Franchise Control','mobile_api.manage'=>'Mobile API Control','pos.manage'=>'Seller POS Administration',
        'monetization.manage'=>'Commercial Growth Command Center','ad_marketplace.manage'=>'Self-Service Ad Marketplace','boosts.manage'=>'Paid Listing Boosts','renewals.manage'=>'Subscription Renewal Center','commissions.manage'=>'Dynamic Commission Engine','qr.manage'=>'QR Center','bulk.manage'=>'Bulk Import / Export','media.manage'=>'Media Library','redirects.manage'=>'SEO / Redirect Console','campaigns.manage'=>'Campaign Manager','inbox.manage'=>'Unified Support Inbox','regression.manage'=>'Regression Test Center',
        'subscriptions.manage'=>'Plans & Subscriptions','ads.manage'=>'Advertisements','payments.manage'=>'Payments & Earnings','accounts.manage'=>'Accounts & Ledger',
        'tenants.manage'=>'Tenant & Franchise Center','settings.manage'=>'Platform Settings','updates.manage'=>'System Updates & Recovery',
    ];
}
function permission_groups(): array {
    return [
        'Dashboard'=>['dashboard.view','widgets.manage','themes.manage'],
        'Website & CMS'=>['homepage.manage','banners.manage','ticker.manage'],
        'Directory'=>['businesses.manage','categories.manage','cities.manage','maps.manage'],
        'City Content'=>['city_content.manage','city_guide.manage','deals.manage','events.manage','jobs.manage','property.manage'],
        'Blood Bank'=>['bloodbank.dashboard','bloodbank.banks.manage','bloodbank.donors.manage','bloodbank.inventory.manage','bloodbank.donations.manage','bloodbank.requests.manage','bloodbank.appointments.manage','bloodbank.camps.manage','bloodbank.analytics','bloodbank.settings'],
        'Doctor Online'=>['health.dashboard','health.doctors.manage','health.organizations.manage','health.specialties.manage','health.appointments.manage','health.consultations.manage','health.patients.manage','health.subscriptions.manage','health.staff.manage','health.analytics','health.settings'],
        'Healthcare Network'=>['healthnet.dashboard','healthnet.pharmacies.manage','healthnet.labs.manage','healthnet.ambulance.manage','healthnet.orders.manage','healthnet.bookings.manage','healthnet.reports.manage','healthnet.subscriptions.manage','healthnet.staff.manage','healthnet.analytics','healthnet.settings'],
        'Smart City v6'=>['smartcity.dashboard','smartcity.municipal.manage','smartcity.wallet.manage','smartcity.loyalty.manage','smartcity.notifications.manage','smartcity.ai.manage','smartcity.staff.manage','smartcity.analytics','smartcity.settings'],
        'Super App v7'=>['superapp.dashboard','education.institutions.manage','education.admissions.manage','education.students.manage','education.finance.manage','transport.operators.manage','transport.dispatch.manage','transport.bookings.manage','tourism.places.manage','tourism.bookings.manage','superapp.staff.manage','superapp.tenant.manage','superapp.analytics'],
        'Newsroom'=>['news.manage'],
        'Live & Engagement'=>['live.manage','engagement.manage'],
        'Marketplace'=>['shop.manage','shop.orders.manage','shop.auctions.manage','restaurants.manage','services.manage','classifieds.manage','seller_staff.manage'],
        'Growth & CRM'=>['reviews.manage','verification.manage','bookings.manage','leads.manage','loyalty.manage','referrals.manage','whatsapp.manage','analytics.manage'],
        'Trust & Operations'=>['emergency.manage','entitlements.manage','moderation.manage','seo.manage','push.manage','multi_city.manage','reports.manage'],
        'Blogging'=>['blog.dashboard','blog.posts.create','blog.posts.edit_own','blog.posts.edit_all','blog.posts.delete_own','blog.posts.delete_all','blog.posts.publish','blog.categories.manage','blog.tags.manage','blog.comments.manage','blog.widgets.manage'],
        'Users & Access'=>['users.manage','users.assign_staff_role','users.assign_admin','roles.manage'],
        'Commercial Operations'=>['operations.dashboard','payouts.manage','commerce_invoices.manage','support.manage','disputes.manage','delivery.manage','inventory.manage','purchasing.manage','audit.manage','backups.manage','queues.manage'],
        'AI Command Center v7.1'=>['ai.dashboard','ai.providers.manage','ai.integrations.manage','ai.knowledge.manage','ai.training.manage','ai.agents.manage','ai.actions.approve','ai.actions.execute','ai.analytics','ai.settings'],
        'Enterprise v8'=>['enterprise.dashboard','voice.manage','bi.manage','ops.manage','security.ops.manage','enterprise.whitelabel.manage','enterprise.api.manage','enterprise.analytics'],
        'Platform v4'=>['security.manage','health.manage','logs.manage','ai.manage','franchise.manage','mobile_api.manage','pos.manage'],
        'Growth & Monetization v4.1'=>['monetization.manage','ad_marketplace.manage','boosts.manage','renewals.manage','commissions.manage','qr.manage','bulk.manage','media.manage','redirects.manage','campaigns.manage','inbox.manage','regression.manage'],
        'Multi-Tenant v5'=>['tenants.manage','franchise.manage','multi_city.manage'],
        'Revenue & Finance'=>['subscriptions.manage','ads.manage','payments.manage','accounts.manage'],
        'System'=>['settings.manage','updates.manage'],
    ];
}
function core_role_registry(): array {
    return [
        'user'=>['label'=>'User','description'=>'General public member/customer account.','admin_access'=>false,'permissions'=>[]],
        'customer'=>['label'=>'Customer (Legacy)','description'=>'Existing public customer role retained for compatibility.','admin_access'=>false,'permissions'=>[]],
        'shopkeeper'=>['label'=>'Shopkeeper','description'=>'Business owner with the dedicated shopkeeper dashboard.','admin_access'=>false,'permissions'=>[]],
        'blogger'=>['label'=>'Blogger','description'=>'Writer who can create, edit and delete only their own blog drafts/posts.','admin_access'=>true,'permissions'=>['blog.dashboard','blog.posts.create','blog.posts.edit_own','blog.posts.delete_own']],
        'editor'=>['label'=>'Editor','description'=>'Editorial staff for Blog, News, Homepage and City Content.','admin_access'=>true,'permissions'=>['dashboard.view','homepage.manage','banners.manage','ticker.manage','news.manage','blog.dashboard','blog.posts.create','blog.posts.edit_all','blog.posts.delete_all','blog.posts.publish','blog.categories.manage','blog.tags.manage','blog.comments.manage','blog.widgets.manage','city_guide.manage','deals.manage','events.manage','jobs.manage','property.manage','reviews.manage','moderation.manage']],
        'admin'=>['label'=>'Admin','description'=>'Administrator. Admin without a custom role is Super Admin.','admin_access'=>true,'permissions'=>[]],
    ];
}
function role_templates(): array {
    return [
        'blogger'=>['name'=>'Blogger','slug'=>'blogger','description'=>'Own blog posts only.','permissions'=>core_role_registry()['blogger']['permissions']],
        'editor'=>['name'=>'Editor','slug'=>'editor','description'=>'Editorial control without finance/system administration.','permissions'=>core_role_registry()['editor']['permissions']],
        'news-editor'=>['name'=>'News Editor','slug'=>'news-editor','description'=>'Newsroom access only.','permissions'=>['news.manage']],
        'city-editor'=>['name'=>'City Content Editor','slug'=>'city-content-editor','description'=>'City Guide, Deals, Events, Jobs and Property.','permissions'=>['city_guide.manage','deals.manage','events.manage','jobs.manage','property.manage']],
        'business-manager'=>['name'=>'Business Manager','slug'=>'business-manager','description'=>'Directory, trust, bookings, leads and business analytics.','permissions'=>['dashboard.view','businesses.manage','categories.manage','cities.manage','maps.manage','reviews.manage','verification.manage','bookings.manage','leads.manage','analytics.manage']],
        'blood-bank-manager'=>['name'=>'Blood Bank Manager','slug'=>'blood-bank-manager','description'=>'Manage Blood Bank donors, inventory, donations, requests, appointments, camps and analytics without access to unrelated admin modules.','permissions'=>['bloodbank.dashboard','bloodbank.banks.manage','bloodbank.donors.manage','bloodbank.inventory.manage','bloodbank.donations.manage','bloodbank.requests.manage','bloodbank.appointments.manage','bloodbank.camps.manage','bloodbank.analytics','bloodbank.settings']],
        'doctor-online-manager'=>['name'=>'Doctor Online Manager','slug'=>'doctor-online-manager','description'=>'Manage the Doctor Online healthcare module without unrelated platform administration.','permissions'=>['health.dashboard','health.doctors.manage','health.organizations.manage','health.specialties.manage','health.appointments.manage','health.consultations.manage','health.patients.manage','health.subscriptions.manage','health.staff.manage','health.analytics','health.settings']],
        'live-producer'=>['name'=>'Live Producer','slug'=>'live-producer','description'=>'Manage authorized live broadcasts.','permissions'=>['dashboard.view','live.manage']],
        'commerce-manager'=>['name'=>'Commerce Manager','slug'=>'commerce-manager','description'=>'Manage marketplace products, orders and auctions.','permissions'=>['dashboard.view','shop.manage','shop.orders.manage','shop.auctions.manage']],
        'engagement-manager'=>['name'=>'Engagement Manager','slug'=>'engagement-manager','description'=>'Manage popups and notifications.','permissions'=>['dashboard.view','engagement.manage']],
        'finance-manager'=>['name'=>'Finance Manager','slug'=>'finance-manager','description'=>'Subscriptions, advertisements, payments, ledger, loyalty and reports.','permissions'=>['dashboard.view','subscriptions.manage','ads.manage','payments.manage','accounts.manage','loyalty.manage','referrals.manage','reports.manage','renewals.manage','commissions.manage']],
        'growth-manager'=>['name'=>'Growth Manager','slug'=>'growth-manager','description'=>'Trust, CRM, retention, analytics, campaigns and monetization.','permissions'=>['dashboard.view','reviews.manage','verification.manage','bookings.manage','leads.manage','loyalty.manage','referrals.manage','whatsapp.manage','analytics.manage','moderation.manage','monetization.manage','ad_marketplace.manage','boosts.manage','campaigns.manage','inbox.manage']],
        'monetization-manager'=>['name'=>'Monetization Manager','slug'=>'monetization-manager','description'=>'Ads, boosts, renewals, commissions, QR, campaigns, media and growth operations without system administration.','permissions'=>['dashboard.view','monetization.manage','ad_marketplace.manage','boosts.manage','renewals.manage','commissions.manage','qr.manage','bulk.manage','media.manage','redirects.manage','campaigns.manage','inbox.manage']],
        'operations-manager'=>['name'=>'Operations Manager','slug'=>'operations-manager','description'=>'Emergency, restaurants, services, classifieds and city operations.','permissions'=>['dashboard.view','emergency.manage','restaurants.manage','services.manage','classifieds.manage','multi_city.manage']],
        'platform-security-manager'=>['name'=>'Security Manager','slug'=>'platform-security-manager','description'=>'Security, reliability, audit and backups.','permissions'=>['dashboard.view','security.manage','health.manage','logs.manage','audit.manage','backups.manage','queues.manage']],
        'ai-content-manager'=>['name'=>'AI & Content Manager','slug'=>'ai-content-manager','description'=>'AI Command Center, knowledge/training and editorial dashboards.','permissions'=>['dashboard.view','ai.dashboard','ai.knowledge.manage','ai.training.manage','ai.agents.manage','ai.analytics','news.manage','blog.dashboard','blog.posts.create','blog.posts.edit_all']],
        'ai-operations-manager'=>['name'=>'AI Operations Manager','slug'=>'ai-operations-manager','description'=>'Manage AI providers, integrations, agents, approvals, training and tenant AI settings.','permissions'=>['dashboard.view','ai.dashboard','ai.providers.manage','ai.integrations.manage','ai.knowledge.manage','ai.training.manage','ai.agents.manage','ai.actions.approve','ai.actions.execute','ai.analytics','ai.settings']],
        'enterprise-platform-manager'=>['name'=>'Enterprise Platform Manager','slug'=>'enterprise-platform-manager','description'=>'Full tenant-scoped v8 enterprise command center without unrelated global administration.','permissions'=>['dashboard.view','enterprise.dashboard','voice.manage','bi.manage','ops.manage','security.ops.manage','enterprise.whitelabel.manage','enterprise.api.manage','enterprise.analytics','ai.dashboard','ai.actions.approve','ai.analytics']],
        'call-center-manager'=>['name'=>'Call Center Manager','slug'=>'call-center-manager','description'=>'Manage Voice AI, PBX connectors, queues, extensions and call analytics.','permissions'=>['enterprise.dashboard','voice.manage','enterprise.analytics']],
        'bi-analyst-v8'=>['name'=>'Enterprise BI Analyst','slug'=>'enterprise-bi-analyst','description'=>'Read and generate enterprise BI/forecasting views.','permissions'=>['enterprise.dashboard','bi.manage','enterprise.analytics']],
        'reliability-manager-v8'=>['name'=>'Reliability Manager v8','slug'=>'reliability-manager-v8','description'=>'Operate health checks, queues, monitoring and security events.','permissions'=>['enterprise.dashboard','ops.manage','security.ops.manage','enterprise.analytics']],
        'franchise-manager'=>['name'=>'Franchise Manager','slug'=>'franchise-manager','description'=>'Multi-city franchise, tenant, API and reporting operations.','permissions'=>['dashboard.view','franchise.manage','multi_city.manage','tenants.manage','reports.manage','mobile_api.manage','superapp.dashboard','superapp.tenant.manage','superapp.analytics']],
        'tenant-manager'=>['name'=>'Tenant Manager','slug'=>'tenant-manager','description'=>'White-label city tenant operations without global system administration.','permissions'=>['dashboard.view','tenants.manage','businesses.manage','categories.manage','city_guide.manage','deals.manage','events.manage','jobs.manage','property.manage','news.manage','blog.dashboard','blog.posts.create','blog.posts.edit_all','blog.posts.publish','shop.manage','shop.orders.manage','reviews.manage','bookings.manage','leads.manage','analytics.manage','reports.manage','superapp.dashboard','education.institutions.manage','education.admissions.manage','education.students.manage','education.finance.manage','transport.operators.manage','transport.dispatch.manage','transport.bookings.manage','tourism.places.manage','tourism.bookings.manage','superapp.staff.manage','superapp.tenant.manage','superapp.analytics','ai.dashboard','ai.providers.manage','ai.integrations.manage','ai.knowledge.manage','ai.training.manage','ai.agents.manage','ai.actions.approve','ai.actions.execute','ai.analytics','ai.settings','enterprise.dashboard','voice.manage','bi.manage','ops.manage','security.ops.manage','enterprise.whitelabel.manage','enterprise.api.manage','enterprise.analytics']],
        'pos-manager'=>['name'=>'POS Manager','slug'=>'pos-manager','description'=>'Seller POS and inventory oversight.','permissions'=>['dashboard.view','pos.manage','inventory.manage','shop.orders.manage']],
        'settlement-manager'=>['name'=>'Settlement Manager','slug'=>'settlement-manager','description'=>'Payments, invoices, settlements and seller payouts.','permissions'=>['dashboard.view','operations.dashboard','payments.manage','payouts.manage','commerce_invoices.manage','accounts.manage','reports.manage']],
        'support-manager'=>['name'=>'Support Manager','slug'=>'support-manager','description'=>'Customer support and dispute resolution.','permissions'=>['dashboard.view','operations.dashboard','support.manage','disputes.manage']],
        'warehouse-manager'=>['name'=>'Warehouse Manager','slug'=>'warehouse-manager','description'=>'Inventory, delivery and vendor purchasing.','permissions'=>['dashboard.view','operations.dashboard','inventory.manage','delivery.manage','purchasing.manage']],
        'user-manager'=>['name'=>'User Manager','slug'=>'user-manager','description'=>'Manage users without ability to assign the Super Admin core role.','permissions'=>['dashboard.view','users.manage']],
    ];
}
function ensure_user_profile_row(int $id): void {try{db()->prepare("INSERT IGNORE INTO user_profiles(user_id,approval_status,approval_source,approved_at) VALUES(?,'approved','auto',NOW())")->execute([$id]);}catch(Throwable $e){}}
function get_user_profile(int $id): array {ensure_user_profile_row($id);try{$q=db()->prepare("SELECT up.*,sr.name custom_role_name,sr.slug custom_role_slug,sr.permissions_json,sr.status custom_role_status FROM user_profiles up LEFT JOIN system_roles sr ON sr.id=up.custom_role_id WHERE up.user_id=? LIMIT 1");$q->execute([$id]);return $q->fetch()?:[];}catch(Throwable $e){return [];}}
function user_approval_status(int $id): string {$p=get_user_profile($id);return (string)($p['approval_status']??'approved');}
function user_is_approved(int $id): bool {return user_approval_status($id)==='approved';}
function approval_mode_for_role(string $role): string {$mode=(string)setting($role==='shopkeeper'?'shopkeeper_approval_mode':'customer_approval_mode',$role==='shopkeeper'?'manual':'auto');return in_array($mode,['auto','manual','package_payment'],true)?$mode:'manual';}
function is_super_admin(?array $u=null): bool {$u=$u?:current_user();if(!$u||($u['role']??'')!=='admin')return false;$p=get_user_profile((int)$u['id']);if(!empty($p['custom_role_id']))return false;if(function_exists('tenant_restrict_super_admin')&&tenant_restrict_super_admin((int)$u['id']))return false;return true;}
function bloodbank_staff_permissions_for_user_v570(int $userId): array {
    if($userId<1)return [];try{
        $tid=function_exists('tenant_id')?tenant_id():0;
        $sql='SELECT access_role,permissions_json FROM blood_bank_staff_v570 WHERE user_id=? AND status=1'.($tid>0?' AND tenant_id=?':'');
        $q=db()->prepare($sql);$q->execute($tid>0?[$userId,$tid]:[$userId]);$out=[];
        $roles=[
          'manager'=>['bloodbank.dashboard','bloodbank.banks.manage','bloodbank.donors.manage','bloodbank.inventory.manage','bloodbank.donations.manage','bloodbank.requests.manage','bloodbank.appointments.manage','bloodbank.camps.manage','bloodbank.analytics','bloodbank.settings'],
          'inventory'=>['bloodbank.dashboard','bloodbank.inventory.manage','bloodbank.donations.manage','bloodbank.analytics'],
          'donor_officer'=>['bloodbank.dashboard','bloodbank.donors.manage','bloodbank.donations.manage','bloodbank.appointments.manage','bloodbank.camps.manage'],
          'request_desk'=>['bloodbank.dashboard','bloodbank.requests.manage','bloodbank.inventory.manage','bloodbank.appointments.manage'],
          'camp_coordinator'=>['bloodbank.dashboard','bloodbank.camps.manage','bloodbank.donors.manage','bloodbank.appointments.manage'],
          'viewer'=>['bloodbank.dashboard','bloodbank.analytics']
        ];
        foreach($q->fetchAll()?:[] as $r){$p=json_decode((string)($r['permissions_json']??''),true);if(!is_array($p)||!$p)$p=$roles[(string)($r['access_role']??'viewer')]??$roles['viewer'];$out=array_merge($out,$p);}
        return array_values(array_unique(array_intersect(array_keys(permission_registry()),$out)));
    }catch(Throwable $e){return [];}
}
function health_staff_permissions_for_user_v580(int $userId): array {
    if($userId<1)return [];try{
        $tid=function_exists('tenant_id')?tenant_id():0;
        $sql='SELECT access_role,permissions_json FROM health_staff_v580 WHERE user_id=? AND status=1'.($tid>0?' AND tenant_id=?':'');
        $q=db()->prepare($sql);$q->execute($tid>0?[$userId,$tid]:[$userId]);$out=[];
        $roles=[
          'health_admin'=>['health.dashboard','health.doctors.manage','health.organizations.manage','health.specialties.manage','health.appointments.manage','health.consultations.manage','health.patients.manage','health.subscriptions.manage','health.staff.manage','health.analytics','health.settings'],
          'doctor'=>['health.dashboard','health.appointments.manage','health.consultations.manage','health.patients.manage'],
          'hospital_manager'=>['health.dashboard','health.doctors.manage','health.organizations.manage','health.appointments.manage','health.consultations.manage','health.patients.manage','health.analytics'],
          'clinic_manager'=>['health.dashboard','health.doctors.manage','health.organizations.manage','health.appointments.manage','health.consultations.manage','health.patients.manage','health.analytics'],
          'receptionist'=>['health.dashboard','health.appointments.manage','health.patients.manage'],
          'subscription_manager'=>['health.dashboard','health.subscriptions.manage','health.analytics'],
          'analyst'=>['health.dashboard','health.analytics']
        ];
        foreach($q->fetchAll()?:[] as $r){$p=json_decode((string)($r['permissions_json']??''),true);if(!is_array($p)||!$p)$p=$roles[(string)($r['access_role']??'analyst')]??$roles['analyst'];$out=array_merge($out,$p);}
        return array_values(array_unique(array_intersect(array_keys(permission_registry()),$out)));
    }catch(Throwable $e){return [];}
}
function healthnet_staff_permissions_for_user_v590(int $userId): array {
    if($userId<1)return [];try{
        $tid=function_exists('tenant_id')?tenant_id():0;
        $sql='SELECT access_role,permissions_json FROM health_network_staff_v590 WHERE user_id=? AND status=1'.($tid>0?' AND tenant_id=?':'');
        $q=db()->prepare($sql);$q->execute($tid>0?[$userId,$tid]:[$userId]);$out=[];
        $roles=[
          'network_admin'=>['healthnet.dashboard','healthnet.pharmacies.manage','healthnet.labs.manage','healthnet.ambulance.manage','healthnet.orders.manage','healthnet.bookings.manage','healthnet.reports.manage','healthnet.subscriptions.manage','healthnet.staff.manage','healthnet.analytics','healthnet.settings'],
          'pharmacy_manager'=>['healthnet.dashboard','healthnet.pharmacies.manage','healthnet.orders.manage','healthnet.analytics'],
          'pharmacist'=>['healthnet.dashboard','healthnet.pharmacies.manage','healthnet.orders.manage'],
          'lab_manager'=>['healthnet.dashboard','healthnet.labs.manage','healthnet.bookings.manage','healthnet.reports.manage','healthnet.analytics'],
          'lab_technician'=>['healthnet.dashboard','healthnet.bookings.manage','healthnet.reports.manage'],
          'sample_collector'=>['healthnet.dashboard','healthnet.bookings.manage'],
          'dispatcher'=>['healthnet.dashboard','healthnet.ambulance.manage'],
          'driver'=>['healthnet.dashboard','healthnet.ambulance.manage'],
          'subscription_manager'=>['healthnet.dashboard','healthnet.subscriptions.manage','healthnet.analytics'],
          'analyst'=>['healthnet.dashboard','healthnet.analytics']
        ];
        foreach($q->fetchAll()?:[] as $r){$p=json_decode((string)($r['permissions_json']??''),true);if(!is_array($p)||!$p)$p=$roles[(string)($r['access_role']??'analyst')]??$roles['analyst'];$out=array_merge($out,$p);}
        return array_values(array_unique(array_intersect(array_keys(permission_registry()),$out)));
    }catch(Throwable $e){return [];}
}
function smartcity_staff_permissions_for_user_v630(int $userId): array {
    if($userId<1)return [];try{
        $tid=function_exists('tenant_id')?tenant_id():0;
        $sql='SELECT access_role,permissions_json FROM smart_city_staff_v630 WHERE user_id=? AND status=1'.($tid>0?' AND tenant_id=?':'');
        $q=db()->prepare($sql);$q->execute($tid>0?[$userId,$tid]:[$userId]);$out=[];
        $roles=[
          'smartcity_admin'=>['smartcity.dashboard','smartcity.municipal.manage','smartcity.wallet.manage','smartcity.loyalty.manage','smartcity.notifications.manage','smartcity.ai.manage','smartcity.staff.manage','smartcity.analytics','smartcity.settings'],
          'municipal_manager'=>['smartcity.dashboard','smartcity.municipal.manage','smartcity.analytics'],
          'municipal_officer'=>['smartcity.dashboard','smartcity.municipal.manage'],
          'wallet_manager'=>['smartcity.dashboard','smartcity.wallet.manage','smartcity.loyalty.manage','smartcity.analytics'],
          'citizen_service'=>['smartcity.dashboard','smartcity.municipal.manage','smartcity.notifications.manage'],
          'ai_manager'=>['smartcity.dashboard','smartcity.ai.manage','smartcity.analytics'],
          'analyst'=>['smartcity.dashboard','smartcity.analytics']
        ];
        foreach($q->fetchAll()?:[] as $r){$p=json_decode((string)($r['permissions_json']??''),true);if(!is_array($p)||!$p)$p=$roles[(string)($r['access_role']??'analyst')]??$roles['analyst'];$out=array_merge($out,$p);}
        return array_values(array_unique(array_intersect(array_keys(permission_registry()),$out)));
    }catch(Throwable $e){return [];}
}

function superapp_staff_permissions_for_user_v700(int $userId): array {
    if($userId<1)return [];try{
        $tid=function_exists('tenant_id')?tenant_id():0;
        $sql='SELECT access_role,permissions_json FROM superapp_staff_v700 WHERE user_id=? AND status=1'.($tid>0?' AND tenant_id=?':'');
        $q=db()->prepare($sql);$q->execute($tid>0?[$userId,$tid]:[$userId]);$out=[];
        $roles=[
          'superapp_admin'=>['superapp.dashboard','education.institutions.manage','education.admissions.manage','education.students.manage','education.finance.manage','transport.operators.manage','transport.dispatch.manage','transport.bookings.manage','tourism.places.manage','tourism.bookings.manage','superapp.staff.manage','superapp.tenant.manage','superapp.analytics'],
          'education_admin'=>['superapp.dashboard','education.institutions.manage','education.admissions.manage','education.students.manage','education.finance.manage','superapp.analytics'],
          'school_manager'=>['superapp.dashboard','education.institutions.manage','education.admissions.manage','education.students.manage','education.finance.manage'],
          'teacher'=>['superapp.dashboard','education.students.manage'],
          'admissions_officer'=>['superapp.dashboard','education.admissions.manage','education.students.manage'],
          'transport_admin'=>['superapp.dashboard','transport.operators.manage','transport.dispatch.manage','transport.bookings.manage','superapp.analytics'],
          'fleet_manager'=>['superapp.dashboard','transport.operators.manage','transport.dispatch.manage'],
          'driver'=>['superapp.dashboard','transport.dispatch.manage'],
          'delivery_manager'=>['superapp.dashboard','transport.dispatch.manage','transport.bookings.manage'],
          'tourism_admin'=>['superapp.dashboard','tourism.places.manage','tourism.bookings.manage','superapp.analytics'],
          'hotel_manager'=>['superapp.dashboard','tourism.places.manage','tourism.bookings.manage'],
          'restaurant_manager'=>['superapp.dashboard','tourism.places.manage','tourism.bookings.manage'],
          'booking_manager'=>['superapp.dashboard','tourism.bookings.manage'],
          'analyst'=>['superapp.dashboard','superapp.analytics']
        ];
        foreach($q->fetchAll()?:[] as $r){$p=json_decode((string)($r['permissions_json']??''),true);if(!is_array($p)||!$p)$p=$roles[(string)($r['access_role']??'analyst')]??$roles['analyst'];$out=array_merge($out,$p);}
        return array_values(array_unique(array_intersect(array_keys(permission_registry()),$out)));
    }catch(Throwable $e){return [];}
}

function permission_list_for_user(?array $u=null): array {
    $u=$u?:current_user();if(!$u||($u['status']??'')!=='active')return [];
    if(function_exists('user_is_approved')&&!user_is_approved((int)$u['id']))return [];
    $blood=bloodbank_staff_permissions_for_user_v570((int)$u['id']);
    $health=health_staff_permissions_for_user_v580((int)$u['id']);
    $healthnet=healthnet_staff_permissions_for_user_v590((int)$u['id']);
    $smartcity=smartcity_staff_permissions_for_user_v630((int)$u['id']);
    $superapp=superapp_staff_permissions_for_user_v700((int)$u['id']);
    if(function_exists('tenant_member_permissions_for_user')){$tp=tenant_member_permissions_for_user((int)$u['id']);if($tp!==null)return array_values(array_unique(array_merge($tp,$blood,$health,$healthnet,$smartcity,$superapp)));}
    $profile=get_user_profile((int)$u['id']);
    if(($u['role']??'')==='admin'&&empty($profile['custom_role_id']))return ['*'];
    if(!empty($profile['custom_role_id'])&&($profile['custom_role_status']??1)){$p=json_decode((string)($profile['permissions_json']??'[]'),true);$p=is_array($p)?$p:[];return array_values(array_unique(array_intersect(array_keys(permission_registry()),array_merge($p,$blood,$health,$healthnet,$smartcity,$superapp))));}
    $core=core_role_registry()[(string)($u['role']??'')]??null;$base=$core?($core['permissions']??[]):[];return array_values(array_unique(array_merge($base,$blood,$health,$healthnet,$smartcity,$superapp)));
}
function permission_aliases(string $permission): array {if(in_array($permission,['city_guide.manage','deals.manage','events.manage','jobs.manage','property.manage'],true))return ['city_content.manage'];if(str_starts_with($permission,'ai.'))return ['ai.manage','smartcity.ai.manage'];return [];}
function has_permission(string $permission,?array $u=null): bool {$p=permission_list_for_user($u);if(in_array('*',$p,true)||in_array($permission,$p,true))return true;foreach(permission_aliases($permission) as $legacy)if(in_array($legacy,$p,true))return true;return false;}
function can_access_admin_panel(?array $u=null): bool {$u=$u?:current_user();return $u&&count(permission_list_for_user($u))>0;}
function staff_landing_url(?array $u=null): string {$u=$u?:current_user();if(!$u)return '/admin/login.php';if(has_permission('dashboard.view',$u))return '/admin/index.php';if(has_permission('blog.dashboard',$u))return '/admin/blog.php';if(has_permission('news.manage',$u))return '/admin/news.php';if(has_permission('live.manage',$u))return '/admin/live.php';if(has_permission('bloodbank.dashboard',$u))return '/admin/blood-bank.php';if(has_permission('health.dashboard',$u))return '/admin/doctor-online.php';if(has_permission('healthnet.dashboard',$u))return '/admin/health-network.php';if(has_permission('enterprise.dashboard',$u)||has_permission('voice.manage',$u)||has_permission('bi.manage',$u)||has_permission('ops.manage',$u))return '/admin/enterprise.php';if(has_permission('ai.dashboard',$u)||has_permission('ai.manage',$u))return '/admin/ai.php';if(has_permission('smartcity.dashboard',$u))return '/admin/smart-city.php';if(has_permission('superapp.dashboard',$u)||has_permission('education.institutions.manage',$u)||has_permission('transport.operators.manage',$u)||has_permission('tourism.places.manage',$u))return '/admin/super-app.php';if(has_permission('shop.manage',$u)||has_permission('shop.orders.manage',$u)||has_permission('shop.auctions.manage',$u))return '/admin/shop.php';if(has_permission('engagement.manage',$u))return '/admin/engagement.php';if(has_permission('operations.dashboard',$u))return '/admin/operations.php';if(has_permission('payouts.manage',$u))return '/admin/payouts.php';if(has_permission('support.manage',$u))return '/admin/support.php';if(has_permission('inventory.manage',$u))return '/admin/inventory.php';foreach(['reviews.manage'=>'/admin/reviews.php','bookings.manage'=>'/admin/bookings.php','leads.manage'=>'/admin/leads.php','analytics.manage'=>'/admin/analytics.php','services.manage'=>'/admin/services.php','classifieds.manage'=>'/admin/classifieds.php'] as $p=>$url)if(has_permission($p,$u))return $url;foreach(['tenants.manage'=>'/admin/tenants.php','city_guide.manage'=>'/admin/city-guide.php','businesses.manage'=>'/admin/businesses.php','payments.manage'=>'/admin/payments.php','monetization.manage'=>'/admin/monetization.php','ad_marketplace.manage'=>'/admin/ad-marketplace.php','campaigns.manage'=>'/admin/campaigns.php','inbox.manage'=>'/admin/inbox.php'] as $p=>$url)if(has_permission($p,$u))return $url;return '/admin/profile.php';}
function require_staff(): array {$u=current_user();if(!$u){header('Location: /admin/login.php',true,302);exit;}if(!can_access_admin_panel($u)){http_response_code(403);exit('This account does not have staff/admin panel access.');}return $u;}
function require_permission(string $permission): array {$u=current_user();if(!$u){header('Location: /admin/login.php',true,302);exit;}if(!has_permission($permission,$u)){http_response_code(403);exit('You do not have permission to access this admin module.');}return $u;}
function approve_user_account(int $id,string $source='manual',?int $by=null,?int $planId=null,string $notes=''): void {ensure_user_profile_row($id);db()->prepare("UPDATE user_profiles SET approval_status='approved',approval_source=?,package_plan_id=?,approved_by=?,approved_at=NOW(),notes=? WHERE user_id=?")->execute([$source,$planId,$by,$notes,$id]);db()->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$id]);}
function reject_user_account(int $id,?int $by=null,string $notes=''): void {ensure_user_profile_row($id);db()->prepare("UPDATE user_profiles SET approval_status='rejected',approval_source='manual',approved_by=?,approved_at=NOW(),notes=? WHERE user_id=?")->execute([$by,$notes,$id]);}
