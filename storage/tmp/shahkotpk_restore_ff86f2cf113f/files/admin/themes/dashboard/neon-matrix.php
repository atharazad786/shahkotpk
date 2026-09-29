<?php require __DIR__.'/../../../app/layout.php';page_start('Neon Matrix',true);render_ticker('admin');?>
<section class="matrix-head"><span>// SYSTEM MATRIX ONLINE</span><h2>SHAHKOTPK CONTROL MATRIX</h2><p>Live business, revenue and directory telemetry.</p><div class="matrix-scan"></div></section>
<?php at_metric_cards($metrics,'neon',10);?>
<section class="matrix-grid"><div class="at-panel neon"><?php at_growth_chart($months,'neon');?></div><div class="at-panel neon"><?php at_health($health,'neon');?></div></section>
<section class="matrix-grid"><div class="at-panel neon"><?php at_quick_actions($quickActions,'neon');?></div><div class="at-panel neon"><?php at_activity($activity);?></div></section>
<div class="at-panel neon"><div class="at-panel-head"><div><h3>Matrix Widgets</h3><span>User-configured telemetry blocks</span></div></div><?php at_custom_widgets($customWidgets);?></div>
<?php require __DIR__.'/../../../app/end.php';?>