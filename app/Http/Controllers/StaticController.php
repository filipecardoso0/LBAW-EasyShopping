<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaticController extends Controller
{
    public function showAboutPage(){
        return view('static.aboutus');
    }

    public function showContactUsPage(){
        return view('static.contactus');
    }

    public function showFAQPage(){
        return view('static.faq');
    }

    public function showFAQAccount(){
        return view('static.faqaccount');
    }

    public function showFAQGames(){
        return view('static.faqgames');
    }
}
