<?php
declare(strict_types=1);

function safe_update_path(string $p): bool {
    $p=str_replace('\\','/',$p);
    return $p!=='' && !str_starts_with($p,'/') && !str_contains($p,'../') && !preg_match('/^[A-Za-z]:\//',$p);
}

function installed_app_version(): string {
    global $config;
    $version=$config['version']??'1.0.0';
    try{
        $latest=db()->query("SELECT version FROM system_updates WHERE status='success' ORDER BY id DESC LIMIT 1")->fetchColumn();
        if($latest && version_compare($latest,$version,'>')) $version=$latest;
    }catch(Throwable $e){}
    return $version;
}

function find_update_manifest(ZipArchive $z): array {
    $idx=$z->locateName('update.json',ZipArchive::FL_NOCASE);
    if($idx!==false) return [$idx,''];

    for($i=0;$i<$z->numFiles;$i++){
        $name=str_replace('\\','/',$z->getNameIndex($i));
        if(strtolower(basename($name))==='update.json'){
            return [$i,substr($name,0,-strlen(basename($name)))];
        }
    }
    throw new RuntimeException('update.json is missing.');
}

function install_update(string $zip): array {
    if(!class_exists('ZipArchive')) throw new RuntimeException('PHP ZIP extension is required.');

    $current=installed_app_version();
    $root=realpath(__DIR__.'/..');
    $z=new ZipArchive();

    if($z->open($zip)!==true) throw new RuntimeException('Invalid ZIP file.');
    [$manifestIndex,$wrapper]=find_update_manifest($z);

    $m=json_decode($z->getFromIndex($manifestIndex),true);
    if(!is_array($m)||empty($m['version'])) throw new RuntimeException('Invalid update manifest.');
    if(version_compare($m['version'],$current,'<=')){
        throw new RuntimeException("Update version {$m['version']} must be higher than installed version {$current}.");
    }

    $tmp=sys_get_temp_dir().'/shahkotpk_'.bin2hex(random_bytes(6));
    if(!mkdir($tmp,0700,true)) throw new RuntimeException('Unable to create temporary update directory.');

    $backup=$root.'/storage/backups/'.date('Ymd_His').'_v'.$current.'.zip';
    $installedFiles=false;

    try{
        // Extract safely.
        for($i=0;$i<$z->numFiles;$i++){
            $original=str_replace('\\','/',$z->getNameIndex($i));
            if($i===$manifestIndex) continue;

            $name=$original;
            if($wrapper!==''){
                if(!str_starts_with($name,$wrapper)) continue;
                $name=substr($name,strlen($wrapper));
            }

            if($name==='' || $name==='update.json') continue;
            if(!safe_update_path($name)) throw new RuntimeException('Unsafe path in update ZIP: '.$name);

            // Protect persistent runtime data.
            if($name==='config/config.php' || str_starts_with($name,'storage/') || $name==='install.php') continue;

            $dest=$tmp.'/'.$name;
            if(str_ends_with($name,'/')){
                if(!is_dir($dest)) mkdir($dest,0755,true);
                continue;
            }

            if(!is_dir(dirname($dest))) mkdir(dirname($dest),0755,true);
            $stream=$z->getStream($original);
            if(!$stream) throw new RuntimeException('Unable to read '.$name.' from update ZIP.');
            $fp=fopen($dest,'wb');
            stream_copy_to_stream($stream,$fp);
            fclose($fp);
            fclose($stream);
        }
        $z->close();

        // Backup current application files.
        $bz=new ZipArchive();
        if($bz->open($backup,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true){
            throw new RuntimeException('Could not create pre-update backup.');
        }

        $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
        foreach($it as $f){
            $rel=str_replace('\\','/',substr($f->getPathname(),strlen($root)+1));
            if(
                is_file($f) &&
                !str_starts_with($rel,'storage/') &&
                !str_starts_with($rel,'config/') &&
                !str_starts_with($rel,'uploads/')
            ){
                $bz->addFile($f->getPathname(),$rel);
            }
        }
        $bz->close();

        // Install files.
        $it=new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach($it as $f){
            $rel=str_replace('\\','/',substr($f->getPathname(),strlen($tmp)+1));
            $dest=$root.'/'.$rel;

            if($f->isDir()){
                if(!is_dir($dest)) mkdir($dest,0755,true);
            }else{
                if(!is_dir(dirname($dest))) mkdir(dirname($dest),0755,true);
                if(!copy($f->getPathname(),$dest)) throw new RuntimeException('Failed to install '.$rel);
            }
        }
        $installedFiles=true;

        // AUTOMATIC SQL MIGRATIONS. Future updates only need to include database/migrations/*.sql.
        require_once $root.'/app/migrations.php';
        $migrations=run_packaged_migrations(true);

        $notes=$m['notes']??'Update installed successfully.';
        if($migrations) $notes.=' SQL migrations: '.implode(', ',$migrations);

        db()->prepare("INSERT INTO system_updates(version,file_name,status,backup_path,notes) VALUES(?,?,?,?,?)")
            ->execute([$m['version'],basename($zip),'success',$backup,$notes]);

        return ['version'=>$m['version'],'migrations'=>$migrations,'backup'=>$backup];

    }catch(Throwable $e){
        try{
            db()->prepare("INSERT INTO system_updates(version,file_name,status,backup_path,notes) VALUES(?,?,?,?,?)")
                ->execute([$m['version']??'unknown',basename($zip),'failed',is_file($backup)?$backup:null,$e->getMessage()]);
        }catch(Throwable $ignored){}
        throw $e;
    }finally{
        if(is_dir($tmp)){
            try{
                $it=new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($tmp,FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach($it as $f){
                    $path=$f->getPathname();
                    if($f->isDir()){
                        @rmdir($path);
                    }else{
                        @unlink($path);
                    }
                }
                @rmdir($tmp);
            }catch(Throwable $cleanupError){
                // Cleanup errors must never make a successful update appear failed.
                try{
                    $logDir=$root.'/storage/logs';
                    if(!is_dir($logDir)) @mkdir($logDir,0755,true);
                    @file_put_contents(
                        $logDir.'/updater.log',
                        '['.date('c').'] Temporary cleanup warning: '.$cleanupError->getMessage().PHP_EOL,
                        FILE_APPEND|LOCK_EX
                    );
                }catch(Throwable $ignored){}
            }
        }
    }
}
