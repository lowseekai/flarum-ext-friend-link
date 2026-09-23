<?php

namespace Nodeloc\FriendLink\Logic;

use Flarum\Foundation\ValidationException;
use Flarum\Notification\NotificationSyncer;
use Flarum\User\User;
use Nodeloc\FriendLink\Model\FriendLink;
use Nodeloc\FriendLink\Model\FriendLinkAction;
use Nodeloc\FriendLink\Notification\LikedNotification;

class LikeLogic
{
    public function save($actor, $data): array
    {
        $showId = (int) ($data['show_id'] ?? 0);

        if (! $showId) {
            throw new ValidationException(['msg' => '请选择有效的友情链接。']);
        }

        $friendLink = FriendLink::find($showId);

        if (! $friendLink) {
            throw new ValidationException(['msg' => '友情链接不存在。']);
        }

        if ((int) $friendLink->user_id === (int) $actor->id) {
            throw new ValidationException(['msg' => '不能给自己分享的网站点赞。']);
        }

        $alreadyLiked = FriendLinkAction::where([
            'friend_link_id' => $showId,
            'user_id' => $actor->id,
            'type' => 0,
        ])->exists();

        if ($alreadyLiked) {
            throw new ValidationException(['msg' => '您已经点过赞了。']);
        }

        FriendLinkAction::create([
            'friend_link_id' => $showId,
            'user_id' => $actor->id,
            'type' => 0,
            'created_time' => time(),
        ]);

        $friendLink->increment('like_count');

        $owner = User::find($friendLink->user_id);

        if ($owner) {
            resolve(NotificationSyncer::class)->sync(
                new LikedNotification($actor, $friendLink),
                [$owner]
            );
        }

        return ['status' => true, 'msg' => '点赞成功。'];
    }
}
