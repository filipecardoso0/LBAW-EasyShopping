<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaticPagesController extends Controller
{
    // FAQs
    /**
     * Shows the frequently asked questions page
     * 
     * @return Response
     */
    public function showFaqs()
    {
        return view('pages.faqs');
    }

    // About
    /**
     * Shows the frequently asked questions page
     * 
     * @return Response
     */
    public function showAbout()
    {
        return view('pages.about');
    }

    // Contatcs
    /**
     * Shows the frequently asked questions page
     * 
     * @return Response
     */
    public function showContacts()
    {
        return view('pages.contacts');
    }
}