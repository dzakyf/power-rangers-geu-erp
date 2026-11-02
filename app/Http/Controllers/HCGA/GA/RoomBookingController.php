<?php

namespace App\Http\Controllers\HCGA\GA;

use App\Http\Controllers\Controller;
use App\Services\GA\GaRoomBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomBookingController extends Controller
{
    use GaXml;

    protected $service;

    public function __construct(GaRoomBookingService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $rooms = DB::table('ga_room')->where('Status', 'ACTIVE')->orderBy('Room_Name')->get();
        $locations = DB::table('ga_room')->select('Location')->distinct()->pluck('Location');

        return view('humanresources.ga.room-booking.index', compact('rooms', 'locations'));
    }

    public function roomData()
    {
        $rooms = DB::table('ga_room')->orderBy('id', 'ASC')->get();

        header('Content-Type: text/xml');
        echo '<rows>';
        $no = 1;
        foreach ($rooms as $r) {
            echo '<row id="' . (int) $r->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($r->Room_Code) . '</cell>';
            echo '<cell>' . $this->xml($r->Room_Name) . '</cell>';
            echo '<cell>' . $this->xml($r->Location) . '</cell>';
            echo '<cell>' . $this->xml($r->Building ?: '-') . '</cell>';
            echo '<cell>' . $this->xml($r->Floor ?: '-') . '</cell>';
            echo '<cell>' . (int) $r->Capacity . '</cell>';
            echo '<cell>' . $this->xml($r->Facilities ?: '-') . '</cell>';
            echo '<cell>' . ($r->Is_Restricted ? 'Restricted' : 'Open') . '</cell>';
            echo '<cell>' . $this->xml($r->Status) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function bookingData(Request $request)
    {
        $query = DB::table('ga_room_booking as b')
            ->join('ga_room as r', 'r.id', '=', 'b.Room_ID')
            ->select('b.*', 'r.Room_Name', 'r.Room_Code', 'r.Location');

        if ($request->filled('date')) {
            $query->where('b.Booking_Date', $request->input('date'));
        }
        if ($request->filled('room_id')) {
            $query->where('b.Room_ID', (int) $request->input('room_id'));
        }

        $bookings = $query->orderBy('b.Booking_Date', 'DESC')
            ->orderBy('b.Start_Time', 'ASC')
            ->get();

        header('Content-Type: text/xml');
        echo '<rows>';
        $no = 1;
        foreach ($bookings as $b) {
            echo '<row id="' . (int) $b->id . '">';
            echo '<cell>' . $no++ . '</cell>';
            echo '<cell>' . $this->xml($b->Booking_No) . '</cell>';
            echo '<cell>' . $this->xml($b->Room_Name) . '</cell>';
            echo '<cell>' . $this->xml($b->Booking_Date) . '</cell>';
            echo '<cell>' . substr($b->Start_Time, 0, 5) . ' - ' . substr($b->End_Time, 0, 5) . '</cell>';
            echo '<cell>' . $this->xml($b->Subject) . '</cell>';
            echo '<cell>' . $this->xml($b->Organizer_Name) . '</cell>';
            echo '<cell>' . $this->xml($b->Organizer_Department ?: '-') . '</cell>';
            echo '<cell>' . (int) $b->Attendees_Count . '</cell>';
            echo '<cell>' . $this->xml($b->Status) . '</cell>';
            echo '</row>';
        }
        echo '</rows>';
    }

    public function saveRoom(Request $request)
    {
        try {
            $room = $this->service->registerRoom($request->all(), Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Ruangan berhasil didaftarkan.', 'data' => $room]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function saveBooking(Request $request)
    {
        try {
            $booking = $this->service->bookRoom($request->all(), Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Ruangan berhasil dibooking dengan No: ' . $booking->Booking_No, 'data' => $booking]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function cancelBooking(Request $request)
    {
        try {
            $id = $request->input('id');
            $reason = $request->input('reason', '');
            $booking = $this->service->cancelBooking($id, $reason, Auth::user());
            return response()->json(['status' => 'success', 'message' => 'Booking berhasil dibatalkan.', 'data' => $booking]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
