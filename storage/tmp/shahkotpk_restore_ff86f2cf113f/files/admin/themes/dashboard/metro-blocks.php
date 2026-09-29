<?php require __DIR__.'/../../../app/layout.php';page_start('Metro Blocks',true);render_ticker('admin');?>
<section class="metro-title"><div><span>METRO ANALYTICS</span><h2>Everything important. One glance.</h2></div><a href="/admin/themes.php">Switch Theme ✦</a></section>
<div class="metro-board"><?php foreach($metrics as $i=>$m):?><a class="metro-tile tile-<?=$i%6?>" href="<?=e($m['href'])?>"><i><?=e($m['icon'])?></i><b><?=e($m['value'])?></b><strong><?=e($m['label'])?></strong><span><?=e($m['note'])?></span></a><?php endforeach;?></div>
<section class="metro-panels"><div class="at-panel"><?php at_growth_chart($months,'metro');?></div><div class="at-panel"><?php at_quick_actions($quickActions,'metro');?></div></section>
<div class="at-panel"><div class="at-panel-head"><div><h3>Custom Tiles</h3><span>Personal dashboard widgets</span></div></div><?php at_custom_widgets($customWidgets);?></div>
<?php require __DIR__.'/../../../app/end.php';?>