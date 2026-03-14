<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $vehicles = Vehicle::disponivel()
            ->select('slug', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get();

        $content = view('site.sitemap', compact('vehicles'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
