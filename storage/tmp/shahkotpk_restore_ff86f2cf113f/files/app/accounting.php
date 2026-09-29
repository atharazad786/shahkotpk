<?php
declare(strict_types=1);

function accounting_reference(string $prefix='JV'): string {
    return strtoupper($prefix).'-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
}
function invoice_reference(): string {
    return 'INV-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
}
function accounting_accounts(bool $activeOnly=true): array {
    try{return db()->query("SELECT * FROM accounting_accounts".($activeOnly?" WHERE status=1":"")." ORDER BY code,name")->fetchAll();}catch(Throwable $e){return [];}
}
function accounting_account(int $id): ?array {
    try{$q=db()->prepare("SELECT * FROM accounting_accounts WHERE id=? LIMIT 1");$q->execute([$id]);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}
function accounting_account_by_key(string $key): ?array {
    try{$q=db()->prepare("SELECT * FROM accounting_accounts WHERE system_key=? LIMIT 1");$q->execute([$key]);return $q->fetch()?:null;}catch(Throwable $e){return null;}
}
function accounting_account_id(string $key): int {
    $a=accounting_account_by_key($key);
    if(!$a) throw new RuntimeException('Accounting account is missing: '.$key);
    return (int)$a['id'];
}
function accounting_normal_balance(string $type,float $debit,float $credit): float {
    return in_array($type,['asset','expense'],true)?$debit-$credit:$credit-$debit;
}
function accounting_account_balance(int $accountId,?string $from=null,?string $to=null): float {
    $a=accounting_account($accountId);if(!$a)return 0;
    $where=["l.account_id=?","j.status='posted'"];$params=[$accountId];
    if($from){$where[]='j.journal_date>=?';$params[]=$from;}
    if($to){$where[]='j.journal_date<=?';$params[]=$to;}
    $q=db()->prepare("SELECT COALESCE(SUM(l.debit),0) d,COALESCE(SUM(l.credit),0) c FROM accounting_journal_lines l JOIN accounting_journals j ON j.id=l.journal_id WHERE ".implode(' AND ',$where));
    $q->execute($params);$r=$q->fetch()?:['d'=>0,'c'=>0];
    return accounting_normal_balance((string)$a['account_type'],(float)$r['d'],(float)$r['c']);
}
function accounting_trial_balance(?string $from=null,?string $to=null): array {
    $where=["j.status='posted'"];$params=[];
    if($from){$where[]='j.journal_date>=?';$params[]=$from;}
    if($to){$where[]='j.journal_date<=?';$params[]=$to;}
    $sql="SELECT a.*,COALESCE(SUM(l.debit),0) total_debit,COALESCE(SUM(l.credit),0) total_credit FROM accounting_accounts a LEFT JOIN accounting_journal_lines l ON l.account_id=a.id LEFT JOIN accounting_journals j ON j.id=l.journal_id AND ".implode(' AND ',$where)." WHERE a.status=1 GROUP BY a.id ORDER BY a.code";
    $q=db()->prepare($sql);$q->execute($params);$rows=$q->fetchAll();
    foreach($rows as &$r)$r['balance']=accounting_normal_balance((string)$r['account_type'],(float)$r['total_debit'],(float)$r['total_credit']);
    return $rows;
}
function accounting_existing_source(string $sourceType,?int $sourceId): ?array {
    if(!$sourceId)return null;
    $q=db()->prepare("SELECT * FROM accounting_journals WHERE source_type=? AND source_id=? AND status='posted' ORDER BY id DESC LIMIT 1");$q->execute([$sourceType,$sourceId]);return $q->fetch()?:null;
}
function accounting_post_journal(string $description,array $lines,string $sourceType='manual',?int $sourceId=null,?int $createdBy=null,?string $date=null,?string $reference=null): int {
    if(!$lines)throw new RuntimeException('Journal entry requires debit and credit lines.');
    if($existing=accounting_existing_source($sourceType,$sourceId))return (int)$existing['id'];
    $totalDebit=0.0;$totalCredit=0.0;
    foreach($lines as $line){
        $d=max(0,(float)($line['debit']??0));$c=max(0,(float)($line['credit']??0));
        if($d>0&&$c>0)throw new RuntimeException('A journal line cannot contain both debit and credit.');
        $totalDebit+=$d;$totalCredit+=$c;
    }
    if(round($totalDebit,2)<=0 || abs(round($totalDebit-$totalCredit,2))>0.009)throw new RuntimeException('Journal is not balanced. Total debit must equal total credit.');
    $reference=$reference?:accounting_reference($sourceType==='manual'?'JV':'SYS');
    $date=$date?:date('Y-m-d');
    db()->prepare("INSERT INTO accounting_journals(reference,journal_date,source_type,source_id,description,status,created_by) VALUES(?,?,?,?,?,'posted',?)")->execute([$reference,$date,$sourceType,$sourceId,$description,$createdBy]);
    $journalId=(int)db()->lastInsertId();$lineNo=1;
    $q=db()->prepare("INSERT INTO accounting_journal_lines(journal_id,account_id,line_no,debit,credit,memo,user_id,business_id,payment_order_id,subscription_id) VALUES(?,?,?,?,?,?,?,?,?,?)");
    foreach($lines as $line){
        $q->execute([$journalId,(int)$line['account_id'],$lineNo++,max(0,(float)($line['debit']??0)),max(0,(float)($line['credit']??0)),trim((string)($line['memo']??''))?:null,($line['user_id']??null)?:null,($line['business_id']??null)?:null,($line['payment_order_id']??null)?:null,($line['subscription_id']??null)?:null]);
    }
    return $journalId;
}
function accounting_gateway_account_key(string $gateway): string {
    $g=strtolower(trim($gateway));
    switch($g){
        case 'cash':
        case 'cash_on_hand': return 'cash_on_hand';
        case 'jazzcash': return 'jazzcash';
        case 'easypaisa': return 'easypaisa';
        case 'payfast': return 'payfast';
        case 'raast':
        case 'bank':
        case 'bank_transfer': return 'bank';
        default: return 'bank';
    }
}
function accounting_revenue_key(string $purpose): string {
    switch($purpose){
        case 'subscription':
        case 'user_package': return 'subscription_revenue';
        case 'advertisement': return 'advertisement_revenue';
        case 'ecommerce': return 'store_revenue';
        default: return 'other_revenue';
    }
}
function accounting_sync_invoice_statuses(): void {
    try{db()->exec("UPDATE subscription_invoices SET status='overdue' WHERE status IN ('unpaid','partial') AND due_date<CURDATE()");}catch(Throwable $e){}
}
function subscription_invoice(int $id): ?array {
    accounting_sync_invoice_statuses();
    $q=db()->prepare("SELECT i.*,b.name business_name,sp.name plan_name,po.reference payment_reference FROM subscription_invoices i JOIN businesses b ON b.id=i.business_id JOIN subscription_plans sp ON sp.id=i.plan_id LEFT JOIN payment_orders po ON po.id=i.payment_order_id WHERE i.id=? LIMIT 1");$q->execute([$id]);return $q->fetch()?:null;
}
function subscription_invoice_by_order(int $orderId): ?array {
    accounting_sync_invoice_statuses();
    $q=db()->prepare("SELECT * FROM subscription_invoices WHERE payment_order_id=? LIMIT 1");$q->execute([$orderId]);return $q->fetch()?:null;
}
function subscription_invoices(string $status='',?int $businessId=null,int $limit=300): array {
    accounting_sync_invoice_statuses();$where=[];$params=[];
    if($status!==''){$where[]='i.status=?';$params[]=$status;}
    if($businessId){$where[]='i.business_id=?';$params[]=$businessId;}
    $sql="SELECT i.*,b.name business_name,sp.name plan_name,po.reference payment_reference FROM subscription_invoices i JOIN businesses b ON b.id=i.business_id JOIN subscription_plans sp ON sp.id=i.plan_id LEFT JOIN payment_orders po ON po.id=i.payment_order_id";
    if($where)$sql.=' WHERE '.implode(' AND ',$where);$sql.=" ORDER BY i.id DESC LIMIT ".max(1,min(1000,$limit));
    $q=db()->prepare($sql);$q->execute($params);return $q->fetchAll();
}
function accounting_create_subscription_invoice(?int $subscriptionId,int $businessId,int $planId,float $amount,?int $paymentOrderId=null,?string $dueDate=null,string $notes='',?int $createdBy=null,float $discount=0,float $tax=0): array {
    $amount=max(0,round($amount,2));$discount=max(0,round($discount,2));$tax=max(0,round($tax,2));$total=max(0,round($amount-$discount+$tax,2));
    $dueDate=$dueDate?:date('Y-m-d',strtotime('+'.max(1,setting_int('subscription_invoice_due_days',7)).' days'));
    $invoiceNo=invoice_reference();$status=$total>0?'unpaid':'waived';
    db()->prepare("INSERT INTO subscription_invoices(invoice_no,subscription_id,business_id,plan_id,payment_order_id,issue_date,due_date,amount,discount,tax,total,paid_amount,status,notes,created_by) VALUES(?,?,?,?,?,CURDATE(),?,?,?,?,0,?,?,?)")->execute([$invoiceNo,$subscriptionId,$businessId,$planId,$paymentOrderId,$dueDate,$amount,$discount,$tax,$total,$status,$notes?:null,$createdBy]);
    $id=(int)db()->lastInsertId();
    if($total>0){
        accounting_post_journal(
            'Subscription invoice '.$invoiceNo,
            [
                ['account_id'=>accounting_account_id('accounts_receivable'),'debit'=>$total,'business_id'=>$businessId,'subscription_id'=>$subscriptionId,'payment_order_id'=>$paymentOrderId,'memo'=>'Invoice receivable'],
                ['account_id'=>accounting_account_id('subscription_revenue'),'credit'=>$total,'business_id'=>$businessId,'subscription_id'=>$subscriptionId,'payment_order_id'=>$paymentOrderId,'memo'=>'Subscription revenue']
            ],
            'subscription_invoice',$id,$createdBy
        );
    }
    return subscription_invoice($id)?:[];
}
function accounting_handle_paid_order(array $order,string $gateway,?int $approvedBy=null): void {
    $amount=max(0,round((float)$order['amount'],2));if($amount<=0)return;
    $invoice=subscription_invoice_by_order((int)$order['id']);
    $debitAccount=accounting_account_id(accounting_gateway_account_key($gateway));
    if($invoice){
        $remaining=max(0,(float)$invoice['total']-(float)$invoice['paid_amount']);$received=min($amount,$remaining>0?$remaining:$amount);
        db()->prepare("UPDATE subscription_invoices SET paid_amount=LEAST(total,paid_amount+?),status=IF(paid_amount+?>=total,'paid','partial'),paid_at=IF(paid_amount+?>=total,NOW(),paid_at) WHERE id=?")->execute([$received,$received,$received,$invoice['id']]);
        if(!empty($invoice['subscription_id']))db()->prepare("UPDATE subscriptions SET status='active' WHERE id=? AND status IN ('pending','suspended')")->execute([$invoice['subscription_id']]);
        accounting_post_journal(
            'Payment received for '.$invoice['invoice_no'],
            [
                ['account_id'=>$debitAccount,'debit'=>$received,'business_id'=>$invoice['business_id'],'subscription_id'=>$invoice['subscription_id'],'payment_order_id'=>$order['id'],'user_id'=>$order['user_id'],'memo'=>'Payment received via '.$gateway],
                ['account_id'=>accounting_account_id('accounts_receivable'),'credit'=>$received,'business_id'=>$invoice['business_id'],'subscription_id'=>$invoice['subscription_id'],'payment_order_id'=>$order['id'],'user_id'=>$order['user_id'],'memo'=>'Receivable settled']
            ],
            'payment',(int)$order['id'],$approvedBy
        );
        return;
    }
    $revenue=accounting_account_id(accounting_revenue_key((string)$order['purpose']));
    accounting_post_journal(
        'Payment '.$order['reference'].' — '.ucwords(str_replace('_',' ',(string)$order['purpose'])),
        [
            ['account_id'=>$debitAccount,'debit'=>$amount,'business_id'=>$order['business_id'],'payment_order_id'=>$order['id'],'user_id'=>$order['user_id'],'memo'=>'Collection via '.$gateway],
            ['account_id'=>$revenue,'credit'=>$amount,'business_id'=>$order['business_id'],'payment_order_id'=>$order['id'],'user_id'=>$order['user_id'],'memo'=>'Recognized revenue']
        ],
        'payment',(int)$order['id'],$approvedBy
    );
}
function accounting_mark_invoice_waived(int $invoiceId,?int $by=null): void {
    $invoice=subscription_invoice($invoiceId);if(!$invoice)throw new RuntimeException('Invoice not found.');
    if((float)$invoice['paid_amount']>0)throw new RuntimeException('A partially/fully paid invoice cannot be waived.');
    if(in_array($invoice['status'],['void','waived'],true))return;
    $total=(float)$invoice['total'];
    if($total>0){
        accounting_post_journal(
            'Waive subscription invoice '.$invoice['invoice_no'],
            [
                ['account_id'=>accounting_account_id('subscription_revenue'),'debit'=>$total,'business_id'=>$invoice['business_id'],'subscription_id'=>$invoice['subscription_id'],'memo'=>'Reverse waived revenue'],
                ['account_id'=>accounting_account_id('accounts_receivable'),'credit'=>$total,'business_id'=>$invoice['business_id'],'subscription_id'=>$invoice['subscription_id'],'memo'=>'Remove receivable']
            ],
            'invoice_waive',$invoiceId,$by
        );
    }
    db()->prepare("UPDATE subscription_invoices SET status='waived' WHERE id=?")->execute([$invoiceId]);
    if($invoice['subscription_id'])db()->prepare("UPDATE subscriptions SET status='active' WHERE id=?")->execute([$invoice['subscription_id']]);
}
function accounting_void_invoice(int $invoiceId,?int $by=null): void {
    $invoice=subscription_invoice($invoiceId);if(!$invoice)throw new RuntimeException('Invoice not found.');
    if((float)$invoice['paid_amount']>0)throw new RuntimeException('Paid or partially paid invoice cannot be voided. Reverse/refund the payment first.');
    if($invoice['status']==='void')return;
    $total=(float)$invoice['total'];
    if($total>0){
        accounting_post_journal(
            'Void subscription invoice '.$invoice['invoice_no'],
            [
                ['account_id'=>accounting_account_id('subscription_revenue'),'debit'=>$total,'business_id'=>$invoice['business_id'],'subscription_id'=>$invoice['subscription_id'],'memo'=>'Reverse invoice revenue'],
                ['account_id'=>accounting_account_id('accounts_receivable'),'credit'=>$total,'business_id'=>$invoice['business_id'],'subscription_id'=>$invoice['subscription_id'],'memo'=>'Reverse receivable']
            ],
            'invoice_void',$invoiceId,$by
        );
    }
    db()->prepare("UPDATE subscription_invoices SET status='void' WHERE id=?")->execute([$invoiceId]);
}
function accounting_business_statement(int $businessId,int $limit=500): array {
    $q=db()->prepare("SELECT j.journal_date,j.reference,j.description,a.code,a.name account_name,a.account_type,l.debit,l.credit,l.memo FROM accounting_journal_lines l JOIN accounting_journals j ON j.id=l.journal_id JOIN accounting_accounts a ON a.id=l.account_id WHERE l.business_id=? AND j.status='posted' ORDER BY j.journal_date,j.id,l.line_no LIMIT ".max(1,min(2000,$limit)));
    $q->execute([$businessId]);return $q->fetchAll();
}
function accounting_ledger_lines(int $accountId,int $limit=1000): array {
    $q=db()->prepare("SELECT j.id journal_id,j.journal_date,j.reference,j.description,j.source_type,l.debit,l.credit,l.memo,l.business_id,b.name business_name FROM accounting_journal_lines l JOIN accounting_journals j ON j.id=l.journal_id LEFT JOIN businesses b ON b.id=l.business_id WHERE l.account_id=? AND j.status='posted' ORDER BY j.journal_date,j.id,l.line_no LIMIT ".max(1,min(5000,$limit)));
    $q->execute([$accountId]);return $q->fetchAll();
}
