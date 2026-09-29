<?php
declare(strict_types=1);

function migration_log(string $message): void {
    $root=realpath(__DIR__.'/..');
    $dir=$root.'/storage/logs';
    if(!is_dir($dir)) @mkdir($dir,0755,true);
    @file_put_contents($dir.'/migrations.log','['.date('c').'] '.$message.PHP_EOL,FILE_APPEND|LOCK_EX);
}

function ensure_schema_migrations_table(): void {
    db()->exec("CREATE TABLE IF NOT EXISTS schema_migrations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        version VARCHAR(80) UNIQUE NOT NULL,
        checksum CHAR(64) NOT NULL,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function split_sql_statements(string $sql): array {
    $statements=[];
    $buffer='';
    $len=strlen($sql);
    $single=false;$double=false;$backtick=false;$lineComment=false;$blockComment=false;$escape=false;

    for($i=0;$i<$len;$i++){
        $c=$sql[$i];
        $n=$i+1<$len?$sql[$i+1]:'';

        if($lineComment){
            if($c==="\n"){$lineComment=false;$buffer.=$c;}
            continue;
        }
        if($blockComment){
            if($c==='*' && $n==='/'){$blockComment=false;$i++;}
            continue;
        }

        if(!$single && !$double && !$backtick){
            if($c==='-' && $n==='-' && ($i+2 >= $len || ctype_space($sql[$i+2]))){$lineComment=true;$i++;continue;}
            if($c==='#'){$lineComment=true;continue;}
            if($c==='/' && $n==='*'){$blockComment=true;$i++;continue;}
        }

        if($escape){
            $buffer.=$c;
            $escape=false;
            continue;
        }

        if(($single || $double) && $c==='\\'){
            $buffer.=$c;
            $escape=true;
            continue;
        }

        if(!$double && !$backtick && $c==="'"){$single=!$single;$buffer.=$c;continue;}
        if(!$single && !$backtick && $c==='"'){$double=!$double;$buffer.=$c;continue;}
        if(!$single && !$double && $c==='`'){$backtick=!$backtick;$buffer.=$c;continue;}

        if(!$single && !$double && !$backtick && $c===';'){
            $stmt=trim($buffer);
            if($stmt!=='')$statements[]=$stmt;
            $buffer='';
            continue;
        }

        $buffer.=$c;
    }

    $tail=trim($buffer);
    if($tail!=='')$statements[]=$tail;
    return $statements;
}

function apply_sql_migration(string $file,bool $throw=true): bool {
    ensure_schema_migrations_table();

    $version=basename($file,'.sql');
    $sql=@file_get_contents($file);
    if($sql===false){
        if($throw) throw new RuntimeException('Unable to read migration: '.$file);
        migration_log('Unable to read migration '.$file);
        return false;
    }

    $checksum=hash('sha256',$sql);
    $q=db()->prepare("SELECT checksum FROM schema_migrations WHERE version=? LIMIT 1");
    $q->execute([$version]);
    $existing=$q->fetchColumn();

    if($existing){
        if(!hash_equals((string)$existing,$checksum)){
            $msg="Migration {$version} already applied but checksum differs.";
            if($throw) throw new RuntimeException($msg);
            migration_log($msg);
        }
        return false;
    }

    try{
        foreach(split_sql_statements($sql) as $stmt){
            db()->exec($stmt);
        }
        $q=db()->prepare("INSERT INTO schema_migrations(version,checksum) VALUES(?,?)");
        $q->execute([$version,$checksum]);
        migration_log("Applied migration {$version}");
        return true;
    }catch(Throwable $e){
        migration_log("Migration {$version} failed: ".$e->getMessage());
        if($throw) throw $e;
        return false;
    }
}

function run_packaged_migrations(bool $throw=false): array {
    $root=realpath(__DIR__.'/..');
    $dir=$root.'/database/migrations';
    if(!is_dir($dir)) return [];

    $files=glob($dir.'/*.sql') ?: [];
    natsort($files);

    $applied=[];
    foreach($files as $file){
        if(apply_sql_migration($file,$throw)) $applied[]=basename($file,'.sql');
    }
    return $applied;
}
