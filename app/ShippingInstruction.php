<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingInstruction extends Model
{
    protected $table = 'shipping_instructions';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    /**
     * Generate the next unique Final_SI_No for the month/year of $finalSiDate.
     */
    public static function generateNextFinalSiNo(?string $finalSiDate = null): string
    {
        $timestamp = ($finalSiDate !== null && trim($finalSiDate) !== '') ? strtotime($finalSiDate) : false;
        if ($timestamp === false) {
            $timestamp = time();
        }

        $romans = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $roman = $romans[(int) date('n', $timestamp)];
        $year = date('Y', $timestamp);
        $prefix = 'FINALSI/EXP/';
        $suffix = "/{$roman}/{$year}";

        $existing = self::query()
            ->whereNotNull('Final_SI_No')
            ->where('Final_SI_No', 'like', $prefix . '%' . $suffix)
            ->pluck('Final_SI_No')
            ->all();

        return self::nextFinalSiNoFromExisting($existing, $prefix, $suffix);
    }

    /**
     * Compute the next Final_SI_No from existing numbers sharing the same prefix and suffix.
     */
    public static function nextFinalSiNoFromExisting(array $existing, string $prefix, string $suffix): string
    {
        $maxSeq = 0;
        $used = [];
        $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)' . preg_quote($suffix, '/') . '$/';

        foreach ($existing as $no) {
            $trimmed = trim((string) $no);
            if ($trimmed === '') {
                continue;
            }
            $used[$trimmed] = true;
            if (preg_match($pattern, $trimmed, $m)) {
                $seq = (int) $m[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $next = $maxSeq + 1;
        do {
            $candidate = $prefix . sprintf('%03d', $next) . $suffix;
            $next++;
        } while (isset($used[$candidate]));

        return $candidate;
    }
}
