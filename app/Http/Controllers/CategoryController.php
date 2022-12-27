<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //TODO FIX GAMES CATEGORIES

    //Gets all Categories in Pagination Mode
    public function index(){
        $categories = Category::paginate(4); //Gets all in pagination mode
        return view('pages.categories.category')
            ->with('categories', $categories);
    }

    public function getCategories(int $x){
        if($x > 0){
            return Category::take($x)->get();
        }

        $categories = Category::get(); //Gets all in pagination mode
    }

    public static function getAllCategories(){
        return Category::get();
    }

    //Gets all Category Games in Pagination Mode
    public function showCategoryGames($categoryid){

        $categorygames = Category::query()
                            ->selectRaw('game.gameid, game.title, game.description, game.price, game.release_date, game.classification, game.discount')
                            ->join('game_categories', 'game_categories.categoryid', '=', 'category.categoryid')
                            ->join('game', 'game.gameid', '=', 'game_categories.gameid')
                            ->where('category.categoryid', '=', $categoryid)
                            ->paginate(12);

        $categorydata = Category::find($categoryid);

        return view('pages.categories.categorygames')
            ->with('categorygames', $categorygames)
            ->with('categorydata', $categorydata);
    }

}
