<?php
require __DIR__.'/app/bootstrap.php';
if(!setting_bool('city_portal_enabled',true)){http_response_code(404);exit('City portal is disabled.');}
$portalType='job';
require __DIR__.'/app/city_portal_page.php';
