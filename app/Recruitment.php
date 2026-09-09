<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    protected $table = 'recruitment';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    public $timestamps = false;

    protected $guarded = [];

    // Konstanta status
    const STATUS_CREATED       = 'CREATED';
    const STATUS_UPLOADED      = 'UPLOADED';
    const STATUS_PSIKOTEST     = 'PSIKOTEST';
    const STATUS_PENDING       = 'PENDING';
    const STATUS_INTERVIEWED   = 'INTERVIEWED';
    const STATUS_BUSINESS_REVIEW = 'BUSINESS_REVIEW';
    const STATUS_CLOSED        = 'CLOSED';
    const STATUS_REJECT        = 'REJECT';
    const STATUS_OFFER_DECLINED = 'OFFER DECLINED';
    const STATUS_INVITATION_DECLINED = 'INVITATION DECLINED';
    const STATUS_CANCELED      = 'CANCELED';

    // Status kolom tahap
    const DOC_ACCEPT           = 'ACCEPT';
    const PSIKO_ACCEPT         = 'ACCEPT';
    const INTERVIEW_ACCEPT     = 'ACCEPT';
    const USER_INTERVIEW_ACCEPT = 'ACCEPT';
    const TECH_ACCEPT          = 'ACCEPT';
    const MCU_ACCEPT           = 'ACCEPT';
    const OFFERING_ACCEPT      = 'ACCEPT';

    /**
     * Scope untuk mengambil kandidat yang belum ditolak
     */
    public function scopeActive($query)
    {
        return $query->where('Status', '!=', self::STATUS_CANCELED);
    }

    /**
     * Scope untuk filter berdasarkan location
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('Location', $location);
    }

    /**
     * Scope untuk filter berdasarkan requisition ID
     */
    public function scopeByRequisition($query, $requisitionId)
    {
        return $query->where('Requisition_ID', $requisitionId);
    }
}
