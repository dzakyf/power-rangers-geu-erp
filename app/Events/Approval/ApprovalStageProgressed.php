<?php

namespace App\Events\Approval;

use Illuminate\Queue\SerializesModels;

class ApprovalStageProgressed
{
    use SerializesModels;

    /**
     * Objek baris approval_request terbaru.
     *
     * @var object
     */
    public $request;

    /**
     * Stage_Order yang baru saja selesai.
     *
     * @var int
     */
    public $completedStage;

    /**
     * Stage_Order aktif berikutnya.
     *
     * @var int
     */
    public $nextStage;

    /**
     * Level_Number aktif berikutnya.
     *
     * @var int|null
     */
    public $nextLevel;

    /**
     * @param object $request
     * @param int $completedStage
     * @param int $nextStage
     * @param int|null $nextLevel
     */
    public function __construct($request, int $completedStage, int $nextStage, int $nextLevel = null)
    {
        $this->request        = $request;
        $this->completedStage = $completedStage;
        $this->nextStage      = $nextStage;
        $this->nextLevel      = $nextLevel;
    }
}
