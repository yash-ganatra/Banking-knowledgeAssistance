<?php

namespace App\Console\Commands;
use DB;
use Illuminate\Console\Command;
use App\Http\Controllers\NPC\ReviewController;
use App\Http\Controllers\NPC\PrivilegeAccessController;


class checkTdAccntCreated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkTdAccntCreated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
    	$debug = false;
        $npcController = new PrivilegeAccessController(); 
        $ReviewController = new ReviewController();

        $forms = DB::table('ACCOUNT_DETAILS')// ->where('source', 'CC')
                                                ->where('td_entry_done', 'Y')
                                                ->where('td_account_no', '=' , null)
                                                ->get()->toArray();
        // echo "<pre>";print_r(count($forms));exit;

        foreach ($forms as $form => $value) {
           $value = (array) ($value);

           $result = $npcController->checkTdAccountcreatedFunciton($value, $value['id'], 'cron');
           if ($debug) {
           		echo "<pre>".$value['aof_number']." : ";print_r($result);
           }

           $ReviewController->markFormForQCInternal($value['id']);

        }
    }

}
