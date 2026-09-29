<?php
require __DIR__.'/app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: public,max-age=60');
if(!google_maps_enabled()){echo json_encode(['ok'=>false,'markers'=>[],'message'=>'Maps disabled']);exit;}
$requested=array_values(array_filter(array_map('trim',explode(',',(string)($_GET['types']??'')))));$types=$requested?:google_maps_allowed_types();
$markers=google_maps_markers($types,true);$q=trim((string)($_GET['q']??''));if($q!==''){$needle=mb_strtolower($q);$markers=array_values(array_filter($markers,fn($m)=>str_contains(mb_strtolower($m['title'].' '.$m['subtitle'].' '.$m['address'].' '.$m['city']),$needle)));}
echo json_encode(['ok'=>true,'markers'=>$markers,'count'=>count($markers),'center'=>google_maps_default_center()],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
