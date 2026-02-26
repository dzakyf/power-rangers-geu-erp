<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function ticketNew()
    {
        return view('partner.ticket-new');
    }

    public function ticketOpen()
    {
        return view('partner.ticket-open');
    }

    public function ticketOpenXml()
    {
        return view('partner.xml.ticket-open');
    }

    public function ticketOpenDetail()
    {
        return view('partner.ticket-open-detail');
    }
}
