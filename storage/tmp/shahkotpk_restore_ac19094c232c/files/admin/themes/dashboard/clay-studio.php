<?php require __DIR__.'/../../../app/layout.php';page_start('Clay Studio',true);render_ticker('admin');?>
<section class="clay-welcome"><div><span>SOFT WORKSPACE</span><h2>Manage ShahkotPK comfortably.</h2><p>Rounded tactile cards, soft depth and a calmer workflow.</p></div><div class="clay-bubble">✦</div></section>
<?php at_metric_cards(array_slice($metrics,0,8),'clay',8);?>
<section class="at-two"><div class="at-panel clay"><?php at_quick_actions($quickActions,'clay');?></div><div class="at-panel clay"><?php at_health($health,'clay');?></div></section>
<section class="at-two wide-left"><div class="at-panel clay"><?php at_growth_chart($months,'clay');?></div><div class="at-panel clay"><?php at_activity($activity);?></div></section>
<div class="at-panel clay"><div class="at-panel-head"><div><h3>My Dashboard Widgets</h3><span>Soft customizable cards</span></div></div><?php at_custom_widgets($customWidgets);?></div>
<?php require __DIR__.'/../../../app/end.php';?>