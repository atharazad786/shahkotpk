<?php
require __DIR__.'/app/bootstrap.php';
$me=current_user();
if(!$me || !(has_permission('settings.manage',$me)||has_permission('updates.manage',$me)||has_permission('tenants.manage',$me))){http_response_code(403);exit('Forbidden');}
require_once __DIR__.'/app/admin_tools_lock_v975.php';
sk975_admin_tools_gate($me);
require_once __DIR__.'/app/homepage_front_v1010.php';
shp1010_render_homepage(true);
