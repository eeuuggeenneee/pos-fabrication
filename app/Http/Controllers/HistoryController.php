<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index()
    {
        $history = History::orderBy('created_at', 'desc')->get();
    
        return view('history.history', ['history' => $history]);
    }
    
    public function destroy(Request $request, $id)
    {
        $product = History::findOrFail($id);
        $product->delete();

        return response()->json(['success' => true]);
    }
}
