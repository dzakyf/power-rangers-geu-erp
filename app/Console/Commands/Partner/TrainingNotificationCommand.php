<?php

namespace App\Console\Commands\Partner;

use App\Services\NotificationService;
use App\Training;
use App\TrainingAttendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TrainingNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:trainingnotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cron for generate training notification';

    protected $notificationService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NotificationService $n)
    {
        parent::__construct();
        $this->notificationService = $n;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cekAttendance = TrainingAttendance::where('training_date', date('Y-m-d'))
            ->where("reminder", 0)
            ->get();
        $users = DB::table('users')->get();
        foreach ($users as $key => $value) {
            $us[$value->username] = $value->id;
        }
        foreach ($cekAttendance as $key => $value) {
            $training = Training::where("id", $value->training_id)->first();
            $this->notificationService->makeNotification(
                "Pengingat Training",
                "Anda memiliki jadwal training ". $training->training_name ." hari ini, jangan lupa untuk hadir tepat waktu ya!",
                'training',
                null,
                $us[$value->participant]
            );

            TrainingAttendance::where('id', $value->id)->update([
                "reminder" => 1
            ]);
        }

        $cekTraining = Training::where('training_from_date', date('Y-m-d', strtotime('-3 days')))
            ->get();
        foreach ($cekTraining as $key => $value) {
            $this->notificationService->makeNotification(
                "Pengingat Training",
                "Anda memiliki jadwal training ". $value->training_name ." 3 hari lagi, jangan lupa untuk hadir tepat waktu ya!",
                'training',
                null,
                $us[$value->created_by]
            );
        }

        $cekTraining = Training::where('training_from_date', date('Y-m-d', strtotime('-1 day')))
            ->get();
        foreach ($cekTraining as $key => $value) {
            $this->notificationService->makeNotification(
                "Pengingat Training",
                "Anda memiliki jadwal training ". $value->training_name ." besok, jangan lupa untuk hadir tepat waktu ya!",
                'training',
                null,
                $us[$value->created_by]
            );
        }
    }
}
