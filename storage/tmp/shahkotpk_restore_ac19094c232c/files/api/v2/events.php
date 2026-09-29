<?php
require __DIR__.'/../../app/bootstrap.php';require_once __DIR__.'/../../app/api_v2.php';$u=api_v2_user('profile');if($_SERVER['REQUEST_METHOD']!=='POST')api_v2_error('POST required',405);$in=api_input();$event=trim((string)($in['event']??''));if($event==='')api_v2_error('event required',422);api_v2_event((int)$u['user_id'],$event,$in);api_v2_ok(['recorded'=>true],(int)$u['user_id']);
