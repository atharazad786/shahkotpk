<?php
require __DIR__.'/../app/bootstrap.php';
require_permission('settings.manage');

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        csrf_check();

        // Handle Side Image Upload
        if (!empty($_FILES['auth_side_image']['tmp_name'])) {
            $ext = pathinfo($_FILES['auth_side_image']['name'], PATHINFO_EXTENSION);
            $filename = 'auth_side_' . time() . '.' . $ext;
            $dest = __DIR__ . '/../uploads/' . $filename;
            if (!is_dir(__DIR__ . '/../uploads/')) @mkdir(__DIR__ . '/../uploads/', 0777, true);
            if (move_uploaded_file($_FILES['auth_side_image']['tmp_name'], $dest)) {
                save_setting('auth_side_image', '/uploads/' . $filename);
            }
        }

        // Handle Background Image Upload
        if (!empty($_FILES['auth_bg_image']['tmp_name'])) {
            $ext = pathinfo($_FILES['auth_bg_image']['name'], PATHINFO_EXTENSION);
            $filename = 'auth_bg_' . time() . '.' . $ext;
            $dest = __DIR__ . '/../uploads/' . $filename;
            if (move_uploaded_file($_FILES['auth_bg_image']['tmp_name'], $dest)) {
                save_setting('auth_bg_image', '/uploads/' . $filename);
            }
        }

        // Handle Logo Upload
        if (!empty($_FILES['auth_logo']['tmp_name'])) {
            $ext = pathinfo($_FILES['auth_logo']['name'], PATHINFO_EXTENSION);
            $filename = 'auth_logo_' . time() . '.' . $ext;
            $dest = __DIR__ . '/../uploads/' . $filename;
            if (move_uploaded_file($_FILES['auth_logo']['tmp_name'], $dest)) {
                save_setting('logo_url', '/uploads/' . $filename);
            }
        }
        
        $success = "Authentication settings saved successfully.";
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$side_img = setting('auth_side_image', '');
$bg_img = setting('auth_bg_image', '');
$logo = setting('logo_url', '');

$title = "Auth UI Settings";
require __DIR__.'/../app/layout.php';
page_start($title, true);
?>
<div class="admin-page-hero">
    <h2>Authentication UI Settings</h2>
    <p>Manage background images, side art, and logos for login & signup pages.</p>
</div>

<?php if($success): ?>
<div class="success"><?=e($success)?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="error"><?=e($error)?></div>
<?php endif; ?>

<section class="card" style="margin-top:14px; max-width:800px;">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
        
        <div class="admin-form-grid">
            <div class="full">
                <label>Auth Split-Screen Cover Image (Left Side)</label>
                <div style="margin-top:5px; padding:15px; border:1px solid var(--admin-border,#e2e8f0); border-radius:6px; background:var(--admin-bg-alt,#f8fafc); text-align:center;">
                    <?php if($side_img): ?>
                        <div style="margin-bottom:10px;"><img src="<?=e($side_img)?>" alt="Side Image" style="max-height:120px; border-radius:4px;"></div>
                    <?php endif; ?>
                    <input type="file" name="auth_side_image" accept="image/*" class="input">
                    <p style="font-size:12px; color:var(--admin-muted,#64748b); margin-top:5px;">Recommended size: 1000x1200px or vertical orientation.</p>
                </div>
            </div>

            <div class="full" style="margin-top:10px;">
                <label>Auth Page Background Pattern (Optional)</label>
                <div style="margin-top:5px; padding:15px; border:1px solid var(--admin-border,#e2e8f0); border-radius:6px; background:var(--admin-bg-alt,#f8fafc); text-align:center;">
                    <?php if($bg_img): ?>
                        <div style="margin-bottom:10px;"><img src="<?=e($bg_img)?>" alt="Background Image" style="max-height:80px; border-radius:4px;"></div>
                    <?php endif; ?>
                    <input type="file" name="auth_bg_image" accept="image/*" class="input">
                    <p style="font-size:12px; color:var(--admin-muted,#64748b); margin-top:5px;">Will be used as the background behind the central split auth card.</p>
                </div>
            </div>

            <div class="full" style="margin-top:10px;">
                <label>Platform Logo</label>
                <div style="margin-top:5px; padding:15px; border:1px solid var(--admin-border,#e2e8f0); border-radius:6px; background:var(--admin-bg-alt,#f8fafc); text-align:center;">
                    <?php if($logo): ?>
                        <div style="margin-bottom:10px;"><img src="<?=e($logo)?>" alt="Logo" style="max-height:60px;"></div>
                    <?php endif; ?>
                    <input type="file" name="auth_logo" accept="image/*" class="input">
                    <p style="font-size:12px; color:var(--admin-muted,#64748b); margin-top:5px;">Appears at the top of the auth forms. (Leave blank to use default icon).</p>
                </div>
            </div>
        </div>

        <div style="margin-top:20px; text-align:right;">
            <button type="submit" class="btn">Save Auth UI Settings</button>
        </div>
    </form>
</section>
<?php require __DIR__.'/../app/end.php'; ?>
