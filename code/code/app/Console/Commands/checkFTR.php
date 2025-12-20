<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\NPC\ReviewController;
use App\Helpers\CommonFunctions;
use DB;

class checkFTR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkFTR';

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
        $checkFTRPending = DB::table('FINCON')->where('FUNDING_STATUS', 'Y')
                                            ->whereIn('FUNDING_TYPE',['1','2','5'])
                                            ->where('FTR_STATUS','N')
                                            ->where('ABORT', null)
                                            ->get()->toArray();
        // echo "<pre>";print_r($checkFTRPending);exit;

        $ReviewController = new ReviewController(); 
        for($f=0; $f < count($checkFTRPending); $f++){
            $ReviewController->checkFundTransfer($checkFTRPending[$f]->form_id, 'cron');
            // echo "<pre>";print($checkFTRPending[$f]->form_id);exit;
            $ReviewController->markFormForQCInternal($checkFTRPending[$f]->form_id);
        }
    }
}
