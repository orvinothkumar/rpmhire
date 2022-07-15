<?php

namespace App\Http\Controllers;

class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        return view('pages.contracts.index');
    }
}
