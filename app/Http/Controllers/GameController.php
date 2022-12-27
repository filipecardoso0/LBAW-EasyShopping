<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\GameCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function index(int $gameid){
        //Queries the database in order to obtain game info of the given gameid
        $game = Game::find($gameid);
        $gamecategories = GameCategories::getGameCategories($gameid);

        return view('pages.gamerelated.game')
            ->with('game', $game)
            ->with('categories', $gamecategories);
    }

    public function showAll(){
        $games = Game::orderBy('title')->paginate(12);
        return view('pages.gamerelated.games')
            ->with('games', $games);
    }

    public function showBestSellers(){
        $games = Game::getBestSellersWithPagination();
        return view('pages.gamerelated.bestsellers')
            ->with('games', $games);
    }

    public function showComingSoon(){
        $games = $this->getComingSoonWithPagination(0);
        return view('pages.gamerelated.comingsoon')
            ->with('games', $games);
    }

    //Get x games that have not been launched yet but are already available for purchasing (pre-ordering)
    public function getComingSoon(int $x){

        if($x > 0){
            $currdate = Carbon::now(); //Gets Current Time
            $tobereleased = Game::where('release_date', '>', $currdate)->take($x)->get(); //Gets all the games that are coming soon
            return $tobereleased;
        }

        $currdate = Carbon::now(); //Gets Current Time
        $tobereleased = Game::where('release_date', '>', $currdate)->get(); //Gets all the games that are coming soon
        return $tobereleased;
    }

    //Get all the games that have not been launched yet but are already available for purchasing (pre-ordering) in pagination mode
    public function getComingSoonWithPagination(){
        $currdate = Carbon::now(); //Gets Current Time
        $tobereleased = Game::where('release_date', '>', $currdate)->paginate(12); //Gets all the games that are coming soon
        return $tobereleased;
    }

    /**
     * Shows the search result for games.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function search(Request $request)
    {
        // Get the search value from the request
        $search = $request->input('search');

        $games = Game::query()
                ->whereRaw('tsvectors @@ plainto_tsquery(\'english\', ?)', "%{$search}%")
                ->orWhere('title', 'ILIKE', "%{$search}%")
                ->orderByRaw('ts_rank(tsvectors, plainto_tsquery(\'english\',?)) DESC', "%{$search}%")
                ->paginate(12);

        return view('pages.search', ['results' => $games]);
    }


    public function searchAJAX(Request $request, $gametitle)
    {

        // Get the search value from the request
        $search = $gametitle;

        $games = Game::query()
            ->whereRaw('tsvectors @@ plainto_tsquery(\'english\', ?)', "%{$search}%")
            ->orWhere('title', 'ILIKE', "%{$search}%")
            ->orderByRaw('ts_rank(tsvectors, plainto_tsquery(\'english\',?)) DESC', "%{$search}%")
            ->get();

        echo json_encode($games);
    }

}
