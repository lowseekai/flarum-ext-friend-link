<?php

namespace Nodeloc\FriendLink\Api\Resource;

use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Illuminate\Database\Eloquent\Builder;
use Nodeloc\FriendLink\Model\FriendLink;

/**
 * @extends AbstractDatabaseResource<FriendLink>
 */
class FriendLinkResource extends AbstractDatabaseResource
{
    public function type(): string
    {
        return 'friendLinkList';
    }

    public function model(): string
    {
        return FriendLink::class;
    }

    public function scope(Builder $query, \Tobyz\JsonApiServer\Context $context): void
    {
        $query->where('status', 1);
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Show::make()
                ->defaultInclude(['user'])
                ->eagerLoad(['user']),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Arr::make('img_list')
                ->get(fn (FriendLink $link) => array_values(array_filter(explode(',', (string) $link->img_list)))),
            Schema\Integer::make('uid')->property('user_id'),
            Schema\Integer::make('cover_width'),
            Schema\Integer::make('cover_height'),
            Schema\Integer::make('like_count'),
            Schema\Integer::make('view_count'),
            Schema\Integer::make('exchange_count'),
            Schema\Integer::make('status'),
            Schema\Integer::make('created_time'),
            Schema\Str::make('sitename'),
            Schema\Str::make('siteurl'),
            Schema\Str::make('sitelogourl'),
            Schema\Relationship\ToOne::make('user')
                ->type('users')
                ->includable(),
        ];
    }
}
