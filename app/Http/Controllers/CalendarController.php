<?php

namespace App\Http\Controllers;

use App\Models\jadwal;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(){
        $events = jadwal::all();

        return response()->json($events);
    }

}
