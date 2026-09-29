<?php
declare(strict_types=1);

function business_slugify(string $value): string {
    $value=trim(mb_strtolower($value));
    $value=preg_replace('/[^a-z0-9]+/u','-',$value);
    $value=trim((string)$value,'-');
    return $value!==''?$value:'business';
}

function unique_business_slug(string $base,int $ignoreId=0): string {
    $base=business_slugify($base);
    $slug=$base;$i=2;

    while(true){
        $sql="SELECT id FROM businesses WHERE slug=?";
        $params=[$slug];
        if($ignoreId>0){$sql.=" AND id<>?";$params[]=$ignoreId;}
        $sql.=" LIMIT 1";

        $q=db()->prepare($sql);
        $q->execute($params);
        if(!$q->fetch()) return $slug;
        $slug=$base.'-'.$i++;
    }
}

function upload_business_image(array $file): ?string {
    if(($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) return null;
    if(!feature_enabled('image_uploads_enabled',true)) throw new RuntimeException('Image uploads are disabled in Admin Settings.');
    if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK) throw new RuntimeException('Business image upload failed.');

    $maxMb=max(1,setting_int('max_image_upload_mb',5));
    if(($file['size']??0)>$maxMb*1024*1024) throw new RuntimeException('Image must be '.$maxMb.'MB or smaller.');

    $allowed=[
        'image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'webp',
        'image/gif'=>'gif',
    ];

    $finfo=new finfo(FILEINFO_MIME_TYPE);
    $mime=$finfo->file($file['tmp_name']);
    if(!isset($allowed[$mime])) throw new RuntimeException('Only JPG, PNG, WEBP or GIF business images are allowed.');

    $dir=__DIR__.'/../uploads/businesses';
    if(!is_dir($dir) && !mkdir($dir,0755,true)) throw new RuntimeException('Could not create business upload directory.');

    $name=date('YmdHis').'-'.bin2hex(random_bytes(5)).'.'.$allowed[$mime];
    if(!move_uploaded_file($file['tmp_name'],$dir.'/'.$name)) throw new RuntimeException('Could not save business image.');

    return '/uploads/businesses/'.$name;
}
