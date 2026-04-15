<?php
// mobile/get_schedules.php
require_once '../includes/auth.php';
requireResident();
require_once '../config/db.php';
$db = getDB();
header('Content-Type: application/json');
$sid = (int)($_GET['service_id']??0);
$out = [];
$st = $db->prepare("SELECT sc.*,s.service_name FROM schedules sc JOIN services s ON sc.service_id=s.service_id WHERE sc.service_id=? AND sc.sched_date>=CURDATE() AND sc.is_available=1 AND sc.booked_slots<sc.max_slots ORDER BY sc.sched_date,sc.start_time LIMIT 14");
$st->bind_param('i',$sid); $st->execute();
$rows = $st->get_result();
while($r=$rows->fetch_assoc()){
    $out[]=['schedule_id'=>$r['schedule_id'],'date_fmt'=>date('l, F j, Y',strtotime($r['sched_date'])),'time_range'=>date('h:i A',strtotime($r['start_time'])).' – '.date('h:i A',strtotime($r['end_time'])),'slots_left'=>$r['max_slots']-$r['booked_slots'],'service'=>$r['service_name']];
}
echo json_encode($out);
