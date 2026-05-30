<?php

namespace App\Actions\Messaging;

use App\Actions\Reports\CreateReport;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ReportMessage
{
    public function __construct(
        protected CreateReport $createReport
    ) {}

    /**
     * Report a message inside a conversation.
     *
     * @param  array{reason: string, description?: string|null}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Message $message, array $data): Report
    {
        return $this->createReport->execute($user, $message, $data);
    }
}
