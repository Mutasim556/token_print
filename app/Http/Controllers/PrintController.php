<?php

namespace App\Http\Controllers;

use App\Console\Commands\TokenPrintCommand;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Mike42\Escpos\ImagickEscposImage;
use Rawilk\Printing\Facades\Printing;
use Rawilk\Printing\Printing as PrintingPrinting;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class PrintController extends Controller
{
    public function index(){
        // DB::setDateFormat('MM/DD/YYYY');
        
        $printer = null;
        $meal_type = mealType();

        foreach(audit_data(date('Y-m-d')) as $audit_data){
           
            if(!insert_audit_data($audit_data,$flag=1)){
                break;
            }else{
                $token_sl = token_serial('SNACKS',date('m-d-Y'));
                $token_sl = sprintf("%04d", $token_sl);
                $validPrint = check_valid_for_print($audit_data,$meal_type,$token_sl,date('m-d-Y'));
                if($validPrint){
                    $printer = printer_details($audit_data->serialno);
                    updateMealInfo($validPrint->emp_id,$meal_type,date('m-d-Y'));
                    printToken($meal_type,$printer->printer_ip,$token_sl,$validPrint->emp_id,$validPrint->sur_name,$audit_data->checktime);
                }
            }
        }
       
    }

    public function configUpdate(Request $data){
        $printData = [];
        $daysName = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Staurday'];
        $printData['start_time'] = $data->start_time;
        $printData['end_time'] = $data->end_time;
        $printData['days'] = $data->days;
        $printData['days_name'] =[];
        foreach($data->days as $key=>$day){
            array_push($printData['days_name'],$daysName[$day]);
        }

        $phpArray = "<?php\n\nreturn " . var_export($printData, true) . ";\n";
        file_put_contents(config_path('print_config.php'), $phpArray);
    }
    

    public function getData(){
        $data = DB::table('auditdata_prnt')->paginate(10);

        $dataO = DB::connection('oracle')->table('meal_information')->first();
        dd($data,$dataO);
    }


    // public function printToken($meal_type,$printer_ip,$token_sl,$emp_id,$emp_name,$punch_time){
    //     // die();
    //     // dd('$hello');
    //     $connector = new NetworkPrintConnector($printer_ip, 9100);
    //     $printer = new Printer($connector);

    //     $printer -> initialize();
        
    //     /* Stuff around with left margin */
    //     $printer -> setPrintLeftMargin(135);
    //     $printer -> text($meal_type." Token Number : ".$token_sl."\n");
    //     $printer -> setPrintLeftMargin(120);
    //     $printer -> text("Incepta Pharmaceuticals Ltd.\n");
    //     $printer -> setPrintLeftMargin(115);
    //     $printer -> text("Date :".date('m/d/Y h:i:s A',strtotime($punch_time))." \n");
    //     $printer -> setPrintLeftMargin(0);
    //     $printer -> text("- - - - - - - - - - - - - - - - - - - - - - - -\n");
    //     // $printer -> text("Token SL : 302 \n");
    //     $printer -> text("Emp ID   : ".$emp_id." \n");
    //     $printer -> text("Emp Name : ".$emp_name." \n");
    //     $printer -> text("- - - - - - - - - - - - - - - - - - - - - - - -\n");
    //     $printer -> setPrintLeftMargin(135);
    //     $printer -> text("Developed By Incepta IT \n");
    //     $printer -> setPrintLeftMargin(0);
    //     $printer -> cut();
    //     $printer -> close();
    // }



    public function testPrint(Request $data){
        $meal_type = mealType();
        $printer_ip = env('PRINTER_IP2');
        $token_sl = 'No';
        $emp_id = $data->emp_id?$data->emp_id:'1022007';
        $emp_name =  $data->emp_id?strtoupper($data->name):'MD. MUTASIM NAIB';
        $punch_time = date('m/d/Y h:i:s A');

        printToken($meal_type,$printer_ip,$token_sl,$emp_id,$emp_name,$punch_time);

        return redirect()->back()->with(['type'=>'success','message'=>'Printed successfully']);
    }


    public function getCurrentMealInfo(){
        $meal_type = mealType();
        $current_token_serial = token_serial($meal_type,date('m-d-Y'))-1;

        return response([
            'meal_type'=>$meal_type,
            'current_token_serial'=>$current_token_serial,
        ]);
    }


    public function startSchedule(Schedule $schedule){
        $artisan = Artisan::call("app:token-print-command");
        // $schedule = $schedule->command(TokenPrintCommand::class)->everyTwoSeconds();
        // dd($schedule);
        // if($artisan){
            return redirect()->back()->with(['type'=>'success','message'=>'Scheduler start successfully']);
        // }
    }

}
