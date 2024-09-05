<?php

use Illuminate\Support\Facades\DB;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

function mealType(){
    $now_time = date('H:i:s');
    $snacks = DB::connection('oracle')->table('meal_time_all')->where([['meal_type','SNACKS']])->first();
    $lunch = DB::connection('oracle')->table('meal_time_all')->where([['meal_type','LUNCH']])->first();
    $meal_type = 'SNACKS';
    if($now_time>=date('H:i:s',strtotime($snacks->time_start)) && $now_time<=date('H:i:s',strtotime($snacks->time_end))){
        $meal_type = 'SNACKS';
    }elseif($now_time>=date('H:i:s',strtotime($lunch->time_start)) && $now_time<=date('H:i:s',strtotime($lunch->time_end))){
        $meal_type = 'LUNCH';
    }else{
        $meal_type = "NO MEAL TIME";
    }
    return $meal_type;
}
function printer_details($kadex_no){
    $printer = DB::connection('oracle')->table('printer_kadex_information')->where([['kadex_no',$kadex_no]])->select('printer_name','printer_ip')->first();
    return $printer;
}
 function token_serial($meal_type,$meal_date){
    $prev_serial = DB::connection('oracle')->select("select max(token_sl) as token_sl from meal_token_history where meal_type like '%$meal_type%' and meal_date = to_date('$meal_date', 'mm/dd/yyyy')");

    $new_serial = $prev_serial[0]->token_sl==null?1:$prev_serial[0]->token_sl+1;
    return $new_serial;
 }

 function audit_data($attend_date){
    $audit_data = DB::table('auditdata_prnt')->where([['flag',0],['attenddate',$attend_date]])->get();
    return $audit_data;
 }

 function insert_audit_data($audit_data,$flag){
    $audit_data_update = DB::table('auditdata_prnt')->where([['idAttendance',$audit_data->idAttendance],['userid',$audit_data->userid]])->update([
        'flag'=>$flag
    ]);
    $audit_data_insert = DB::connection('oracle')->table('auditdata_prnt')->insert([
        'idattendance'=>$audit_data->idAttendance,
        'serialno'=>$audit_data->serialno,
        'userid'=>$audit_data->userid,
        'verifycode'=>$audit_data->verifycode,
        'checktime'=>$audit_data->checktime,
        'flag'=>$flag,
        'cntrldoorno'=>$audit_data->cntrldoorno,
        'attenddate'=>$audit_data->attenddate,
        'isattend'=>$audit_data->isattend,
        'isvalid'=>$audit_data->isvalid,
        'pstatus'=>$audit_data->pstatus,
    ]);

    

    // if($audit_data_update && $audit_data_insert){
    if($audit_data_insert){
        return true;
    }else{
        return false;
    }
 }

 function check_valid_for_print($audit_data,$meal_type,$token_sl,$meal_date){
    $check =  DB::connection('oracle')->table('meal_information')->where([['proxy_id',sprintf("%010d", $audit_data->userid)],['meal_type',$meal_type]])->whereRaw("meal_date=to_date('$meal_date', 'mm/dd/yyyy')")->first();
    if($check && $check->meal_status=='YES' && $check->token_status=='NO'){
    // if($check && $check->present_status=='ABSENT' && $check->meal_status=='NO' && $check->token_status=='NO'){
        $updateTokenSl =  DB::connection('oracle')->table('meal_token_history')->insert([
            'emp_id'=>$check->emp_id,
            'meal_type'=>$meal_type,
            'meal_date'=>$audit_data->attenddate,
            'token_sl'=>$token_sl,
            'token_time'=>$audit_data->checktime,
            'create_user'=>'SYS',
            'create_date'=>$audit_data->checktime,
            'com_id'=>1,
            'plant_id'=>$check->plant_id,
        ]);
        return $check;
    }else{
        return false;
    }
 }

 function printToken($meal_type,$printer_ip,$token_sl,$emp_id,$emp_name,$punch_time){
    // die();
    // dd('$hello');
    $connector = new NetworkPrintConnector($printer_ip, 9100);
    // dd($connector);
    $printer = new Printer($connector);

    $printer -> initialize();
    
    /* Stuff around with left margin */
    $printer -> setPrintLeftMargin(135);
    $printer -> text(ucfirst(strtolower($meal_type))." Token Number : ".$token_sl."\n");
    $printer -> setPrintLeftMargin(120);
    $printer -> text("Incepta Pharmaceuticals Ltd.\n");
    $printer -> setPrintLeftMargin(115);
    $printer -> text("Date :".date('m/d/Y h:i:s A',strtotime($punch_time))." \n");
    $printer -> setPrintLeftMargin(0);
    $printer -> text("- - - - - - - - - - - - - - - - - - - - - - - -\n");
    // $printer -> text("Token SL : 302 \n");
    $printer -> text("Emp ID   : ".$emp_id." \n");
    $printer -> text("Emp Name : ".$emp_name." \n");
    $printer -> text("- - - - - - - - - - - - - - - - - - - - - - - -\n");
    $printer -> setPrintLeftMargin(135);
    $printer -> text("Developed By Incepta IT \n");
    $printer -> setPrintLeftMargin(0);
    $printer -> cut();
    $printer -> close();
}

function updateMealInfo($emp_id,$meal_type,$meal_date){
    $update = DB::connection('oracle')->table('meal_information')->where([['emp_id',$emp_id],['meal_type',$meal_type]])->whereRaw("meal_date = to_date('$meal_date', 'mm/dd/yyyy')")->update([
        'token_status'=>'YES',
    ]);

    if($update){
        return true;
    }else{
        return false;
    }
}