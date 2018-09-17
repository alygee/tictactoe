<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\History;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $history = History::all();
        return view('main', compact('history'));
    }

    public function store(Request $request)
    {
        $winner   = $request->input('winner');
        $moves    = $request->input('moves');
        $response = History::create([
            'winner' => $winner,
            'moves'  => json_encode($moves)
        ]);
        return $response;
    }
}