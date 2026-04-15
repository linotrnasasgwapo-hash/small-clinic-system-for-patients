<?php
// includes/queue_api.php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/db.php';
$db = getDB();
$in = json_decode(file_get_contents('php://input'), true);
$qid    = (int)($in['queue_id'] ?? 0);
$status = $in['status'] ?? '';
if (!in_array($status, ['waiting','serving','done','skipped'])) {
    jsonOut(['success'=>false,'error'=>'Invalid status'],400);
}
$extra = $status==='serving' ? ', called_at=NOW()' : ($status==='done' ? ', served_at=NOW()' : '');
$db->query("UPDATE queue SET status='$status' $extra WHERE queue_id=$qid");
$amap = ['serving'=>'serving','done'=>'done','skipped'=>'no_show'];
if (isset($amap[$status])) {
    $ast = $amap[$status];
    $db->query("UPDATE appointments a JOIN queue q ON a.appt_id=q.appt_id SET a.status='$ast' WHERE q.queue_id=$qid");
}
jsonOut(['success'=>true]);
