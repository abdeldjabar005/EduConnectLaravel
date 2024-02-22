<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'author' => 'required|string|max:255',
            'quote' => 'required|string',
            'permalink' => 'required|string|unique:quotes',
        ]);

        $quote = Quote::create($request->all());

        return response()->json([
            'id' => $quote->id,
            'author' => $quote->author,
            'quote' => $quote->quote,
            'permalink' => $quote->permalink,
        ], 201);

    }


    /**
     * Display the specified resource.
     */
    public function showRandom()
    {
        $quote = Quote::inRandomOrder()->first();

        return response()->json([
            'id' => $quote->id,
            'author' => $quote->author,
            'quote' => $quote->quote,
            'permalink' => $quote->permalink,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
