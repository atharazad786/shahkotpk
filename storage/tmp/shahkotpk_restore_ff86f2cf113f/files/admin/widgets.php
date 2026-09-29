<?php
require __DIR__.'/../app/bootstrap.php';
require_permission('widgets.manage');
require_once __DIR__.'/../app/dashboard_widgets.php';

$success=$error=null;
$registry=dashboard_metric_registry();
$edit=null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        csrf_check();
        $action=$_POST['action']??'save';
        $id=(int)($_POST['id']??0);

        if($action==='delete'){
            db()->prepare("DELETE FROM dashboard_widgets WHERE id=?")->execute([$id]);
            $success='Dashboard widget deleted.';
        }else{
            $title=trim((string)($_POST['title']??''));
            $type=(string)($_POST['widget_type']??'metric');
            $metric=trim((string)($_POST['metric_source']??''));
            $icon=mb_substr(trim((string)($_POST['icon']??'')),0,30);
            $target=trim((string)($_POST['target_url']??''));
            $note=trim((string)($_POST['note_text']??''));
            $accent=dashboard_widget_accent((string)($_POST['accent']??'blue'));
            $width=(string)($_POST['width']??'small');
            $enabled=isset($_POST['enabled'])?1:0;
            $order=(int)($_POST['sort_order']??10);

            if($title==='') throw new RuntimeException('Widget title is required.');
            if(!in_array($type,['metric','progress','quick_link','note'],true)) throw new RuntimeException('Invalid widget type.');
            if(!in_array($width,['small','medium','large','full'],true)) $width='small';
            if(in_array($type,['metric','progress'],true) && !isset($registry[$metric])) throw new RuntimeException('Select a valid metric source.');
            if($type==='quick_link' && $target==='') throw new RuntimeException('Quick link widget requires a target URL.');

            if($id){
                $q=db()->prepare("UPDATE dashboard_widgets SET title=?,widget_type=?,metric_source=?,icon=?,target_url=?,note_text=?,accent=?,width=?,enabled=?,sort_order=? WHERE id=?");
                $q->execute([$title,$type,$metric?:null,$icon?:null,$target?:null,$note?:null,$accent,$width,$enabled,$order,$id]);
                $success='Dashboard widget updated.';
            }else{
                $key='widget-'.bin2hex(random_bytes(5));
                $q=db()->prepare("INSERT INTO dashboard_widgets(widget_key,title,widget_type,metric_source,icon,target_url,note_text,accent,width,enabled,sort_order) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
                $q->execute([$key,$title,$type,$metric?:null,$icon?:null,$target?:null,$note?:null,$accent,$width,$enabled,$order]);
                $success='Dashboard widget created.';
            }
        }
    }catch(Throwable $e){
        $error=$e->getMessage();
    }
}

if(isset($_GET['edit'])){
    $q=db()->prepare("SELECT * FROM dashboard_widgets WHERE id=? LIMIT 1");
    $q->execute([(int)$_GET['edit']]);
    $edit=$q->fetch()?:null;
}

$widgets=dashboard_widgets(false);
require __DIR__.'/../app/layout.php';
page_start('Dashboard Widgets',true);
?>
<div class="admin-page-hero">
    <h2>Dashboard Widgets</h2>
    <p>Create safe custom metric, progress, quick-link and note widgets. Drag/drop order can also be changed directly on the main dashboard.</p>
</div>

<?php if($success):?><div class="success"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>

<div class="widget-manager-grid">
    <div class="card">
        <h3><?=$edit?'Edit Widget':'Create Widget'?></h3>
        <form method="post">
            <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
            <input type="hidden" name="id" value="<?=e($edit['id']??0)?>">
            <input type="hidden" name="action" value="save">

            <label>Widget Title</label>
            <input class="input" name="title" value="<?=e($edit['title']??'')?>" required>

            <label>Widget Type</label>
            <select class="input" name="widget_type" id="widgetType">
                <?php foreach(['metric'=>'Metric Card','progress'=>'Progress Meter','quick_link'=>'Quick Link','note'=>'Information Note'] as $value=>$label):?>
                    <option value="<?=e($value)?>" <?=($edit['widget_type']??'metric')===$value?'selected':''?>><?=e($label)?></option>
                <?php endforeach;?>
            </select>

            <div id="metricSourceWrap">
                <label>Metric Source</label>
                <select class="input" name="metric_source">
                    <option value="">Select metric</option>
                    <?php foreach($registry as $key=>$meta):?>
                        <option value="<?=e($key)?>" <?=($edit['metric_source']??'')===$key?'selected':''?>><?=e($meta['label'])?></option>
                    <?php endforeach;?>
                </select>
            </div>

            <label>Icon / Short Symbol</label>
            <input class="input" name="icon" maxlength="30" value="<?=e($edit['icon']??'')?>" placeholder="e.g. U, ₨, ★">

            <label>Target URL</label>
            <input class="input" name="target_url" value="<?=e($edit['target_url']??'')?>" placeholder="/admin/users.php">

            <label>Note / Caption</label>
            <textarea class="input" name="note_text" rows="3"><?=e($edit['note_text']??'')?></textarea>

            <div class="admin-form-grid">
                <div>
                    <label>Accent</label>
                    <select class="input" name="accent">
                        <?php foreach(['blue','green','violet','amber','cyan','red','indigo','slate'] as $a):?>
                            <option value="<?=e($a)?>" <?=($edit['accent']??'blue')===$a?'selected':''?>><?=e(ucfirst($a))?></option>
                        <?php endforeach;?>
                    </select>
                </div>
                <div>
                    <label>Width</label>
                    <select class="input" name="width">
                        <?php foreach(['small'=>'1 Column','medium'=>'2 Columns','large'=>'3 Columns','full'=>'Full Width'] as $v=>$label):?>
                            <option value="<?=e($v)?>" <?=($edit['width']??'small')===$v?'selected':''?>><?=e($label)?></option>
                        <?php endforeach;?>
                    </select>
                </div>
                <div>
                    <label>Order</label>
                    <input class="input" type="number" name="sort_order" value="<?=e($edit['sort_order']??10)?>">
                </div>
                <div>
                    <label>Visibility</label>
                    <label style="display:flex;align-items:center;gap:8px;padding-top:9px"><input type="checkbox" name="enabled" <?=($edit['enabled']??1)?'checked':''?>> Show widget</label>
                </div>
            </div>

            <br>
            <button class="btn"><?=$edit?'Save Widget':'Create Widget'?></button>
            <?php if($edit):?><a class="btn ghost" href="/admin/widgets.php">Cancel</a><?php endif;?>
        </form>
    </div>

    <div class="card">
        <div class="panel-title">
            <div><h3>Widget Library</h3><span><?=e(count($widgets))?> configured widgets</span></div>
            <a class="btn ghost" href="/admin/index.php">Open Dashboard</a>
        </div>

        <div class="widget-manager-list">
            <?php foreach($widgets as $w):?>
            <div class="widget-manager-row">
                <div class="drag-handle">⋮⋮</div>
                <div>
                    <strong><?=e($w['title'])?></strong>
                    <div class="muted"><?=e($w['widget_type'])?> · <?=e($w['metric_source']?:'custom')?> · <?=e($w['width'])?></div>
                </div>
                <div style="display:flex;gap:7px;flex-wrap:wrap;justify-content:flex-end">
                    <span class="badge <?=$w['enabled']?'success':'slate'?>"><?=$w['enabled']?'Visible':'Hidden'?></span>
                    <a class="btn ghost" href="?edit=<?=e($w['id'])?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this widget?')">
                        <input type="hidden" name="_csrf" value="<?=e(csrf_token())?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?=e($w['id'])?>">
                        <button class="btn danger">Delete</button>
                    </form>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    </div>
</div>

<script>
(function(){
    const type=document.getElementById('widgetType');
    const wrap=document.getElementById('metricSourceWrap');
    function sync(){wrap.style.display=['metric','progress'].includes(type.value)?'block':'none';}
    type.addEventListener('change',sync);sync();
})();
</script>
<?php require __DIR__.'/../app/end.php';?>
