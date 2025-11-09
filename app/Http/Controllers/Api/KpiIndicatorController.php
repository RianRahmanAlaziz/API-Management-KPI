<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiIndicator;
use Illuminate\Http\Request;

class KpiIndicatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = KpiIndicator::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }
        $KpiIndicator = $query->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data semua Kpi Indicator berhasil diambil',
            'data' => $KpiIndicator->appends([
                'search' => $request->input('search'),
            ]),
        ], 200);
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
    public function show(KpiIndicator $kpiIndicator)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KpiIndicator $kpiIndicator)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KpiIndicator $kpiIndicator)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KpiIndicator $kpiIndicator)
    {
        //
    }
}
