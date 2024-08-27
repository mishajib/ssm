<?php

namespace App\Actions\User;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

final class CreateOrUpdateUser
{
    public function execute(array $data, ?int $id = null): User
    {
        DB::beginTransaction();
        try {
            $user = User::updateOrCreate(
                ['id' => $id],
                $data
            );
            DB::commit();
            $user->refresh();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}