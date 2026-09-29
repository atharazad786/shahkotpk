<?php
require __DIR__.'/../../app/bootstrap.php';require_once __DIR__.'/../../app/api_v2.php';$u=api_v2_user('catalog');$limit=max(1,min(40,(int)($_GET['limit']??20)));api_v2_ok(['version'=>'4.3.0','data'=>ai_recommendations((int)$u['id'],$limit)]);
