<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Contract;
use App\Models\Log;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $contacts = Contact::whereNotNull('created_at')->get();
        $cCount = count($contacts);
        $contracts = Contract::whereNotNull('created_at')->get();
        $crCount = count($contracts);
        $logs = Log::select('id', 'date_time', 'job_name', 'action_name', 'message', 'status')->whereNotNull('created_at')->orderBy('date_time', 'ASC')->get();
        $lCount = count($logs);
        $admins = User::where('role_id', 1)->whereNotNull('created_at')->get();
        $aCount = count($admins);

        return view('dashboard', [
            'logs' => $logs->sortBy('date_time', SORT_REGULAR, false),
            'cCount' => $cCount,
            'qCount' => 0,
            'crCount' => $crCount,
        ]);
    }
}
