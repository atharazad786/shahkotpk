<?php
declare(strict_types=1);
function active_tickers(string $scope='public'): array {
    $cond=$scope==='admin'?"(scope='admin' OR scope='both')":"(scope='public' OR scope='both')";
    try{return db()->query("SELECT * FROM information_tickers WHERE status=1 AND {$cond} AND (starts_at IS NULL OR starts_at<=NOW()) AND (ends_at IS NULL OR ends_at>=NOW()) ORDER BY sort_order,id DESC LIMIT 20")->fetchAll();}catch(Throwable $e){return [];}
}
function render_ticker(string $scope='public'): void {
    if(!feature_enabled($scope==='admin'?'ticker_admin_enabled':'ticker_public_enabled',true))return;
    $rows=active_tickers($scope); if(!$rows)return;
    $label=$scope==='public'?'UPDATES':'LIVE';
    echo '<div class="info-ticker"><div class="info-ticker-label">'.e($label).'</div><div class="info-ticker-track"><div class="info-ticker-content">';
    foreach($rows as $r){
        echo '<span class="ticker-item"><strong>'.e($r['title']?:'Update').':</strong> ';
        echo $r['link_url']?'<a href="'.e($r['link_url']).'">'.e($r['message']).'</a>':e($r['message']);
        echo '</span><span class="ticker-sep">◆</span>';
    }
    echo '</div></div></div>';
}
