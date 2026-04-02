<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class GeneratorService
{

    public function generateTaskID()
    {
        $now = Carbon::now()->format('YmdHisv');
        $location = UserService::getUserOfficeLocation();

        return 'BP/' . $location . '/' . $now . '/' . '212';
    }

    public function generateUUID()
    {
        return Str::uuid()->toString();
    }

    public function rupiahFormatter(int $amount): string
    {
        $prefix = $amount < 0 ? 'Rp -' : 'Rp ';

        return $prefix . number_format(abs($amount), 2, ',', '.');
    }

    public function generateApplicationCode($count, $topic)
    {
        $now = now()->format('Ym');
        $uniqcode = strtoupper(base_convert(now()->timestamp, 10, 36));
        $paddedCount = str_pad($count, 5, '0', STR_PAD_LEFT);

        return "{$paddedCount}/{$topic}/{$now}{$uniqcode}";
    }

    function numberToWords(int $number): string
    {
        $units = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas',
            'dua belas',
            'tiga belas',
            'empat belas',
            'lima belas',
            'enam belas',
            'tujuh belas',
            'delapan belas',
            'sembilan belas'
        ];

        if ($number < 0) {
            return 'minus ' . $this->numberToWords(abs($number));
        }

        if ($number === 0) {
            return 'nol';
        }

        if ($number < 20) {
            return $units[$number];
        }

        if ($number < 100) {
            $tens      = (int)($number / 10);
            $remainder = $number % 10;
            return $units[$tens] . ' puluh' . ($remainder ? ' ' . $units[$remainder] : '');
        }

        if ($number < 200) {
            $remainder = $number - 100;
            return 'seratus' . ($remainder ? ' ' . $this->numberToWords($remainder) : '');
        }

        if ($number < 1000) {
            $hundreds  = (int)($number / 100);
            $remainder = $number % 100;
            return $units[$hundreds] . ' ratus' . ($remainder ? ' ' . $this->numberToWords($remainder) : '');
        }

        if ($number < 2000) {
            $remainder = $number - 1000;
            return 'seribu' . ($remainder ? ' ' . $this->numberToWords($remainder) : '');
        }

        if ($number < 1_000_000) {
            $thousands = (int)($number / 1000);
            $remainder = $number % 1000;
            return $this->numberToWords($thousands) . ' ribu' . ($remainder ? ' ' . $this->numberToWords($remainder) : '');
        }

        if ($number < 1_000_000_000) {
            $millions  = (int)($number / 1_000_000);
            $remainder = $number % 1_000_000;
            return $this->numberToWords($millions) . ' juta' . ($remainder ? ' ' . $this->numberToWords($remainder) : '');
        }

        if ($number < 1_000_000_000_000) {
            $billions  = (int)($number / 1_000_000_000);
            $remainder = $number % 1_000_000_000;
            return $this->numberToWords($billions) . ' miliar' . ($remainder ? ' ' . $this->numberToWords($remainder) : '');
        }

        $trillions = (int)($number / 1_000_000_000_000);
        $remainder = $number % 1_000_000_000_000;
        return $this->numberToWords($trillions) . ' triliun' . ($remainder ? ' ' . $this->numberToWords($remainder) : '');
    }

    function spellRupiah(int $amount): string
    {
        if ($amount === 0) return 'nol rupiah';
        return $this->numberToWords($amount) . ' rupiah';
    }
}
