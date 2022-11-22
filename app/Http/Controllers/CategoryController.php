<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::paginate(4); //Gets all in pagination mode
        return view('pages.category')
            ->with('categories', $categories);
    }

    public function getCategories(int $x){
        if($x > 0){
            return Category::take($x)->get();
        }

        $categories = Category::get(); //Gets all in pagination mode
    }
}
