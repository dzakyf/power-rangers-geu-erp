<?php

namespace App\Services\GA;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GaRoomBookingService
{
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_WAITING_APPROVAL = 'WAITING_APPROVAL';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_COMPLETED = 'COMPLETED';

    protected $notifications;

    public function __construct(GaNotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function registerRoom(array $data, $user = null)
    {
        $code = trim((string) ($data['Room_Code'] ?? ''));
        $name = trim((string) ($data['Room_Name'] ?? ''));

        if ($code === '' || $name === '') {
            throw new InvalidArgumentException('Kode dan Nama Ruangan wajib diisi.');
        }

        if (DB::table('ga_room')->where('Room_Code', $code)->exists()) {
            throw new InvalidArgumentException('Kode ruangan ' . $code . ' sudah ada.');
        }

        $now = Carbon::now();
        $username = $user ? $user->username : 'system';

        $id = DB::table('ga_room')->insertGetId([
            'Room_Code' => $code,
            'Room_Name' => $name,
            'Location' => $data['Location'] ?? '',
            'Building' => $data['Building'] ?? null,
            'Floor' => $data['Floor'] ?? null,
            'Capacity' => (int) ($data['Capacity'] ?? 10),
            'Facilities' => $data['Facilities'] ?? null,
            'Is_Restricted' => !empty($data['Is_Restricted']) ? 1 : 0,
            'Status' => $data['Status'] ?? 'ACTIVE',
            'Created_By' => $username,
            'Created_At' => $now,
            'Updated_By' => $username,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_room')->where('id', $id)->first();
    }

    public function updateRoom($id, array $data, $user = null)
    {
        $room = DB::table('ga_room')->where('id', (int) $id)->first();
        if (!$room) {
            throw new InvalidArgumentException('Ruangan tidak ditemukan.');
        }

        $now = Carbon::now();
        $username = $user ? $user->username : 'system';

        DB::table('ga_room')->where('id', $room->id)->update([
            'Room_Name' => $data['Room_Name'] ?? $room->Room_Name,
            'Location' => $data['Location'] ?? $room->Location,
            'Building' => $data['Building'] ?? $room->Building,
            'Floor' => $data['Floor'] ?? $room->Floor,
            'Capacity' => isset($data['Capacity']) ? (int) $data['Capacity'] : $room->Capacity,
            'Facilities' => $data['Facilities'] ?? $room->Facilities,
            'Is_Restricted' => isset($data['Is_Restricted']) ? (!empty($data['Is_Restricted']) ? 1 : 0) : $room->Is_Restricted,
            'Status' => $data['Status'] ?? $room->Status,
            'Updated_By' => $username,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_room')->where('id', $room->id)->first();
    }

    public function bookRoom(array $data, $user = null)
    {
        $roomId = (int) ($data['Room_ID'] ?? 0);
        $room = DB::table('ga_room')->where('id', $roomId)->first();
        if (!$room) {
            throw new InvalidArgumentException('Ruangan tidak ditemukan.');
        }
        if ($room->Status !== 'ACTIVE') {
            throw new InvalidArgumentException('Ruangan ' . $room->Room_Name . ' saat ini tidak aktif/dalam perbaikan.');
        }

        $date = $this->normalizeDate($data['Booking_Date'] ?? null);
        $startTime = $this->normalizeTime($data['Start_Time'] ?? null);
        $endTime = $this->normalizeTime($data['End_Time'] ?? null);

        if ($startTime >= $endTime) {
            throw new InvalidArgumentException('Jam selesai harus lebih besar dari jam mulai.');
        }

        // Cek bentrok jadwal (Overlapping Bookings)
        $conflict = DB::table('ga_room_booking')
            ->where('Room_ID', $roomId)
            ->where('Booking_Date', $date)
            ->whereIn('Status', [self::STATUS_CONFIRMED, self::STATUS_WAITING_APPROVAL])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($sub) use ($startTime, $endTime) {
                    $sub->where('Start_Time', '<', $endTime)
                        ->where('End_Time', '>', $startTime);
                });
            })
            ->first();

        if ($conflict) {
            throw new InvalidArgumentException(
                'Jadwal bentrok! Ruangan ' . $room->Room_Name . ' sudah dibooking pada ' .
                $date . ' (' . substr($conflict->Start_Time, 0, 5) . ' - ' . substr($conflict->End_Time, 0, 5) . ') oleh ' . $conflict->Organizer_Name . ' - ' . $conflict->Subject
            );
        }

        $subject = trim((string) ($data['Subject'] ?? ''));
        if ($subject === '') {
            throw new InvalidArgumentException('Judul / Subjek rapat wajib diisi.');
        }

        $organizerName = trim((string) ($data['Organizer_Name'] ?? ($user ? $user->username : '')));
        if ($organizerName === '') {
            throw new InvalidArgumentException('Nama pemesan / organizer wajib diisi.');
        }

        $status = !empty($room->Is_Restricted) ? self::STATUS_WAITING_APPROVAL : self::STATUS_CONFIRMED;
        $now = Carbon::now();
        $username = $user ? $user->username : 'system';
        $bookingNo = 'BKG/ROOM/' . $now->format('YmdHis') . '/' . $room->id;

        $id = DB::table('ga_room_booking')->insertGetId([
            'Booking_No' => $bookingNo,
            'Room_ID' => $room->id,
            'Booking_Date' => $date,
            'Start_Time' => $startTime,
            'End_Time' => $endTime,
            'Organizer_User_Id' => $data['Organizer_User_Id'] ?? ($user ? (string) $user->id : null),
            'Organizer_Name' => $organizerName,
            'Organizer_Department' => $data['Organizer_Department'] ?? null,
            'Organizer_Contact' => $data['Organizer_Contact'] ?? null,
            'Subject' => $subject,
            'Purpose' => $data['Purpose'] ?? null,
            'Attendees_Count' => (int) ($data['Attendees_Count'] ?? 1),
            'Status' => $status,
            'Need_Projector' => !empty($data['Need_Projector']) ? 1 : 0,
            'Need_Sound' => !empty($data['Need_Sound']) ? 1 : 0,
            'Need_Layout' => !empty($data['Need_Layout']) ? 1 : 0,
            'Layout_Note' => $data['Layout_Note'] ?? null,
            'Created_By' => $username,
            'Created_At' => $now,
            'Updated_By' => $username,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_room_booking')->where('id', $id)->first();
    }

    public function cancelBooking($id, $reason = '', $user = null)
    {
        $booking = DB::table('ga_room_booking')->where('id', (int) $id)->first();
        if (!$booking) {
            throw new InvalidArgumentException('Data booking tidak ditemukan.');
        }
        if ($booking->Status === self::STATUS_CANCELLED) {
            throw new InvalidArgumentException('Booking sudah dibatalkan sebelumnya.');
        }

        $now = Carbon::now();
        $username = $user ? $user->username : 'system';

        DB::table('ga_room_booking')->where('id', $booking->id)->update([
            'Status' => self::STATUS_CANCELLED,
            'Cancellation_Reason' => $reason !== '' ? $reason : 'Dibatalkan oleh user',
            'Updated_By' => $username,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_room_booking')->where('id', $booking->id)->first();
    }

    public function scheduleGrid($roomId, $date = null)
    {
        $date = $this->normalizeDate($date);

        return DB::table('ga_room_booking as b')
            ->join('ga_room as r', 'r.id', '=', 'b.Room_ID')
            ->where('b.Room_ID', (int) $roomId)
            ->where('b.Booking_Date', $date)
            ->whereIn('b.Status', [self::STATUS_CONFIRMED, self::STATUS_WAITING_APPROVAL])
            ->select('b.*', 'r.Room_Name', 'r.Room_Code')
            ->orderBy('b.Start_Time', 'ASC')
            ->get();
    }

    protected function normalizeDate($value)
    {
        if ($value === null || $value === '') {
            return Carbon::now()->toDateString();
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Format tanggal booking tidak valid.');
        }
    }

    protected function normalizeTime($value)
    {
        $val = trim((string) $value);
        if ($val === '') {
            throw new InvalidArgumentException('Jam wajib diisi.');
        }

        if (strlen($val) === 5) {
            $val .= ':00';
        }

        return $val;
    }
}
