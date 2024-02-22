<?php

namespace Database\Factories\Helpers;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FactoryHelper
{
    /**
     * This function will get a random model id from the database.
     * @param string | HasFactory $model
     */
    public static function getRandomModelId(string $model)
    {
        // get model count

        $count = $model::query()->count();

        if($count === 0){
            // if model count is 0
            // we should create a new record and retrieve the record id
            return $model::factory()->create()->id;
        }else{
            // generate random number between 1 and model count
            return rand(1, $count);
        }
    }
    public static function getRandomUserIdByRole(string $role)
    {
        // Query the User model and filter the results to only include users with the specified role
        $query = User::query()->where('role', $role);

        // Count the number of these users
        $count = $query->count();

        if($count === 0){
            // If the count is 0, create a new user with the specified role and return the id
            return User::factory()->state(['role' => $role])->create()->id;
        }else{
            // If the count is not 0, generate a random number between 1 and the count
            $randomIndex = rand(1, $count);

            // Return the id of the user at the index corresponding to the random number
            return $query->skip($randomIndex - 1)->first()->id;
        }
    }
}
