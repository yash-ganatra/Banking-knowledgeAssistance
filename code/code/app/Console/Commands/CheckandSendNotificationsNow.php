<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\NotificationController;

class CheckandSendNotificationsNow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CheckandSendNotificationsNow';

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

         $NotificationController = new NotificationController(); 
         $NotificationController->sendEmailNow();


         $smsController = new SmsController(); 
         $smsController->checkSmsToBeSendNow();
         
         return 0;
    }
}
