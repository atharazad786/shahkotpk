<?php
require __DIR__.'/../app/bootstrap.php';
require_permission('widgets.manage');

header('Content-Type: application/json; charset=utf-8');

try{
    if($_SERVER['REQUEST_METHOD']!=='POST') throw new RuntimeException('POST required.');
    csrf_check();

    $ids=$_POST['ids']??[];
    if(!is_array($ids)) throw new RuntimeException('Invalid widget order.');

    $clean=[];
    foreach($ids as $id){
        $id=(int)$id;
        if($id>0 && !in_array($id,$clean,true)) $clean[]=$id;
    }

    db()->beginTransaction();
    $q=db()->prepare("UPDATE dashboard_widgets SET sort_order=? WHERE id=?");
    foreach($clean as $i=>$id){
        $q->execute([($i+1)*10,$id]);
    }
    db()->commit();

    echo json_encode(['ok'=>true,'message'=>'Widget order saved.']);
}catch(Throwable $e){
    if(db()->inTransaction()) db()->rollBack();
    http_response_code(400);
    echo json_encode(['ok'=>false,'message'=>$e->getMessage()]);
}
