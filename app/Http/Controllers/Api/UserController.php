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
        
        if($user->private) {
            User::where('id', $user->id)
            ->update(
                [
                    'private' => 0
                ]
            );
            
            $user = User::find(auth()->id());
            return response()->json(['message' => "Profile is set to public" , 'profile' => new ProfileResource($user)]);
        }
        
        User::where('id', auth()->id())
            ->update(
                [
                    'private' => 1
                ]
            );
        $user = User::find(auth()->id());
        return response()->json(['message' => "Profile is set to private", 'profile' => new ProfileResource($user)]);
        
        
        // User::where('id', auth()->id())->update(
        //     [
        //         'private' => auth()->user()->private ? 1 : 0
        //     ]
        // );

        // $status = 'Public';
        // if (auth()->user()->private) {
        //     $status = 'Private';
        // }

        // return response()->json(
        //     [
        //         'message' => "Profile is set to " . $status, 
        //         'profile' => new ProfileResource(auth()->user()),
        // ]
        // );
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

    // public function search()
    // {
    //     $q = $_GET['q'] ?? '';
    //     $res['connected'] = $this->custom->searchUsers($q);
    //     $res['featured'] = $this->db->table('users')->where('featured', 1)->get();
    //     $this->response->success("Searched Profiles", $res);
    // }


    // public function searchUsers($key, $id = LOGGED_USER)
    // {
    //     $AND = '';
    //     if (!empty($key)) {
    //         $AND = " AND ( name LIKE ? OR username LIKE ? ) ";
    //         // LEFT JOIN connects on connects.connected_id=users.id
    //     }
    //     $query = "SELECT users.*,connected_id,connecting_id
    // 		FROM connects
    // 		INNER JOIN users on users.id=connected_id
    // 		WHERE connecting_id=$id AND users.status=1 AND featured = 0
    // 		$AND
    // 		GROUP BY users.id
    // 	";

    //     if (!empty($key)) {
    //         $key = addslashes($key);
    //         $vals[0] = "%$key%";
    //         $vals[1] = $vals[0];
    //         $users = $this->db->getDataWithQuery($query, $vals, 'ss');
    //     } else {
    //         $users = $this->db->getDataWithQuery($query);
    //     }
    //     foreach ($users as $user) {
    //         $user->id = (int)$user->id;
    //         $user->connecting_id = (int)$user->connecting_id;
    //         $user->connected_id = (int)$user->connected_id;
    //         $user->tiks = (int)$user->tiks;
    //         $user->gender = (int)$user->gender;
    //         $user->private = (int)$user->private;
    //         $user->status = (int)$user->status;
    //         $user->featured = (int)$user->featured;
    //         $user->verified = (int)$user->verified;
    //         $user->connected = "1";
    //     }
    //     return $users;
    // }

    // public function getDataWIthQuery($query, $vals = [], $types = '')
    // {
    //     $data = array();
    //     if (empty($vals)) {
    //         $result = $this
    //             ->conn
    //             ->query($query) or die($this
    //                 ->conn
    //                 ->error);
    //         while ($row = $result->fetch_object()) $data[] = $row;
    //     } else if (is_array($vals)) {
    //         if (empty($types)) foreach ($vals as $val) $types .= $this->returnTypeOfVar($val);

    //         $this->stmt = $this->conn->prepare($query) or die($this->conn->error);
    //         $this->bind_custom_param($vals, $types);
    //         $this->stmt->execute() or die($this->stmt->error);
    //         $result = $this->stmt->get_result();
    //         while ($row = $result->fetch_object()) $data[] = $row;
    //     }
    //     $this->emptyObj();
    //     return $data;
    // }
}
