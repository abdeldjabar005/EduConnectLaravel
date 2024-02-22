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
    public function update(Request $request, User $user)
    {
        $request->validate([
            'profile_picture' => 'sometimes|nullable|image|max:2048',
            'bio' => 'sometimes|nullable|string',
            'contact_information' => 'sometimes|nullable|string',
        ]);

        $data = $request->only('bio', 'contact_information');

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $data['profile_picture'] = $path;
        }

        $user->update($data);

        return response(new UserResource($user), 201);
    }
}
