<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PhoneContact\AddPhoneContactRequest;
use App\Http\Requests\Api\PhoneContact\ContactDetailsRequest;
use App\Http\Requests\Api\PhoneContact\UpdatePhoneContactRequest;
use App\Http\Resources\Api\ContactResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PhoneContactController extends Controller
{

    /**
     * All Contact List
     */
    public function index()
    {
        
        $contacts = DB::table('phone_contacts')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['total_contacts' => $contacts->count(), 'contacts' => ContactResource::collection($contacts)]);
        
    }

    /**
     * Contact Details
     */
    public function phoneContact(ContactDetailsRequest $request)
    {
        $contact = DB::table('phone_contacts')
            ->where('user_id', auth()->id())
            ->where('id', $request->contact_id)
            ->first();

        if (!$contact) {
            return response()->json(['message' => 'Phone contact details not found']);
        }

        return response()->json(['contactDetails' => new ContactResource($contact)]);
    }

    /**
     * Add Contact
     */
    public function add(AddPhoneContactRequest $request)
    {
        $photo = null;

        if ($request->hasFile('photo')) {
            $image = $request->photo;
            $imageName = time() . '.' . $image->extension();
            $image->storeAs('public/uploads/photos', $imageName);
            $photo = 'uploads/photos/' . $imageName;
        }

        DB::table('phone_contacts')->insert([
            'user_id' => auth()->id(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'photo' => $photo,
            'work_email' => $request->work_email,
            'work_phone' => $request->work_phone,
            'website' => $request->website,
            'company_name' => $request->company_name,
            'address' => $request->address
        ]);

        $data = DB::table('phone_contacts')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json(['message' => "Contact added successfully", 'contactDetails' => new ContactResource($data)]);
    }

    /**
     * Update Contact
     */
    public function update(UpdatePhoneContactRequest $request)
    {
        $contact = DB::table('phone_contacts')->where('id', $request->contact_id)->first();
        if (!$contact) {
            return response()->json(['message' => 'Phone contact not found']);
        }

        try {
            $oldPhoto = $contact->photo;
            $photo = $oldPhoto;
            if ($request->hasFile('photo')) {
                $image = $request->photo;
                $imageName = time() . '.' . $image->extension();
                $image->storeAs('public/uploads/photos', $imageName);
                $photo = 'uploads/photos/' . $imageName;
                if ($oldPhoto) {
                    if (Storage::exists('public/' . $oldPhoto)) {
                        Storage::delete('public/' . $oldPhoto);
                    }
                }
            }

            DB::table('phone_contacts')->where('id', $request->contact_id)->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'photo' => $photo,
                'work_email' => $request->work_email,
                'work_phone' => $request->work_phone,
                'website' => $request->website,
                'company_name' => $request->company_name,
                'address' => $request->address
            ]);

            $contact = DB::table('phone_contacts')->where('id', $request->contact_id)->first();
            return response()->json(['message' => "Contact updated successfully", 'contactDetails' => new ContactResource($contact)]);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()]);
        }
    }

    /**
     * Remove Contact
     */
    public function remove(ContactDetailsRequest $request)
    {
        $platform = DB::table('phone_contacts')
            ->where('user_id', auth()->id())
            ->where('id', $request->contact_id)
            ->delete();
        if (!$platform) {
            return response()->json(['message' => 'Contact not found']);
        }

        return response()->json(['message' => 'Contact removed successfully']);
    }
}
