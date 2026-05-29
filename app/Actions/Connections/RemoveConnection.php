<?php

namespace App\Actions\Connections;

use App\Enums\ConnectionStatus;
use App\Models\Connection;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class RemoveConnection
{
    /**
     * Remove an existing connection.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Connection $connection): Connection
    {
        Gate::forUser($user)->authorize('remove', $connection);

        $connection->update([
            'status' => ConnectionStatus::REMOVED,
            'disconnected_at' => now(),
        ]);

        $connection->delete(); // Soft delete

        return $connection;
    }
}
