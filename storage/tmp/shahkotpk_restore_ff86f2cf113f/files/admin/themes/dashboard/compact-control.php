<?php require __DIR__.'/../../../app/layout.php';page_start('Compact Control',true);render_ticker('admin');?>
<section class="compact-head"><div><h2>Compact Operations</h2><span>Dense view · <?=date('d M Y')?> · <?=date('H:i')?> PKT</span></div><div><b><?=e($pendingBusinesses+$pendingPayments)?></b><span>Items need attention</span></div></section>
<?php at_metric_cards($metrics,'compact',10);?>
<section class="compact-main"><div class="at-panel"><?php at_growth_chart($months,'compact');?></div><div class="at-panel"><?php at_health($health,'compact');?></div><div class="at-panel"><?php at_activity($activity);?></div></section>
<div class="at-panel"><?php at_quick_actions($quickActions,'compact');?></div>
<section class="at-two"><?php at_recent_table('Latest Users',['User','Role','Status'],array_map(fn($u)=>[e($u['name']),e($u['role']),e($u['status'])],$recentUsers),'/admin/users.php');?><?php at_recent_table('Latest Businesses',['Business','Category','Status'],array_map(fn($b)=>[e($b['name']),e($b['category_name']?:'—'),e($b['verification_status'])],$recentBusinesses),'/admin/businesses.php');?></section>
<?php require __DIR__.'/../../../app/end.php';?>