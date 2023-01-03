<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function storeTicket(Request $request){
        $validated = $request->validate([
            'username' => 'required|string',
            'email' => 'required|string',
            'issuetype' => 'required|string',
            'subject' => 'required|string',
            'message' => 'required|string'
        ]);

        //TODO FINALIZE THE TICKET SYSTEM LATER WHEN THE ADMIN PAGE IS COMPLETED OR ALMOST COMPLETED
        Ticket::create([
            'userid' => $request->user()->userid,
            'type' => $request->get('issuetype'),
            'subject' => $request->get('subject'),
            'message' => $request->get('message')
        ]);

        //TODO ADD A NOTIFICATION OR A MESSAGE SAYING THAT TICKET WAS SUCCESSFULLY SUBMITED
        return redirect()->route('homepage');
    }
}
