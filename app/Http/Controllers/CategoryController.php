<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
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

    //Gets all Category Games in Pagination Mode
    public function showCategoryGames($categoryid){
        //TODO COMPLETE THIS
    }
}
