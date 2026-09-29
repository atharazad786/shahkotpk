<?php
require __DIR__.'/../app/bootstrap.php';
$admin=require_staff();
$success=$error=null;

ensure_user_profile_row((int)$admin['id']);
$profile=admin_profile_data((int)$admin['id']);

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();
        $action=(string)($_POST['action']??'save');

        if($action==='remove_photo'){
            $old=admin_avatar_url((int)$admin['id']);
            db()->prepare("UPDATE user_profiles SET avatar_url=NULL WHERE user_id=?")->execute([$admin['id']]);
            remove_admin_profile_photo_file($old);
            $success='Profile photo removed.';
        }else{
            $name=trim((string)($_POST['name']??''));
            $email=trim((string)($_POST['email']??''));
            $phone=trim((string)($_POST['phone']??''));
            $newPassword=(string)($_POST['new_password']??'');

            if($name==='') throw new RuntimeException('Name is required.');
            if($email==='' && $phone==='') throw new RuntimeException('Please enter at least email or phone number.');

            $check=db()->prepare("SELECT id FROM users WHERE id<>? AND ((email=? AND ?<>'') OR (phone=? AND ?<>'')) LIMIT 1");
            $check->execute([$admin['id'],$email,$email,$phone,$phone]);
            if($check->fetch()) throw new RuntimeException('This email or phone is already used by another account.');

            $minPassword=max(8,min(64,setting_int('password_min_length',8)));
            if($newPassword!=='' && strlen($newPassword)<$minPassword){
                throw new RuntimeException('New password must be at least '.$minPassword.' characters.');
            }

            if($newPassword!==''){
                db()->prepare("UPDATE users SET name=?,email=?,phone=?,password_hash=? WHERE id=?")
                    ->execute([$name,$email?:null,$phone?:null,password_hash($newPassword,PASSWORD_DEFAULT),$admin['id']]);
            }else{
                db()->prepare("UPDATE users SET name=?,email=?,phone=? WHERE id=?")
                    ->execute([$name,$email?:null,$phone?:null,$admin['id']]);
            }

            if(isset($_FILES['profile_photo']) && ($_FILES['profile_photo']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
                $old=admin_avatar_url((int)$admin['id']);
                $newPhoto=save_admin_profile_photo($_FILES['profile_photo'],(int)$admin['id']);
                if($newPhoto!==''){
                    db()->prepare("UPDATE user_profiles SET avatar_url=? WHERE user_id=?")->execute([$newPhoto,$admin['id']]);
                    if($old!==$newPhoto) remove_admin_profile_photo_file($old);
                }
            }

            $success='Admin profile updated successfully.';
        }

        $admin=current_user();
        $profile=admin_profile_data((int)$admin['id']);
    }catch(Throwable $e){
        $error=$e->getMessage();
    }
}

$avatar=admin_avatar_url((int)$admin['id']);
$admins=0;$totalUsers=0;$totalBusinesses=0;$activeSubscriptions=0;
try{
    $admins=(int)db()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    $totalUsers=(int)db()->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalBusinesses=(int)db()->query("SELECT COUNT(*) FROM businesses")->fetchColumn();
    $activeSubscriptions=(int)db()->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();
}catch(Throwable $e){}

require __DIR__.'/../app/layout.php';
page_start('Staff Profile',true);
?>
<div class="admin-page-hero profile-hero">
    <div class="profile-hero-copy">
        <h2>Staff Profile</h2>
        <p>Manage your staff identity, profile photo, contact information and password.</p>
    </div>
    <div class="profile-hero-avatar">
        <?php if($avatar):?>
            <img src="<?=e($avatar)?>" alt="Admin profile photo">
        <?php else:?>
            <span><?=e(strtoupper(substr($admin['name']??'A',0,1)))?></span>
        <?php endif;?>
    </div>
</div>

<?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>

<div class="profile-card-grid">
    <section class="card">
        <div class="panel-title">
            <div><h3>Edit Staff Profile</h3><span>Changes apply to your current staff account.</span></div>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
            <input type="hidden" name="action" value="save">

            <div class="profile-photo-editor">
                <div class="profile-photo-preview">
                    <?php if($avatar):?>
                        <img src="<?=e($avatar)?>" alt="Current profile photo">
                    <?php else:?>
                        <span><?=e(strtoupper(substr($admin['name']??'A',0,1)))?></span>
                    <?php endif;?>
                </div>
                <div>
                    <label>Staff Profile Photo</label>
                    <input class="input" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                    <div class="muted" style="margin-top:6px">JPG, PNG or WEBP · maximum 4 MB.</div>
                </div>
            </div>

            <div class="profile-grid" style="margin-top:16px">
                <div>
                    <label>Full Name</label>
                    <input class="input" name="name" value="<?=e($admin['name'])?>" required>
                </div>
                <div>
                    <label>Email</label>
                    <input class="input" type="email" name="email" value="<?=e($admin['email'])?>">
                </div>
                <div>
                    <label>Phone</label>
                    <input class="input" name="phone" value="<?=e($admin['phone'])?>">
                </div>
                <div>
                    <label>New Password</label>
                    <input class="input" type="password" name="new_password" placeholder="Leave blank to keep current password">
                </div>
            </div>

            <div class="profile-action-row">
                <button class="btn" type="submit">Save Profile</button>
                <a class="btn ghost" href="/admin/index.php">Back to Dashboard</a>
            </div>
        </form>

        <?php if($avatar):?>
        <form method="post" class="profile-remove-form" onsubmit="return confirm('Remove your current profile photo?')">
            <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
            <input type="hidden" name="action" value="remove_photo">
            <button class="btn danger" type="submit">Remove Profile Photo</button>
        </form>
        <?php endif;?>
    </section>

    <section class="card">
        <div class="panel-title"><div><h3>Account Overview</h3><span>Your staff identity and platform summary.</span></div></div>
        <div class="profile-stat-grid">
            <article class="profile-stat"><b><?=e($admins)?></b><span>Admin Accounts</span></article>
            <article class="profile-stat"><b><?=e($totalUsers)?></b><span>Total Users</span></article>
            <article class="profile-stat"><b><?=e($totalBusinesses)?></b><span>Businesses</span></article>
            <article class="profile-stat"><b><?=e($activeSubscriptions)?></b><span>Active Subscriptions</span></article>
        </div>

        <div class="profile-identity-card">
            <div class="profile-identity-avatar">
                <?php if($avatar):?><img src="<?=e($avatar)?>" alt="Admin"><?php else:?><span><?=e(strtoupper(substr($admin['name']??'A',0,1)))?></span><?php endif;?>
            </div>
            <div>
                <strong><?=e($admin['name'])?></strong>
                <span><?=e($admin['email']?:$admin['phone'])?></span>
                <small><?=e(core_role_registry()[$admin['role']]['label']??ucfirst((string)$admin['role']))?> · <?=e(ucfirst($admin['status']))?></small>
            </div>
        </div>

        <div class="profile-help">
            <strong>Security</strong><br>
            Your password is never displayed in the admin panel. Use a strong password that meets the configured minimum password length. Uploaded profile images are validated and stored in a protected uploads folder.
        </div>
    </section>
</div>
<?php require __DIR__.'/../app/end.php';?>
