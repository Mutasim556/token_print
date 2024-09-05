<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class TokenPrintCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:token-print-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $printer = null;
        $meal_type = mealType();
        
        foreach(audit_data(date('Y-m-d')) as $audit_data){
           
            if(!insert_audit_data($audit_data,$flag=1)){
                break;
            }else{
                $token_sl = token_serial($meal_type,date('m-d-Y'));
                $token_sl = sprintf("%04d", $token_sl);
                $validPrint = check_valid_for_print($audit_data,$meal_type,$token_sl,date('m-d-Y'));
                if($validPrint){
                    $printer = printer_details($audit_data->serialno);
                    // if(env('UPDATE_FLAG ')==1){
                        updateMealInfo($validPrint->emp_id,$meal_type,date('m-d-Y'));
                    // }
                    printToken($meal_type,$printer->printer_ip,$token_sl,$validPrint->emp_id,$validPrint->sur_name,$audit_data->checktime);
                }
            }
        }
    }
}
