<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use App\Classes\Overview;

class OverviewController extends Controller
{

    public function index(){
        $overview = new Overview();
        return view('pages.overview')
            ->with('gamesoons', $overview->getComingSoon(12))
            ->with('categories', $overview->getCategories(5))
            ->with('bestsellers', Game::getBestSellers(12))
            ->with('latestgames', $overview->getLatestGames(12));
    }
}
