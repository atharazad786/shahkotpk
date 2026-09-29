<?php
require __DIR__.'/app/bootstrap.php';
$type=preg_replace('/[^a-z-]/','',(string)($_GET['type']??''));$id=(int)($_GET['id']??0);$event=preg_replace('/[^a-z_]/','',(string)($_GET['event']??'click'));$next=(string)($_GET['next']??'/recommendations.php');
$allowed=['business','product','guide','deal','event','job','property'];if(!in_array($type,$allowed,true))$type='business';if(!in_array($event,['click','favorite','contact','order','dismiss'],true))$event='click';if($id>0&&function_exists('ai_track_recommendation_event')){$u=current_user();ai_track_recommendation_event($u?(int)$u['id']:null,$type,$id,$event,0);}
if($next===''||$next[0]!=='/'||str_starts_with($next,'//')||preg_match('/[\r\n]/',$next))$next='/recommendations.php';$parts=parse_url($next);if($parts===false||isset($parts['host'])||isset($parts['scheme']))$next='/recommendations.php';header('Location: '.$next,true,302);exit;
