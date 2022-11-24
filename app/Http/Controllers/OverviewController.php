<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\Overview;

class OverviewController extends Controller
{

    public function index(){
        $overview = new Overview();
        return view('pages.overview')
            ->with('gamesoons', $overview->getComingSoon(12))
            ->with('categories', $overview->getCategories(5))
            ->with('bestsellers', $overview->getBestSellers(12));
    }
}
