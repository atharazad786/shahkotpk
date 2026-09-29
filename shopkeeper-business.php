<?php
require __DIR__.'/app/bootstrap.php';
$u=require_login();

if($u['role']!=='shopkeeper'){
    header('Location: /account.php',true,302);
    exit;
}

if(!feature_enabled('business_registration_enabled',true)){
    http_response_code(403);
    exit('Business registration is currently disabled.');
}

require __DIR__.'/app/business_tools.php';

$success=$error=null;
$id=(int)($_GET['id']??$_POST['id']??0);
$edit=null;

if($id>0){
    $q=db()->prepare("SELECT * FROM businesses WHERE id=? AND owner_id=? LIMIT 1");
    $q->execute([$id,$u['id']]);
    $edit=$q->fetch();
    if(!$edit){
        http_response_code(404);
        exit('Business not found.');
    }
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();

        $name=trim((string)($_POST['name']??''));
        $slugInput=trim((string)($_POST['slug']??''));
        $cityId=(int)($_POST['city_id']??0);
        $categoryId=(int)($_POST['category_id']??0);
        $description=trim((string)($_POST['description']??''));
        $phone=trim((string)($_POST['phone']??''));
        $whatsapp=trim((string)($_POST['whatsapp']??''));
        $address=trim((string)($_POST['address']??''));
        $image=trim((string)($_POST['image']??($edit['image']??'')));

        if($name==='' || !$cityId || !$categoryId) throw new RuntimeException('Business name, city and category are required.');

        $uploaded=upload_business_image($_FILES['business_image']??[]);
        if($uploaded) $image=$uploaded;

        $slug=unique_business_slug($slugInput!==''?$slugInput:$name,$id);

        // Shopkeeper edits always go back to Pending for admin review.
        if($id>0){
            $q=db()->prepare("UPDATE businesses SET city_id=?,category_id=?,name=?,slug=?,description=?,phone=?,whatsapp=?,address=?,image=?,verification_status='pending' WHERE id=? AND owner_id=?");
            $q->execute([$cityId,$categoryId,$name,$slug,$description,$phone,$whatsapp,$address,$image,$id,$u['id']]);
            $success='Business updated successfully and sent for admin verification.';
        }else{
            $q=db()->prepare("INSERT INTO businesses(owner_id,city_id,category_id,name,slug,description,phone,whatsapp,address,image,verification_status,is_featured,status) VALUES(?,?,?,?,?,?,?,?,?,?,'pending',0,1)");
            $q->execute([$u['id'],$cityId,$categoryId,$name,$slug,$description,$phone,$whatsapp,$address,$image]);
            $id=(int)db()->lastInsertId();
            $success='Business added successfully and sent for admin verification.';
        }

        $q=db()->prepare("SELECT * FROM businesses WHERE id=? AND owner_id=? LIMIT 1");
        $q->execute([$id,$u['id']]);
        $edit=$q->fetch();

    }catch(Throwable $e){
        $error=$e->getMessage();
    }
}

$cities=db()->query("SELECT id,name FROM cities WHERE status=1 ORDER BY name")->fetchAll();
$categories=db()->query("SELECT id,name FROM categories WHERE status=1 ORDER BY name")->fetchAll();
$siteName=setting('site_name','ShahkotPK');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=$edit?'Edit':'Add'?> Business - <?=e($siteName)?></title>
<link rel="stylesheet" href="/assets/shahkotpk-1.6.0.css?v=160">
<link rel="stylesheet" href="/assets/shahkotpk-userdash-1.7.0.css?v=170">
</head>
<body class="dashboard-page">
<div class="dash-shell">
<div class="dash-topbar">
<a class="dash-back" href="/shopkeeper.php">← Shopkeeper Dashboard</a>
<div class="dash-actions"><a class="dash-chip" href="/">Homepage</a><a class="dash-chip" href="/logout.php">Logout</a></div>
</div>

<section class="dash-hero">
<div class="dash-hero-grid">
<div>
<span class="dash-badge">● Business management</span>
<h1><?=$edit?'Edit Business':'Add New Business'?></h1>
<p>Create a professional listing with complete contact information. New and edited listings are submitted to admin for verification.</p>
</div>
<div class="dash-hero-side">
<div class="dash-mini-stat"><strong>Pending</strong><span>Verification after save</span></div>
<div class="dash-mini-stat"><strong>Local</strong><span>Shahkot directory visibility</span></div>
</div>
</div>
</section>

<div class="dash-card" style="margin-top:18px">
<?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>

<form method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
<input type="hidden" name="id" value="<?=e($edit['id']??0)?>">

<div class="dash-profile-grid">
<div class="dash-field">
<label>Business Name</label>
<input class="dash-input" name="name" value="<?=e($edit['name']??'')?>" placeholder="Your business name" required>
</div>

<div class="dash-field">
<label>Custom Slug (optional)</label>
<input class="dash-input" name="slug" value="<?=e($edit['slug']??'')?>" placeholder="Auto-generated from business name">
</div>

<div class="dash-field">
<label>City</label>
<select class="dash-select" name="city_id" required>
<option value="">Select city</option>
<?php foreach($cities as $city):?><option value="<?=e($city['id'])?>" <?=($edit['city_id']??0)==$city['id']?'selected':''?>><?=e($city['name'])?></option><?php endforeach;?>
</select>
</div>

<div class="dash-field">
<label>Category</label>
<select class="dash-select" name="category_id" required>
<option value="">Select category</option>
<?php foreach($categories as $cat):?><option value="<?=e($cat['id'])?>" <?=($edit['category_id']??0)==$cat['id']?'selected':''?>><?=e($cat['name'])?></option><?php endforeach;?>
</select>
</div>

<div class="dash-field">
<label>Phone</label>
<input class="dash-input" name="phone" value="<?=e($edit['phone']??$u['phone'])?>" placeholder="Business phone">
</div>

<div class="dash-field">
<label>WhatsApp</label>
<input class="dash-input" name="whatsapp" value="<?=e($edit['whatsapp']??'')?>" placeholder="WhatsApp number">
</div>
</div>

<div class="dash-field" style="margin-top:14px">
<label>Business Address</label>
<input class="dash-input" name="address" value="<?=e($edit['address']??'')?>" placeholder="Complete business address">
</div>

<div class="dash-field" style="margin-top:14px">
<label>Description</label>
<textarea class="dash-textarea" name="description" placeholder="Describe your products, services and business"><?=e($edit['description']??'')?></textarea>
</div>

<div class="dash-profile-grid" style="margin-top:14px">
<div class="dash-field">
<label>Existing / External Image URL</label>
<input class="dash-input" name="image" value="<?=e($edit['image']??'')?>" placeholder="/uploads/businesses/...">
</div>
<div class="dash-field">
<label>Upload Business Image</label>
<input class="dash-input" type="file" name="business_image" accept=".jpg,.jpeg,.png,.webp,.gif">
</div>
</div>

<?php if(!empty($edit['image'])):?><div style="margin-top:14px"><img src="<?=e($edit['image'])?>" alt="" style="max-width:260px;border-radius:18px;border:1px solid #e4ece6"></div><?php endif;?>

<div class="dash-btn-line">
<button class="dash-btn primary" type="submit"><?=$edit?'Save Business Changes':'Create Business Listing'?></button>
<a class="dash-btn soft" href="/shopkeeper.php">Back to Dashboard</a>
</div>
</form>
</div>
</div>
</body>
</html>
