<?php

namespace App\Classes;

//Controllers Import
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GameController;
use App\Models\Game;

class Overview {

    //Fetches all categories in pagination mode from the database
    public function getCategories(int $x){
        $categoryctrl = new CategoryController();
        return $categoryctrl->getCategories($x);
    }

    //Fetches x games that have high sales number
    public function getBestSellers(int $x){
        $gamectrl = new GameController();
        return $gamectrl->getBestSellers($x);
    }

    //Gets All Coming Soon Games
    public function getComingSoon(int $x){
        $gamectrl = new GameController();
        return $gamectrl->getComingSoon($x);
    }

    //Gets the first X Latest Games
    public function getLatestGames(int $limit){
        return Game::getLatestGames($limit);
    }
}
