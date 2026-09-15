<?php

namespace Nodeloc\FriendLink;

use Flarum\Extend;
use Flarum\User\User;
use Nodeloc\FriendLink\Api\Resource\FriendLinkResource;
use Nodeloc\FriendLink\Model\FriendLink;
use Nodeloc\FriendLink\Notification\LikedNotification;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less')
        ->route('/friendlink', 'friendlink', Controllers\IndexController::class),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),
    new Extend\Locales(__DIR__.'/locale'),

    // Register the resource so Flarum 2 can serialize friend-link notifications.
    new Extend\ApiResource(FriendLinkResource::class),

    (new Extend\Model(User::class))
        ->relationship('friendLinkList', function ($user) {
            return $user->hasOne(FriendLink::class, 'user_id');
        }),

    (new Extend\Routes('api'))
        ->get('/friend_link_list', 'FriendLink.list', Controllers\GetListController::class)
        ->post('/nodeloc/friend_link/add', 'FriendLink.create', Controllers\AddController::class)
        ->post('/nodeloc/friend_link/hide', 'FriendLink.hide', Controllers\HideController::class)
        ->post('/nodeloc/friend_link/delete', 'FriendLink.delete', Controllers\DeleteController::class)
        ->post('/nodeloc/friend_link/approve', 'FriendLink.approve', Controllers\ApproveController::class)
        ->post('/nodeloc/friend_link/like', 'FriendLink.like', Controllers\LikeController::class)
        ->post('/nodeloc/friend_link/view', 'FriendLink.view', Controllers\ViewAddController::class),

    (new Extend\Notification())
        ->type(LikedNotification::class, ['alert']),
];
