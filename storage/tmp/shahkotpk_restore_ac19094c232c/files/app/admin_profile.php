<?php
declare(strict_types=1);

function admin_profile_data(int $userId): array {
    if(function_exists('get_user_profile')){
        $profile=get_user_profile($userId);
        return is_array($profile)?$profile:[];
    }
    return [];
}

function admin_avatar_url(int $userId): string {
    $profile=admin_profile_data($userId);
    $url=trim((string)($profile['avatar_url']??''));
    if($url!=='' && str_starts_with($url,'/uploads/admin-profiles/')) return $url;
    return '';
}

function save_admin_profile_photo(array $file,int $userId): string {
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) return '';
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK) throw new RuntimeException('Profile photo upload failed.');
    if(($file['size']??0)>4*1024*1024) throw new RuntimeException('Profile photo must be 4 MB or smaller.');
    if(empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Invalid uploaded profile photo.');

    $allowed=[
        'image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'webp',
    ];
    $finfo=new finfo(FILEINFO_MIME_TYPE);
    $mime=(string)$finfo->file($file['tmp_name']);
    if(!isset($allowed[$mime])) throw new RuntimeException('Use JPG, PNG or WEBP for the profile photo.');

    $imageInfo=@getimagesize($file['tmp_name']);
    if(!$imageInfo || empty($imageInfo[0]) || empty($imageInfo[1])) throw new RuntimeException('Uploaded file is not a valid image.');
    if($imageInfo[0]>5000 || $imageInfo[1]>5000) throw new RuntimeException('Profile photo dimensions are too large.');

    $dir=__DIR__.'/../uploads/admin-profiles';
    if(!is_dir($dir) && !mkdir($dir,0755,true)) throw new RuntimeException('Unable to create admin profile photo folder.');

    $htaccess=$dir.'/.htaccess';
    if(!is_file($htaccess)){
        @file_put_contents($htaccess,"Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar|cgi|pl|py|sh)$\">\nRequire all denied\n</FilesMatch>\n",LOCK_EX);
    }

    $name='admin-'.$userId.'-'.date('YmdHis').'-'.bin2hex(random_bytes(5)).'.'.$allowed[$mime];
    $path=$dir.'/'.$name;
    if(!move_uploaded_file($file['tmp_name'],$path)) throw new RuntimeException('Unable to save profile photo.');
    @chmod($path,0644);

    return '/uploads/admin-profiles/'.$name;
}

function remove_admin_profile_photo_file(string $url): void {
    $url=trim($url);
    if($url==='' || !str_starts_with($url,'/uploads/admin-profiles/')) return;
    $name=basename($url);
    if($name==='' || $name==='.' || $name==='..') return;
    $path=__DIR__.'/../uploads/admin-profiles/'.$name;
    if(is_file($path)) @unlink($path);
}
