<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;

class AdvertisementPageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Advertisement::query()->orderByDesc('created_at');
        if ($status) {
            $query->where('status', $status);
        }

        $ads = $query->paginate(20)->withQueryString();

        return view('ads.index', [
            'ads' => $ads,
            'status' => $status,
        ]);
    }
}


