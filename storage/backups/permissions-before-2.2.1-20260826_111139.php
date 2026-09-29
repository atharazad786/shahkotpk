<?php
declare(strict_types=1);

function permission_registry(): array {
    return [
        'dashboard.view'=>'Dashboard',
        'widgets.manage'=>'Dashboard Widgets',
        'homepage.manage'=>'Homepage Builder',
        'banners.manage'=>'Banner Manager',
        'ticker.manage'=>'Information Ticker',
        'businesses.manage'=>'Businesses',
        'categories.manage'=>'Categories',
        'cities.manage'=>'Cities',
        'users.manage'=>'Users',
        'roles.manage'=>'Roles & Permissions',
        'subscriptions.manage'=>'Plans & Subscriptions',
        'ads.manage'=>'Advertisements',
        'payments.manage'=>'Payments & Earnings',
        'settings.manage'=>'Settings',
        'updates.manage'=>'System Updates',
    ];
}
function ensure_user_profile_row(int $id): void {
    try{db()->prepare("INSERT IGNORE INTO user_profiles(user_id,approval_status,approval_source,approved_at) VALUES(?,'approved','auto',NOW())")->execute([$id]);}catch(Throwable $e){}
}
function get_user_profile(int $id): array {
    ensure_user_profile_row($id);
    try{$q=db()->prepare("SELECT up.*,sr.name custom_role_name,sr.slug custom_role_slug,sr.permissions_json FROM user_profiles up LEFT JOIN system_roles sr ON sr.id=up.custom_role_id WHERE up.user_id=? LIMIT 1");$q->execute([$id]);return $q->fetch()?:[];}catch(Throwable $e){return [];}
}

function user_approval_status(int $id): string {
    $profile=get_user_profile($id);
    return (string)($profile['approval_status']??'approved');
}

function user_is_approved(int $id): bool {
    return user_approval_status($id)==='approved';
}
function approval_mode_for_role(string $role): string {
    $mode=(string)setting($role==='shopkeeper'?'shopkeeper_approval_mode':'customer_approval_mode',$role==='shopkeeper'?'manual':'auto');
    return in_array($mode,['auto','manual','package_payment'],true)?$mode:'manual';
}
function permission_list_for_user(?array $u=null): array {
    $u=$u?:current_user(); if(!$u||($u['role']??'')!=='admin') return [];
    $profile=get_user_profile((int)$u['id']);
    if(empty($profile['custom_role_id'])) return ['*'];
    $p=json_decode((string)($profile['permissions_json']??'[]'),true);
    return is_array($p)?$p:[];
}
function has_permission(string $permission,?array $u=null): bool {
    $p=permission_list_for_user($u);
    return in_array('*',$p,true)||in_array($permission,$p,true);
}
function require_permission(string $permission): array {
    $u=require_admin();
    if(!has_permission($permission,$u)){http_response_code(403);exit('You do not have permission to access this admin module.');}
    return $u;
}
function approve_user_account(int $id,string $source='manual',?int $by=null,?int $planId=null,string $notes=''): void {
    ensure_user_profile_row($id);
    db()->prepare("UPDATE user_profiles SET approval_status='approved',approval_source=?,package_plan_id=?,approved_by=?,approved_at=NOW(),notes=? WHERE user_id=?")->execute([$source,$planId,$by,$notes,$id]);
    db()->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$id]);
}
function reject_user_account(int $id,?int $by=null,string $notes=''): void {
    ensure_user_profile_row($id);
    db()->prepare("UPDATE user_profiles SET approval_status='rejected',approval_source='manual',approved_by=?,approved_at=NOW(),notes=? WHERE user_id=?")->execute([$by,$notes,$id]);
}
