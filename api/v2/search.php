<?php
require __DIR__.'/../../app/bootstrap.php';require_once __DIR__.'/../../app/api_v2.php';$u=api_v2_user('catalog');$q=trim((string)($_GET['q']??''));api_v2_ok(['query'=>$q,'data'=>$q===''?['businesses'=>[],'products'=>[],'city_content'=>[]]:ai_smart_search($q,40)]);
