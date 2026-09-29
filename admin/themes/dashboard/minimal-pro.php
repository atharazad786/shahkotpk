<?php require __DIR__.'/../../../app/layout.php';page_start('Minimal Pro',true);render_ticker('admin');?>
<section class="minimal-head"><div><small>SHAHKOTPK ADMINISTRATION</small><h2>Good <?=date('H')<12?'morning':(date('H')<18?'afternoon':'evening')?>.</h2><p>Here is your platform at a glance.</p></div><a class="btn" href="/">View website ↗</a></section>
<?php at_metric_cards(array_slice($metrics,0,6),'minimal',6);?>
<section class="minimal-grid"><div class="at-panel"><div class="at-panel-head"><div><h3>Growth</h3><span>Simple 6-month trend</span></div></div><?php at_growth_chart($months,'minimal');?></div><div class="at-panel"><div class="at-panel-head"><div><h3>Activity</h3><span>Recent changes</span></div></div><?php at_activity($activity);?></div></section>
<div class="at-panel"><div class="at-panel-head"><div><h3>Shortcuts</h3><span>Frequently used tools</span></div></div><?php at_quick_actions($quickActions,'minimal');?></div>
<div class="at-panel"><div class="at-panel-head"><div><h3>Your Widgets</h3><span>Configurable dashboard blocks</span></div></div><?php at_custom_widgets($customWidgets);?></div>
<?php require __DIR__.'/../../../app/end.php';?>