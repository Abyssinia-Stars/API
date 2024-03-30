<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    //




    public function showEventsByArtist( Request $request)
    {

        $id=$request->query('id');

        $events = Event::where('artist_id', $id)->get();
        return response()->json(['Events' => $events]);
    }


    public function index()
    {
        return Event::all();


    }

    public function store(Request $request)
    {


        $out = new \Symfony\Component\Console\Output\ConsoleOutput();

        $out->writeln("Here is" . json_encode($request->all()));




        $validation = Validator::make($request->all(), [

            'artist_id' => 'required|exists:users,id',
            'start' => 'required|date',
            'end' => 'required|date',
            'is_availabile' => 'required|boolean',
        ]);

        if ($validation->fails()) {

            return response()->json(['error' => $validation->errors()], 401);
        }







        $event = Event::create($request->all());
        return response()->json($event, 201);
    }

    public function show(Event $event)
    {
        return $event;
    }

    public function update(Request $request, Event $event)
    {
        $event->update($request->all());
        return response()->json($event, 200);
    }

    public function destroy($id)
    {

        $event = Event::where('id', $id)->first();
        $is_deleted = $event->delete();
        return response()->json(["Is deleted" => $is_deleted], 200);
    }




}