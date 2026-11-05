<?php

namespace App\Events\Approval;

use Illuminate\Queue\SerializesModels;

class ApprovalRequestUpdated
{
    use SerializesModels;

    /**
     * Objek baris approval_request.
     *
     * @var object
     */
    public $request;

    /**
     * Status baru approval_request (APPROVED, REJECTED, RETURNED, CANCELLED, dsb.)
     *
     * @var string
     */
    public $status;

    /**
     * Alasan / catatan transisi status.
     *
     * @var string
     */
    public $reason;

    /**
     * @param object $request
     * @param string $status
     * @param string $reason
     */
    public function __construct($request, string $status, string $reason = '')
    {
        $this->request = $request;
        $this->status  = strtoupper(trim($status));
        $this->reason  = $reason;
    }
}

