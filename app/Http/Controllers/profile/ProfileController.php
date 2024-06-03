<?php

namespace App\Http\Controllers\profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

/**
     * @group Profile
     *
     * APIs for managing profile
     * */
class ProfileController extends Controller
{
   public function update(Request $request)
{
    $user = auth()->user();

    $request->validate([
        'first_name' => 'sometimes|nullable|string|max:255',
        'last_name' => 'sometimes|nullable|string|max:255',
        'profile_picture' => 'sometimes|nullable|image|max:20048',
        'bio' => 'sometimes|nullable|string',
        'contact_information' => 'sometimes|nullable|string',
    ]);

    $data = $request->only('first_name', 'last_name', 'bio', 'contact_information');

    if ($request->hasFile('profile_picture')) {
        $path = $request->file('profile_picture')->store('users-avatar', 'public');
        $data['profile_picture'] = $path;
    }
    $user->update($data);
    return response(new UserResource($user), 201);
}
    public function updateProfilePicture(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'profile_picture' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('users-avatar', 'public');
            $user->update(['profile_picture' => $path]);
        }

        return response(new UserResource($user), 201);
    }
}
