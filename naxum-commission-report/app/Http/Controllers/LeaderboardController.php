<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaderboardController extends Controller
{
    /**
     * Display the top distributors leaderboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $allDistributors = User::getTopDistributorsBySales();
        
        // Limit to only top 200 distributors as per requirements
        $allDistributors = array_slice($allDistributors, 0, 200);

        $perPage = 10;
        $currentPage = $request->input('page', 1);
        $pagedData = array_slice($allDistributors, ($currentPage - 1) * $perPage, $perPage);

        $distributors = new LengthAwarePaginator(
            $pagedData,
            count($allDistributors),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('leaderboard', [
            'distributors' => $distributors,
        ]);
    }
} 