<?php

namespace App\Actions\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttachMediaToModelAction
{
    /**
     * Link uploaded temporary media records polymorphically to a target model.
     *
     * @param  array<int|string>  $mediaIdsOrUuids
     */
    public function execute(User $user, Model $model, array $mediaIdsOrUuids, string $collection): void
    {
        if (empty($mediaIdsOrUuids)) {
            return;
        }

        $mediaIds = [];
        $mediaUuids = [];

        foreach ($mediaIdsOrUuids as $identifier) {
            if (is_int($identifier) || (is_string($identifier) && ctype_digit($identifier))) {
                $mediaIds[] = (int) $identifier;

                continue;
            }

            if (is_string($identifier) && Str::isUuid($identifier)) {
                $mediaUuids[] = $identifier;
            }
        }

        if (empty($mediaIds) && empty($mediaUuids)) {
            throw ValidationException::withMessages([
                'media' => ['Một hoặc nhiều media không hợp lệ hoặc đã được sử dụng.'],
            ]);
        }

        $mediaItems = Media::query()
            ->where(function ($query) use ($mediaIds, $mediaUuids) {
                if (! empty($mediaIds)) {
                    $query->whereIn('id', array_unique($mediaIds));
                }

                if (! empty($mediaUuids)) {
                    $method = empty($mediaIds) ? 'whereIn' : 'orWhereIn';
                    $query->{$method}('uuid', array_unique($mediaUuids));
                }
            })
            ->where('collection', $collection)
            ->whereIn('status', ['temporary', 'processing'])
            ->get();

        if ($mediaItems->count() !== count(array_unique($mediaIdsOrUuids))) {
            throw ValidationException::withMessages([
                'media' => ['Một hoặc nhiều media không hợp lệ hoặc đã được sử dụng.'],
            ]);
        }

        foreach ($mediaItems as $media) {
            if ((int) $media->user_id !== (int) $user->id) {
                throw ValidationException::withMessages([
                    'media' => ['Bạn không có quyền gắn media này.'],
                ]);
            }

            // Update relation and status
            $media->update([
                'mediable_type' => $model->getMorphClass(),
                'mediable_id' => $model->getKey(),
                'status' => 'ready', // Ensure status is promoted
            ]);
        }
    }
}
