<?php
require __DIR__.'/../app/bootstrap.php';require_permission('categories.manage');
$msg=$err=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  csrf_check();$action=$_POST['action']??'save';
  if($action==='delete'){db()->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_POST['id']]);$msg='Category deleted.';}
  else{
   $id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');$slug=trim($_POST['slug']??'');$status=isset($_POST['status'])?1:0;
   if(!$name||!$slug)throw new RuntimeException('Name and slug are required.');
   if($id){db()->prepare("UPDATE categories SET name=?,slug=?,status=? WHERE id=?")->execute([$name,$slug,$status,$id]);$msg='Category updated.';}
   else{db()->prepare("INSERT INTO categories(name,slug,status) VALUES(?,?,?)")->execute([$name,$slug,$status]);$msg='Category added.';}
  }
 }catch(Throwable $e){$err=$e->getMessage();}
}
$edit=null;if(isset($_GET['edit'])){$q=db()->prepare("SELECT * FROM categories WHERE id=?");$q->execute([(int)$_GET['edit']]);$edit=$q->fetch();}
$rows=db()->query("SELECT * FROM categories ORDER BY name")->fetchAll();
require __DIR__.'/../app/layout.php';page_start('Categories',true);?>
<div class="card"><h1>Categories</h1><?php if($msg):?><div class="success"><?=e($msg)?></div><?php endif;?><?php if($err):?><div class="error"><?=e($err)?></div><?php endif;?>
<form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id" value="<?=e($edit['id']??0)?>">
<label>Name</label><input class="input" name="name" value="<?=e($edit['name']??'')?>" required>
<label>Slug</label><input class="input" name="slug" value="<?=e($edit['slug']??'')?>" required>
<label><input type="checkbox" name="status" <?=($edit['status']??1)?'checked':''?>> Active</label><br><br>
<button class="btn"><?=$edit?'Update':'Add'?> Category</button></form></div>
<div class="card"><table><tr><th>Name</th><th>Slug</th><th>Status</th><th>Action</th></tr><?php foreach($rows as $r):?><tr><td><?=e($r['name'])?></td><td><?=e($r['slug'])?></td><td><?=e($r['status']?'Active':'Hidden')?></td><td><a href="?edit=<?=e($r['id'])?>">Edit</a> · <form method="post" style="display:inline" onsubmit="return confirm('Delete category?')"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=e($r['id'])?>"><button style="border:0;background:none;color:#b91c1c;cursor:pointer">Delete</button></form></td></tr><?php endforeach;?></table></div>
<?php require __DIR__.'/../app/end.php';?>
