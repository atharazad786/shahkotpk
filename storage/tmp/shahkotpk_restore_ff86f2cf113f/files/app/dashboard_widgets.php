<?php
declare(strict_types=1);

function dashboard_metric_registry(): array {
    return [
        'total_users' => ['label'=>'Total Users','format'=>'number'],
        'shopkeepers' => ['label'=>'Shopkeepers','format'=>'number'],
        'customers' => ['label'=>'Customers','format'=>'number'],
        'total_businesses' => ['label'=>'Total Businesses','format'=>'number'],
        'verified_businesses' => ['label'=>'Verified Businesses','format'=>'number'],
        'pending_businesses' => ['label'=>'Pending Businesses','format'=>'number'],
        'featured_businesses' => ['label'=>'Featured Businesses','format'=>'number'],
        'categories' => ['label'=>'Categories','format'=>'number'],
        'cities' => ['label'=>'Cities','format'=>'number'],
        'active_subscriptions' => ['label'=>'Active Subscriptions','format'=>'number'],
        'subscription_value' => ['label'=>'Subscription Value','format'=>'money'],
        'total_ads' => ['label'=>'Advertisements','format'=>'number'],
        'active_ads' => ['label'=>'Active Ads','format'=>'number'],
        'ad_budget' => ['label'=>'Ad Budget','format'=>'money'],
        'recorded_revenue' => ['label'=>'Recorded Revenue','format'=>'money'],
        'payment_review' => ['label'=>'Payment Review','format'=>'number'],
        'updates_installed' => ['label'=>'Installed Updates','format'=>'number'],
    ];
}

function dashboard_metric_value(string $source): float {
    $queries=[
        'total_users'=>"SELECT COUNT(*) FROM users",
        'shopkeepers'=>"SELECT COUNT(*) FROM users WHERE role='shopkeeper'",
        'customers'=>"SELECT COUNT(*) FROM users WHERE role IN ('customer','user')",
        'total_businesses'=>"SELECT COUNT(*) FROM businesses",
        'verified_businesses'=>"SELECT COUNT(*) FROM businesses WHERE verification_status='verified'",
        'pending_businesses'=>"SELECT COUNT(*) FROM businesses WHERE verification_status='pending'",
        'featured_businesses'=>"SELECT COUNT(*) FROM businesses WHERE is_featured=1 AND status=1",
        'categories'=>"SELECT COUNT(*) FROM categories",
        'cities'=>"SELECT COUNT(*) FROM cities",
        'active_subscriptions'=>"SELECT COUNT(*) FROM subscriptions WHERE status='active'",
        'subscription_value'=>"SELECT COALESCE(SUM(amount),0) FROM subscriptions WHERE status='active'",
        'total_ads'=>"SELECT COUNT(*) FROM advertisements",
        'active_ads'=>"SELECT COUNT(*) FROM advertisements WHERE status='active'",
        'ad_budget'=>"SELECT COALESCE(SUM(budget),0) FROM advertisements WHERE status IN ('active','scheduled','completed')",
        'recorded_revenue'=>"SELECT COALESCE(SUM(amount),0) FROM payment_orders WHERE status='paid'",
        'payment_review'=>"SELECT COUNT(*) FROM payment_transactions WHERE status='review'",
        'updates_installed'=>"SELECT COUNT(*) FROM system_updates WHERE status='success'",
    ];
    if(!isset($queries[$source])) return 0;
    try{return (float)(db()->query($queries[$source])->fetchColumn() ?: 0);}catch(Throwable $e){return 0;}
}

function dashboard_metric_display(string $source,float $value): string {
    $registry=dashboard_metric_registry();
    $format=$registry[$source]['format']??'number';
    return $format==='money' ? 'Rs '.number_format($value,0) : number_format($value,0);
}

function dashboard_widgets(bool $enabledOnly=true): array {
    try{
        $sql="SELECT * FROM dashboard_widgets";
        if($enabledOnly)$sql.=" WHERE enabled=1";
        $sql.=" ORDER BY sort_order,id";
        return db()->query($sql)->fetchAll();
    }catch(Throwable $e){return [];}
}

function dashboard_widget_width_class(string $width): string {
    switch($width){
        case 'medium': return 'widget-span-2';
        case 'large': return 'widget-span-3';
        case 'full': return 'widget-span-full';
        default: return 'widget-span-1';
    }
}

function dashboard_widget_accent(string $accent): string {
    $allowed=['blue','green','violet','amber','cyan','red','indigo','slate'];
    return in_array($accent,$allowed,true)?$accent:'blue';
}
