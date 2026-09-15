<?php

namespace Nodeloc\FriendLink\Logic;

use Flarum\Foundation\ValidationException;
use Nodeloc\FriendLink\Model\FriendLink;

class ApproveLogic
{
    public function save($actor, $data): array
    {
        $showId = (int) ($data['show_id'] ?? 0);

        if (! $showId) {
            throw new ValidationException(['msg' => '请选择有效的友情链接。']);
        }

        if (! $actor->isAdmin()) {
            throw new ValidationException(['msg' => '只有管理员可以审核友情链接。']);
        }

        $friendLink = FriendLink::find($showId);

        if (! $friendLink) {
            throw new ValidationException(['msg' => '友情链接不存在。']);
        }

        $friendLink->update([
            'status' => 1,
            'update_time' => time(),
        ]);

        return ['status' => true, 'msg' => '审核成功。'];
    }
}
