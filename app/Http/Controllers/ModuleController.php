<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(string $module)
    {
        if ($module === 'company-settings') {
            return view('modules.company-settings.index');
        }

        // Keep other modules as placeholders for now
        return view('modules.placeholder', ['module' => $module, 'section' => null, 'page' => null]);
    }

    public function section(string $module, string $section)
    {
        return view('modules.placeholder', ['module' => $module, 'section' => $section, 'page' => null]);
    }

    public function page(string $module, string $section, string $page)
    {
        return view('modules.placeholder', ['module' => $module, 'section' => $section, 'page' => $page]);
    }
}
