<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require_once __DIR__.'/../app/layout.php';
$u=require_permission('operations.dashboard');
function od_table_exists(string $name): bool {try{$q=db()->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?");$q->execute([$name]);return (int)$q->fetchColumn()>0;}catch(Throwable $e){return false;}}
$tables=['commission_rules','seller_wallets','seller_wallet_transactions','seller_payout_requests','commerce_order_settlements','commerce_invoices','commerce_invoice_items','support_tickets','support_messages','commerce_disputes','commerce_refunds','delivery_zones','shipments','shipment_events','inventory_locations','product_variants','inventory_movements','vendors','purchase_orders','purchase_order_items','payment_gateway_webhook_keys','payment_webhook_logs','payment_reconciliation','audit_logs','job_queue','backup_records','health_checks'];
$rows=[];foreach($tables as $t)$rows[]=['table'=>$t,'ok'=>od_table_exists($t)];
$migration=null;try{$q=db()->prepare('SELECT version,applied_at FROM schema_migrations WHERE version IN (?,?,?) ORDER BY version DESC');$q->execute(['3.6.0','3.6.1','3.6.2']);$migration=$q->fetchAll();}catch(Throwable $e){$migration=[];}
$log=__DIR__.'/../storage/logs/runtime-errors.log';$tail='';if(is_file($log)&&is_readable($log)){$lines=@file($log,FILE_IGNORE_NEW_LINES);if(is_array($lines))$tail=implode("\n",array_slice($lines,-35));}
page_start('Operations Diagnostic',true);
?>
<link rel="stylesheet" href="/assets/operations-3.6.0.css?v=362">
<section class="ops-hero"><div><div>v3.6.2 RUNTIME DIAGNOSTIC</div><h2>Commercial Operations Health</h2><p>Checks the exact shared engine and database tables used by all Operations dashboards.</p></div></section>
<div class="ops-kpis">
<div class="ops-kpi">PHP Runtime<b><?=e(PHP_VERSION)?></b></div>
<div class="ops-kpi">Engine Loaded<b><?=operations_module_loaded()?'YES':'NO'?></b></div>
<div class="ops-kpi">Tables Ready<b><?=e(count(array_filter($rows,function($r){return $r['ok'];})))?> / <?=e(count($rows))?></b></div>
<div class="ops-kpi">SAPI<b><?=e(PHP_SAPI)?></b></div>
</div>
<section class="card"><h3>Operations Database Tables</h3><div class="ops-table"><table><tr><th>Table</th><th>Status</th></tr><?php foreach($rows as $r):?><tr><td><?=e($r['table'])?></td><td><span class="ops-badge <?=$r['ok']?'ok':'danger'?>"><?=$r['ok']?'READY':'MISSING'?></span></td></tr><?php endforeach;?></table></div></section>
<section class="card"><h3>Migration State</h3><pre><?=e(json_encode($migration,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES))?></pre></section>
<section class="card"><h3>Recent Runtime Errors</h3><pre style="white-space:pre-wrap;max-height:420px;overflow:auto;background:#07111d;color:#dbeafe;padding:14px;border-radius:12px"><?=e($tail?:'No readable runtime errors logged.')?></pre></section>
<?php require __DIR__.'/../app/end.php'; ?>
