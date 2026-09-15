<?php

namespace Nodeloc\FriendLink\Logic;

use Flarum\Foundation\ValidationException;
use Nodeloc\FriendLink\Model\FriendLink;

class HideLogic
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

        if ((int) $friendLink->user_id !== (int) $actor->id && ! $actor->isAdmin()) {
            throw new ValidationException(['msg' => '您只能隐藏自己分享的链接。']);
        }

        $friendLink->update([
            'status' => 2,
            'update_time' => time(),
        ]);

        return ['status' => true, 'msg' => '友情链接已隐藏。'];
    }
}
