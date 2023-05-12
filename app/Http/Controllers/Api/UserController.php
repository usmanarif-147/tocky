<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\ConnectRequest;
use App\Models\Group;
use App\Http\Resources\Api\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function connect(ConnectRequest $request)
    {
        $connected = DB::table('connects')->where('connected_id', $request->connect_id)
            ->where('connecting_id', auth()->id())->first();
        if ($connected) {
            $deleted = DB::table('connects')->where('connected_id', $request->connect_id)
                ->where('connecting_id', auth()->id())
                ->delete();
            if ($deleted) {
                return response()->json(['message' => "Connection removed successfully"]);
            } else {
                return response()->json(['message' => "Ooops Could not be removed"]);
            }
        }
        $connect = DB::table('connects')->insert([
            'connected_id' => $request->connect_id,
            'connecting_id' => auth()->id()
        ]);
        if ($connect) {
            return response()->json(['message' => "Connected successfully"]);
        }
        return response()->json(['message' => "Ooops Could not be removed"]);
    }

    /**
     * Private Profile
     */
    public function privateProfile()
    {

        $user = auth()->user();

        if ($user->private) {
            User::where('id', $user->id)
                ->update(
                    [
                        'private' => 0
                    ]
                );

            $user = User::find(auth()->id());
            return response()->json(['message' => "Profile is set to public", 'profile' => new ProfileResource($user)]);
        }

        User::where('id', auth()->id())
            ->update(
                [
                    'private' => 1
                ]
            );
        $user = User::find(auth()->id());
        return response()->json(['message' => "Profile is set to private", 'profile' => new ProfileResource($user)]);
    }

    /**
     * Deactivate Account
     */
    public function deactivateAccount()
    {
        $user_groups = DB::table('user_group')
            ->where('user_id', auth()->id())
            ->get();

        foreach ($user_groups as $user_group) {
            $group = Group::where('id', $user_group->group_id)->first();
            Group::where('id', $group->id)->decrement();
        }

        $updated = User::where('id', auth()->id())->update(
            [
                'status' => 0,
                'deactivated_at' => date('Y-m-d H:i:s'),
            ]
        );
        if ($updated) {
            $message = 'You have 2 weeks to recover your account, otherwise your account will be deleted.';
            return response()->json(['message' => $message]);
        }
        return response()->json(['message' => 'Something went wrong']);
    }

    /**
     * Analytics
     */
    public function analytics()
    {
        $connections = DB::table('connects')->where('connecting_id', auth()->id())->get()->count();
        $profileViews = User::where('id', auth()->id())->first()->tiks;

        $platforms = DB::table('user_platforms')
            ->select(
                'platforms.id',
                'platforms.title',
                'platforms.icon',
                'user_platforms.path',
                'user_platforms.label',
                'user_platforms.clicks',
            )
            ->join('platforms', 'platforms.id', 'user_platforms.platform_id')
            ->where('user_id', auth()->id())
            ->orderBy(('user_platforms.platform_order'))
            ->get();


        return response()->json(
            [
                'user' => [
                    [
                        'label' => 'Connections',
                        'connections' => $connections,
                        'icon' => 'uploads/photos/total_connections.png',
                    ],
                    [
                        'label' => 'Profile Views',
                        'profileViews' => $profileViews,
                        'icon' => 'uploads/photos/profile_views.png',
                    ],
                    [
                        'label' => 'Platform Clicks',
                        'total_clicks' => $platforms->sum('clicks'),
                        'icon' => 'uploads/photos/total_clicks.png',
                    ],
                    [
                        'label' => 'Platforms',
                        'total_platforms' => $platforms->count(),
                        'icon' => 'uploads/photos/total_platforms.png',
                    ],
                    [
                        'label' => 'Groups',
                        'total_groups' => Group::where('user_id', auth()->id())->count(),
                        'icon' => 'uploads/photos/total_groups.png',
                    ],
                ],
                'platforms' => $platforms
            ]
        );
    }
}
