<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function index(int $gameid){
        //Queries the database in order to obtain game info of the given gameid
        $game = Game::find($gameid);

        return view('pages.game')
            ->with('game', $game);
    }

    public function showAll(){
        $games = Game::orderBy('title')->paginate(12);
        return view('pages.games')
            ->with('games', $games);
    }

    public function showBestSellers(){
        $games = $this->getBestSellersWithPagination();
        return view('pages.bestsellers')
            ->with('games', $games);
    }

    public function showComingSoon(){
        $games = $this->getComingSoonWithPagination(0);
        return view('pages.comingsoon')
            ->with('games', $games);
    }

    //Fetch the info about First x Games with the highest ammount of sales
    public function getBestSellers(int $x){
        //Fetches the game info from orders table join with game table in descending order and having state=true(have been bought)
        if($x > 0){
            //Select game_order.gameid, count(*) as salesgame from easyshopping.game_order Group by(gameid) Order by salesgame DESC;

            $query = DB::table('game')
                    ->select('game.gameid', 'game.title', 'game.price', 'game.discount', 'game.classification', 'game.categoryid', (DB::raw('count(*) as salesnum')))
                    ->join('game_order', 'game_order.gameid', '=', 'game.gameid')
                    ->join('order_', 'order_.orderid', '=', 'game_order.orderid')
                    ->where('order_.state', '=', 'true')
                    ->groupBy('game.gameid')
                    ->orderByRaw('salesnum DESC')
                    ->take($x)
                    ->get();
            return $query;
        }

        $query = DB::table('game')
                ->select('game.gameid', 'game.title', 'game.price', 'game.discount', 'game.classification', 'game.categoryid', 'order_.state', (DB::raw('count(*) as salesnum')))
                ->join('game_order', 'game_order.gameid', '=', 'game.gameid')
                ->join('order_', 'order_.orderid', '=', 'game_order.orderid')
                ->where('order_.state', '=', 'true')
                ->groupBy('game.gameid', 'order_.state')
                ->orderByRaw('salesnum DESC')
                ->get();

        return $query;
    }

    //Fetches game info from the game with the biggest amount of sales to the one with the least amount of sales
    public function getBestSellersWithPagination(){
        $query = DB::table('game')
                ->select('game.gameid', 'game.title', 'game.price', 'game.discount', 'game.classification', 'game.categoryid', 'order_.state', (DB::raw('count(*) as salesnum')))
                ->join('game_order', 'game_order.gameid', '=', 'game.gameid')
                ->join('order_', 'order_.orderid', '=', 'game_order.orderid')
                ->where('order_.state', '=', 'true')
                ->groupBy('game.gameid', 'order_.state')
                ->orderByRaw('salesnum DESC')
                ->paginate(12);

        return $query;
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

}
