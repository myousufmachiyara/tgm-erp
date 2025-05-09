<?php

namespace App\Http\Controllers;

use App\Models\JournalVoucher1;
use Illuminate\Http\Request;

class JournalVoucher1Controller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jv1 = JournalVoucher1::with(['debitAccount', 'creditAccount', 'voucherDetails'])->get();

        return view('finance.jv1.index', compact('jv1'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
