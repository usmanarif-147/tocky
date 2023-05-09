<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ConnectRequest;
use App\Models\Category;
use App\Models\Link;
use App\Models\User;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConnectController extends Controller
{
    public function connect(ConnectRequest $request)
    {
        // check connection is valid
        $connection = User::where('id', $request->connect_id)
            ->first();

        if (!$connection) {
            return response()->json(['message' => "Connection not found"]);
        }

        // check
        $connected = DB::table('connects')
            ->where('connected_id', $request->connect_id)
            ->where('connecting_id', auth()->id())
            ->first();
        if ($connected) {
            return response()->json(['message' => "You both are already connected."]);
        }

        try {
            DB::table('connects')->insert([
                'connected_id' => $request->connect_id,
                'connecting_id' => auth()->id()
            ]);
            return response()->json(['message' => "Connected successfully"]);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    public function disconnect(ConnectRequest $request)
    {

        // check connection is valid
        $connection = User::where('id', $request->connect_id)
            ->first();

        if (!$connection) {
            return response()->json(['message' => "Connection not found"]);
        }

        $connected = DB::table('connects')
            ->where('connected_id', $request->connect_id)
            ->where('connecting_id', auth()->id())
            ->first();
        if (!$connected) {
            return response()->json(['message' => "You both not connected so connection cann't be removed"]);
        }

        try {
            DB::table('connects')
                ->where('connected_id', $request->connect_id)
                ->where('connecting_id', auth()->id())
                ->delete();
            return response()->json(['message' => "Connection removed successfully"]);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * Get all connections
     */
    public function getConnections()
    {
        $connections = User::select(
            'connection.id as connection_id',
            'connection.name as connection_name',
            'connection.username as connection_user_name',
            'connection.job_title as connection_job_title',
            'connection.company as connection_company',
            'connection.photo as connection_photo',
        )
            ->join('connects', 'connects.connecting_id', 'users.id')
            ->join('users as connection', 'connection.id', 'connects.connected_id')
            ->where('users.id', auth()->id())
            ->get();

        return response()->json(['connections' => $connections]);
    }

    /**
     * Get connection profile
     */
    public function getConnectionProfile(ConnectRequest $request)
    {
        $res['user'] = User::where('id', $request->connect_id)->first();
        if (!$res['user']) {
            return  response()->error("Profile not found");
        }


        $links = Link::where('user_id', $request->connect_id)->get();
        $platforms = DB::table('user_platforms')
            ->select(
                'platforms.id',
                'platforms.title',
                'platforms.icon',
                'platforms.input',
                'platforms.baseUrl',
                'user_platforms.created_at',
                'user_platforms.path',
                'user_platforms.label',
                'user_platforms.platform_order',
                'user_platforms.direct',
            )
            ->join('platforms', 'platforms.id', 'user_platforms.platform_id')
            ->where('user_id', $request->connect_id)
            ->orderBy(('user_platforms.platform_order'))
            ->get();

        $isConnected = DB::table('connects')
            ->where('connecting_id', auth()->id())
            ->where('connected_id', $request->connect_id)
            ->first();

        return response()->json([
            'message' => 'User profile',
            'user' => $res['user'],
            'links' => $links,
            'platforms' => $platforms,
            'is_connected' => $isConnected ? 1 : 0,
        ]);
    }
}
