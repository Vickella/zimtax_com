<?php

namespace App\Http\Controllers\Tax;

use App\Http\Controllers\Controller;

class TaxModuleController extends Controller
{
    public function index()
    {
        return view('modules.tax.index');
    }
}
