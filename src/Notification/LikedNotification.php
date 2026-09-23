<?php

namespace Nodeloc\FriendLink\Notification;

use Flarum\Database\AbstractModel;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\User\User;
use Nodeloc\FriendLink\Model\FriendLink;

class LikedNotification implements BlueprintInterface
{
    public function __construct(
        protected User $actor,
        protected FriendLink $show
    ) {
    }

    public function getFromUser(): ?User
    {
        return $this->actor;
    }

    public function getSubject(): ?AbstractModel
    {
        return $this->show;
    }

    public function getData(): mixed
    {
        return null;
    }

    public static function getType(): string
    {
        return 'friendLinkLiked';
    }

    public static function getSubjectModel(): string
    {
        return FriendLink::class;
    }
}
