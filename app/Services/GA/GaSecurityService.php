<?php

namespace App\Services\GA;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GaSecurityService
{
    const STATUS_IN = 'IN';
    const STATUS_OUT = 'OUT';

    const INCIDENT_REPORTED = 'REPORTED';
    const INCIDENT_INVESTIGATING = 'INVESTIGATING';
    const INCIDENT_RESOLVED = 'RESOLVED';
    const INCIDENT_CLOSED = 'CLOSED';

    const CONFIDENTIALITY_NORMAL = 'NORMAL';
    const CONFIDENTIALITY_CONFIDENTIAL = 'CONFIDENTIAL';
    const CONFIDENTIALITY_STRICTLY_CONFIDENTIAL = 'STRICTLY_CONFIDENTIAL';

    protected $notifications;

    public function __construct(GaNotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Catat pengunjung masuk di pos sekuriti.
     */
    public function recordVisitorEntry(array $data, $user = null)
    {
        $visitorName = trim((string) ($data['Visitor_Name'] ?? ''));
        $hostName = trim((string) ($data['Host_Name'] ?? ''));
        $purpose = trim((string) ($data['Purpose'] ?? ''));

        if ($visitorName === '' || $hostName === '' || $purpose === '') {
            throw new InvalidArgumentException('Nama Tamu, Nama Host/Tujuan, dan Keperluan wajib diisi.');
        }

        $now = Carbon::now();
        $dateStr = $now->format('Ymd');
        $count = DB::table('ga_security_visitor')->whereDate('Time_In', $now->toDateString())->count() + 1;
        $visitorNo = 'VIS/' . $dateStr . '/' . sprintf('%04d', $count);
        $recordedBy = $user ? $user->username : 'system';

        $id = DB::table('ga_security_visitor')->insertGetId([
            'Visitor_No' => $visitorNo,
            'Visitor_Name' => $visitorName,
            'Identity_Number' => $data['Identity_Number'] ?? null,
            'Institution' => $data['Institution'] ?? null,
            'Phone_Number' => $data['Phone_Number'] ?? null,
            'Host_User_Id' => $data['Host_User_Id'] ?? null,
            'Host_Name' => $hostName,
            'Host_Department' => $data['Host_Department'] ?? null,
            'Purpose' => $purpose,
            'Location' => $data['Location'] ?? ($user && !empty($user->Location) ? $user->Location : 'HO'),
            'Post_Name' => $data['Post_Name'] ?? 'Pos Utama',
            'Badge_Number' => $data['Badge_Number'] ?? null,
            'Time_In' => $now,
            'Vehicle_Number' => $data['Vehicle_Number'] ?? null,
            'Status' => self::STATUS_IN,
            'Remarks' => $data['Remarks'] ?? null,
            'Recorded_By' => $recordedBy,
            'Created_At' => $now,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_security_visitor')->where('id', $id)->first();
    }

    /**
     * Catat pengunjung keluar (checkout).
     */
    public function recordVisitorExit($id, array $data = [], $user = null)
    {
        $visitor = DB::table('ga_security_visitor')->where('id', (int) $id)->first();
        if (!$visitor) {
            throw new InvalidArgumentException('Data pengunjung tidak ditemukan.');
        }
        if ($visitor->Status === self::STATUS_OUT) {
            throw new InvalidArgumentException('Pengunjung ' . $visitor->Visitor_Name . ' sudah tercatat keluar sebelumnya.');
        }

        $now = Carbon::now();
        $checkoutBy = $user ? $user->username : 'system';

        DB::table('ga_security_visitor')->where('id', $visitor->id)->update([
            'Time_Out' => $now,
            'Status' => self::STATUS_OUT,
            'Check_Out_By' => $checkoutBy,
            'Remarks' => !empty($data['Remarks']) ? $visitor->Remarks . ' | ' . $data['Remarks'] : $visitor->Remarks,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_security_visitor')->where('id', $visitor->id)->first();
    }

    /**
     * Catat insiden keamanan (Incident Report).
     */
    public function recordIncident(array $data, $user = null)
    {
        $title = trim((string) ($data['Title'] ?? ''));
        $type = trim((string) ($data['Incident_Type'] ?? ''));
        $description = trim((string) ($data['Description'] ?? ''));

        if ($title === '' || $type === '' || $description === '') {
            throw new InvalidArgumentException('Judul, Jenis Insiden, dan Kronologi/Deskripsi wajib diisi.');
        }

        $now = Carbon::now();
        $date = !empty($data['Incident_Date']) ? Carbon::parse($data['Incident_Date'])->toDateString() : $now->toDateString();
        $time = !empty($data['Incident_Time']) ? $data['Incident_Time'] : $now->format('H:i:s');
        $count = DB::table('ga_security_incident')->whereYear('Created_At', $now->year)->count() + 1;
        $incidentNo = 'INC/' . $now->format('Ymd') . '/' . sprintf('%04d', $count);

        $confidentiality = strtoupper(trim((string) ($data['Confidentiality'] ?? self::CONFIDENTIALITY_NORMAL)));
        $severity = strtoupper(trim((string) ($data['Severity'] ?? 'MEDIUM')));
        $username = $user ? $user->username : 'system';
        $reporterName = !empty($data['Reporter_Name']) ? $data['Reporter_Name'] : ($user && !empty($user->Name) ? $user->Name : $username);

        $id = DB::table('ga_security_incident')->insertGetId([
            'Incident_No' => $incidentNo,
            'Title' => $title,
            'Incident_Type' => $type,
            'Severity' => $severity,
            'Confidentiality' => $confidentiality,
            'Incident_Date' => $date,
            'Incident_Time' => $time,
            'Location' => $data['Location'] ?? ($user && !empty($user->Location) ? $user->Location : 'HO'),
            'Specific_Location' => $data['Specific_Location'] ?? null,
            'Description' => $description,
            'Involved_Parties' => $data['Involved_Parties'] ?? null,
            'Initial_Action' => $data['Initial_Action'] ?? null,
            'Status' => self::INCIDENT_REPORTED,
            'Reporter_Name' => $reporterName,
            'Reported_By' => $username,
            'Created_At' => $now,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_security_incident')->where('id', $id)->first();
    }

    /**
     * Selesaikan atau tutup insiden keamanan.
     */
    public function resolveIncident($id, array $data, $user = null)
    {
        $incident = DB::table('ga_security_incident')->where('id', (int) $id)->first();
        if (!$incident) {
            throw new InvalidArgumentException('Data insiden tidak ditemukan.');
        }

        $now = Carbon::now();
        $username = $user ? $user->username : 'system';
        $toStatus = !empty($data['Status']) ? strtoupper(trim((string) $data['Status'])) : self::INCIDENT_RESOLVED;

        DB::table('ga_security_incident')->where('id', $incident->id)->update([
            'Status' => $toStatus,
            'Resolved_By' => $username,
            'Resolution_Notes' => $data['Resolution_Notes'] ?? 'Insiden telah ditangani.',
            'Resolved_At' => $now,
            'Updated_At' => $now,
        ]);

        return DB::table('ga_security_incident')->where('id', $incident->id)->first();
    }

    /**
     * Buat tiket tindak lanjut (Follow-up Ticket) dari insiden untuk tim teknis/perbaikan,
     * menyembunyikan detail sensitif/konfidensial dari publik.
     */
    public function spawnFollowUpTicket($incidentId, array $ticketData = [], $user = null)
    {
        $incident = DB::table('ga_security_incident')->where('id', (int) $incidentId)->first();
        if (!$incident) {
            throw new InvalidArgumentException('Insiden tidak ditemukan.');
        }

        $now = Carbon::now();
        $ticketNo = 'TCK/SEC/' . $now->format('Ymd') . '/' . $incident->id;

        // Header minimalis tanpa rincian konfidensial
        $subject = 'Tindak Lanjut Insiden ' . $incident->Incident_No . ': ' . ($ticketData['Title'] ?? $incident->Title);
        $description = 'Tindak lanjut perbaikan fisik/fasilitas terkait insiden pengamanan. ' .
            (!empty($ticketData['Action_Needed']) ? 'Instruksi: ' . $ticketData['Action_Needed'] : 'Mohon periksa area: ' . ($incident->Specific_Location ?: $incident->Location));

        $ticketId = DB::table('ticket')->insertGetId([
            'Ticket_No' => $ticketNo,
            'Proposed_Date' => $now->toDateString(),
            'Location' => $incident->Location,
            'Ticket_Subject' => mb_substr($subject, 0, 250),
            'Description' => mb_substr($description, 0, 250),
            'Ticket_Status' => 'OPEN',
            'Priority' => $incident->Severity === 'CRITICAL' ? 'CRITICAL' : 'HIGH',
            'Module' => 'GA',
            'Ticket_Type' => 'GA',
            'Ticket_Category' => 'Security',
            'Reporter' => $user ? $user->username : 'security',
            'Created_By' => $user ? $user->username : 'security',
            'Created_Date' => $now,
        ]);

        // Hubungkan tiket ke insiden
        DB::table('ga_security_incident')->where('id', $incident->id)->update([
            'Ticket_ID' => $ticketId,
            'Updated_At' => $now,
        ]);

        return [
            'ticket_id' => $ticketId,
            'ticket_no' => $ticketNo,
            'incident_no' => $incident->Incident_No
        ];
    }

    /**
     * Audit log saat seseorang membuka detail insiden yang bersifat CONFIDENTIAL.
     */
    public function logConfidentialView($incidentId, $user = null, $ip = null)
    {
        DB::table('ga_security_incident_view_log')->insert([
            'Incident_ID' => (int) $incidentId,
            'Viewed_By' => $user ? $user->username : 'system',
            'Role' => $user && !empty($user->Position) ? $user->Position : 'STAFF',
            'IP_Address' => $ip ?: '127.0.0.1',
            'Viewed_At' => Carbon::now(),
        ]);
    }
}
