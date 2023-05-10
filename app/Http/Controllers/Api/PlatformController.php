<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Platform\AddPlatformRequest;
use App\Http\Requests\Api\Platform\IncrementRequest;
use App\Http\Requests\Api\Platform\PlatformRequest;
use App\Http\Requests\Api\Platform\SwapPlatformRequest;
use App\Models\Platform;
use Exception;
use Illuminate\Support\Facades\DB;

class PlatformController extends Controller
{
    // public function add(AddPlatformRequest $request)
    // {

    //     $platform = Platform::where('id', $request->platform_id)->first();
    //     if (!$platform) {
    //         return response()->json(['message' => 'Platform not found']);
    //     }

    //     $platform = DB::table('user_platforms')
    //         ->where('platform_id', $request->platform_id)
    //         ->where('user_id', auth()->id())
    //         ->first();

    //     try {
    //         if ($platform) {
    //             DB::table('user_platforms')
    //                 ->where('platform_id', $request->platform_id)
    //                 ->where('user_id', auth()->id())
    //                 ->update([
    //                     'label' => $request->label,
    //                     'path' => $request->path,
    //                 ]);

    //             $userPlatform = $this->userPlatform($request->platform_id);
    //             if ($userPlatform) {
    //                 return response()->json(['message' => "Platform updated successfully", 'data' => $userPlatform]);
    //             }
    //         } else {
    //             $userPlatform = DB::table('user_platforms')->insert([
    //                 'user_id' => auth()->id(),
    //                 'platform_id' => $request->platform_id,
    //                 'direct' => 1,
    //                 'label' => $request->label,
    //                 'path' => $request->path,
    //                 'platform_order' => 1
    //             ]);

    //             $userPlatform = $this->userPlatform($request->platform_id);
    //             return response()->json(["message" => "Platform added successfully", 'data' => $userPlatform]);
    //         }
    //     } catch (Exception $ex) {
    //         return response()->json(["message" => $ex->getMessage()]);
    //     }
    // }

    public function add(AddPlatformRequest $request)
    {

        $platform = Platform::where('id', $request->platform_id)->first();
        if (!$platform) {
            return response()->json(['message' => 'Platform not found']);
        }

        $platform = DB::table('user_platforms')
            ->where('platform_id', $request->platform_id)
            ->where('user_id', auth()->id())
            ->first();

        try {
            if ($platform) {
                DB::table('user_platforms')
                    ->where('platform_id', $request->platform_id)
                    ->where('user_id', auth()->id())
                    ->update([
                        'label' => $request->label,
                        'path' => $request->path,
                    ]);

                $userPlatform = $this->userPlatform($request->platform_id);
                if ($userPlatform) {
                    return response()->json(['message' => "Platform updated successfully", 'data' => $userPlatform]);
                }
            } else {

                $latestPlatform = DB::table('user_platforms')
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->first();

                $userPlatform = DB::table('user_platforms')->insert([
                    'user_id' => auth()->id(),
                    'platform_id' => $request->platform_id,
                    'direct' => 1,
                    'label' => $request->label,
                    'path' => $request->path,
                    'platform_order' => $latestPlatform ? ($latestPlatform->platform_order + 1) : 1,
                ]);

                $userPlatform = $this->userPlatform($request->platform_id);
                return response()->json(["message" => "Platform added successfully", 'data' => $userPlatform]);
            }
        } catch (Exception $ex) {
            return response()->json(["message" => $ex->getMessage()]);
        }
    }

    /**
     * Remove platform
     */
    public function remove(PlatformRequest $request)
    {
        $platform = DB::table('user_platforms')
            ->where('user_id', auth()->id())
            ->where('platform_id', $request->platform_id)
            ->delete();
        if (!$platform) {
            return response()->json(['message' => 'Platform is not exist']);
        }

        return response()->json(['message' => 'Plateform removed successfully']);
    }

    /**
     * Swap order
     */
    public function swap(SwapPlatformRequest $request)
    {
        // if (!is_array($request->orderList)) {
        //     return response()->json(['message' => "order list must be an array"]);
        // }

        // $orderList = json_decode(json_encode($request->orderList));

        // foreach ($orderList as $platform) {
        //     DB::table('user_platforms')->where('user_id', auth()->id())
        //         ->where('platform_id', $platform->id)
        //         ->update(
        //             [
        //                 'platform_order' => $platform->order
        //             ]
        //         );
        // }

        // return response()->json(['message' => "Order swapped successfully"]);

        if (!is_array($request->orderList)) {
            return response()->json(['message' => "order list must be an array"]);
        }

        $orderList = json_decode(json_encode($request->orderList));

        $id = array_column($orderList, 'id');
        array_multisort($id, SORT_ASC, $orderList);

        foreach ($orderList as $index => $platform) {

            DB::table('user_platforms')->where('user_id', auth()->id())
                ->where('platform_id', $platform->id)
                ->update(
                    [
                        'platform_order' => $platform->order
                    ]
                );
        }

        return response()->json(['message' => "Order swapped successfully"]);
    }

    /**
     * Direct
     */
    public function direct(PlatformRequest $request)
    {
        $userPlatform = DB::table('user_platforms')
            ->where('user_id', auth()->id())
            ->where('platform_id', $request->platform_id)
            ->first();
        if (!$userPlatform) {
            return response()->json(['message' => 'Platform not found']);
        }

        try {
            DB::table('user_platforms')
                ->where('user_id', auth()->id())
                ->where('platform_id', $request->platform_id)
                ->update([
                    'direct' => $userPlatform->direct ? 0 : 1
                ]);

            if ($userPlatform->direct) {
                return response()->json(['message' => 'Plateform updated to hide successfully']);
            }
            return response()->json(['message' => 'Plateform updated to visible successfully']);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * Increment Click
     */
    public function incrementClick(IncrementRequest $request)
    {
        if ($request->user_id == auth()->id()) {
            return response()->json(['message' => 'You can not click your own platform']);
        }

        DB::table('user_platforms')
            ->where('platform_id', $request->platform_id)
            ->where('user_id', $request->user_id)
            ->increment('clicks');

        return response()->json(['message' => 'Platform clicked successfully']);
    }

    /**
     * Platform Response (private)
     */
    private function userPlatform($id)
    {
        $userPlatform = DB::table('user_platforms')
            ->select(
                'platforms.id',
                'platforms.title',
                'platforms.icon',
                'platforms.input',
                'platforms.baseUrl',
                'user_platforms.created_at',
                'user_platforms.path',
                'user_platforms.label',
                'user_platforms.direct',
            )
            ->join('platforms', 'platforms.id', 'user_platforms.platform_id')
            ->where('platform_id', $id)
            ->where('user_id', auth()->id())
            ->first();

        return $userPlatform;
    }
}
