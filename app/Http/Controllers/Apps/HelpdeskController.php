<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class HelpdeskController extends Controller
{
    /**
     * Display the helpdesk guide.
     */
    public function index()
    {
        return Inertia::render('Dashboard/Helpdesk/Index');
    }
}
