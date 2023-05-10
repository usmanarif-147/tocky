<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Group\AddGroupRequest;
use App\Http\Requests\Api\Group\ContactInGroupRequest;
use App\Http\Requests\Api\Group\UserInGroupRequest;
use App\Http\Requests\Api\Group\GroupDetailsRequest;
use App\Http\Requests\Api\Group\UpdateGroupRequest;
use App\Http\Resources\Api\ContactResource;
use App\Http\Resources\Api\GroupResource;
use App\Http\Resources\Api\UserResource;
use App\Models\Group;
use App\Models\User;
use App\Services\GroupService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::where('groups.user_id', auth()->id())
            ->get();

        return response()->json(['groups' => GroupResource::collection($groups)]);
    }

    public function group(GroupDetailsRequest $request)
    {
        $group = Group::where('id', $request->group_id)
            ->where('user_id', auth()->id())
            ->first();
        if (!$group) {
            return response()->json(['message' => 'Group not found']);
        }



        $groupContacts = Group::select(
            'contacts.*'
        )
            ->join('group_contacts', 'group_contacts.group_id', 'groups.id')
            ->join('phone_contacts as contacts', 'contacts.id', 'group_contacts.contact_id')
            ->where('groups.id', $request->group_id)
            ->where('groups.user_id', auth()->id())
            ->get();

        $groupMembers = Group::select(
            'members.*'
        )
            ->join('user_group', 'user_group.group_id', 'groups.id')
            ->join('users as members', 'members.id', 'user_group.user_id')
            ->where('groups.id', $request->group_id)
            ->where('groups.user_id', auth()->id())
            ->get();


        return response()->json(
            [
                'data' =>
                [
                    'group' => new GroupResource($group),
                    'group_members' => UserResource::collection($groupMembers),
                    'group_contacts' => ContactResource::collection($groupContacts)
                ]
            ]
        );
    }

    public function add(AddGroupRequest $request)
    {
        $isExist = Group::where('user_id', auth()->id())
            ->where('title', $request->title)
            ->first();
        if ($isExist) {
            return response()->json(['message' => "Group with same title is already exist"]);
        }

        $group = Group::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'icon' => $request->icon,
            'active' => $request->active ?? 0,
        ]);
        return response()->json(['message' => "Group created successfully", 'groupDetails' => new GroupResource($group)]);
    }

    public function update(UpdateGroupRequest $request)
    {
        $group = Group::where('id', $request->group_id)->where('user_id', auth()->id())->first();

        if (!$group) {
            return response()->json(['message' => "Group not found"]);
        }

        Group::where('id', $request->group_id)->where('user_id', auth()->id())->update([
            'title' => $request->title,
            'icon' => $request->icon,
            'active' => $request->active ?? 0,
        ]);

        $group = Group::where('id', $request->group_id)->where('user_id', auth()->id())->first();
        return response()->json(['message' => 'Group updated successfully', 'groupDetails' => new GroupResource($group)]);
    }

    public function destroy(GroupDetailsRequest $request)
    {
        $group = Group::where('id', $request->group_id)->where('user_id', auth()->id())->first();
        if (!$group) {
            return response()->json(['message' => "Group not found"]);
        }

        // remove all data related to group
        try {
            DB::table('group_contacts')->where('group_id', $request->group_id)->delete();
            DB::table('user_group')->where('group_id', $request->group_id)->delete();
            Group::where('user_id', auth()->id())->where('id', $request->group_id)->delete();
            return response()->json(['message' => 'Group removed successfully']);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }


    /**
     * Add user in group
     */
    public function addUser(UserInGroupRequest $request)
    {
        // check is user itself
        if (auth()->id() == $request->user_id) {
            return response()->json(['message' => 'You cannot add yourself into your own group']);
        }

        $user = User::where('id', $request->user_id)->first();
        // is user exist
        if (!$user) {
            return response()->json(['message' => 'User not found']);
        }

        // is user account activated
        if (!$user->status) {
            return response()->json(['message' => 'You cannot add user into the group because user is not activated']);
        }

        // is group belongs to the logged in user
        $group = Group::where('user_id', auth()->id())->where('id', $request->group_id)->first();
        if (!$group) {
            return response()->json(['message' => 'Group not found']);
        }

        // check user is already exist into the group
        $checkUserInGroup = DB::table('user_group')
            ->where('user_id', $request->user_id)
            ->where('group_id', $request->group_id)
            ->first();

        if ($checkUserInGroup) {
            return response()->json(['message' => 'User already exist in this group']);
        }

        // insert record into the user_groups
        try {

            DB::table('user_group')->insert([
                'user_id' => $request->user_id,
                'group_id' => $request->group_id
            ]);

            Group::where('user_id', auth()->id())->where('id', $request->group_id)->increment('total_members');
            return response()->json(['message' => 'User added into group successfully', 'userDetails' => new UserResource($user)]);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * Remove user from group
     */
    public function removeUser(UserInGroupRequest $request)
    {
        // check group belongs to the user
        $group = Group::where('user_id', auth()->id())->where('id', $request->group_id)->first();
        if (!$group) {
            return response()->json(['message' => 'Group not found']);
        }

        // check user belongs to this group
        $userInGroup = DB::table('user_group')
            ->where('user_id', $request->user_id)
            ->where('group_id', $request->group_id)
            ->first();
        if (!$userInGroup) {
            return response()->json(['message' => 'You cannot delete this user because user not found']);
        }

        // remoce user from group
        try {
            DB::table('user_group')
                ->where('user_id', $request->user_id)
                ->where('group_id', $request->group_id)
                ->delete();

            Group::where('user_id', auth()->id())->where('id', $request->group_id)->decrement('total_members');
            return response()->json(['message' => 'User removed from group successfully']);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * Add contact in group
     */
    public function addContact(ContactInGroupRequest $request)
    {
        $contact = DB::table('phone_contacts')->where('id', $request->contact_id)->first();
        // is contact exist
        if (!$contact) {
            return response()->json(['message' => 'Contact not found']);
        }

        // is group belongs to the logged in user
        $group = Group::where('user_id', auth()->id())->where('id', $request->group_id)->first();
        if (!$group) {
            return response()->json(['message' => 'Group not found']);
        }

        // check contact is already exist into the group
        $checkContactInGroup = DB::table('group_contacts')
            ->where('contact_id', $request->contact_id)
            ->where('group_id', $request->group_id)
            ->first();

        if ($checkContactInGroup) {
            return response()->json(['message' => 'Contact already exist in this group']);
        }

        // insert record into the group_contacts
        try {
            DB::table('group_contacts')->insert([
                'contact_id' => $request->contact_id,
                'group_id' => $request->group_id
            ]);

            Group::where('user_id', auth()->id())->where('id', $request->group_id)->increment('total_contacts');
            return response()->json(['message' => 'Contact added into group successfully', 'contactDetails' => new ContactResource($contact)]);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * Remove contact from group
     */
    public function removeContact(Request $request)
    {
        // check group belongs to the user
        $group = Group::where('user_id', auth()->id())->where('id', $request->group_id)->first();
        if (!$group) {
            return response()->json(['message' => 'Group not found']);
        }

        // check contact belongs to this group
        $contactInGroup = DB::table('group_contacts')
            ->where('contact_id', $request->contact_id)
            ->where('group_id', $request->group_id)
            ->first();
        if (!$contactInGroup) {
            return response()->json(['message' => 'You cannot delete this contcat because contact not found']);
        }

        // remove contact from group
        try {
            DB::table('group_contacts')
                ->where('contact_id', $request->contact_id)
                ->where('group_id', $request->group_id)
                ->delete();

            Group::where('user_id', auth()->id())->where('id', $request->group_id)->decrement('total_contacts');
            return response()->json(['message' => 'Contact removed from group successfully']);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }
}
