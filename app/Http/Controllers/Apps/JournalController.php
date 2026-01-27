<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JournalController extends Controller
{
    /**
     * Display a listing of journals.
     */
    public function index(Request $request)
    {
        $journals = Journal::with('user')
            ->withSum('entries as total_debit', 'debit')
            ->withSum('entries as total_credit', 'credit')
            ->when($request->q, function ($query) use ($request) {
                $query->where('reference', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%');
            })
            ->when($request->start_date, function ($query) use ($request) {
                $query->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($query) use ($request) {
                $query->whereDate('date', '<=', $request->end_date);
            })
            ->latest('date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dashboard/Journals/Index', [
            'journals' => $journals,
            'filters' => $request->only(['q', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Display the specified journal.
     */
    public function show($id)
    {
        $journal = Journal::with(['entries.account', 'user'])->findOrFail($id);

        return Inertia::render('Dashboard/Journals/Show', [
            'journal' => $journal,
        ]);
    }
}
