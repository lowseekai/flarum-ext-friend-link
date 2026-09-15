<?php

namespace Nodeloc\FriendLink\Controllers;

use Flarum\Http\RequestUtil;
use Laminas\Diactoros\Response\JsonResponse;
use Nodeloc\FriendLink\Model\FriendLink;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetListController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $query = FriendLink::query()->with('user');

        if ($actor->isAdmin()) {
            $query->whereIn('status', [1, 2]);
        } else {
            $query->where(function ($query) use ($actor) {
                $query->where('status', 1);

                if ($actor->exists) {
                    $query->orWhere(function ($query) use ($actor) {
                        $query->where('user_id', $actor->id)->where('status', 2);
                    });
                }
            });
        }

        $sort = $request->getQueryParams()['sort'] ?? '-created_time';
        $sort = is_string($sort) ? $sort : '-created_time';
        $sortColumn = ltrim($sort, '-');
        $sortDirection = str_starts_with($sort, '-') ? 'desc' : 'asc';

        if (! in_array($sortColumn, ['created_time', 'like_count'], true)) {
            $sortColumn = 'created_time';
            $sortDirection = 'desc';
        }

        $page = $request->getQueryParams()['page'] ?? [];
        $limit = max(1, min((int) ($page['limit'] ?? 12), 100));
        $offset = max(0, (int) ($page['offset'] ?? 0));
        $total = (clone $query)->count();

        $links = $query
            ->orderBy($sortColumn, $sortDirection)
            ->orderBy('id', $sortDirection)
            ->offset($offset)
            ->limit($limit)
            ->get();

        $data = [];
        $included = [];
        $includedUserIds = [];

        foreach ($links as $link) {
            $user = $link->user;
            $isMyLike = $actor->exists && $link->actions()
                ->where('user_id', $actor->id)
                ->where('type', 0)
                ->exists();

            $data[] = [
                'type' => 'friendLinkList',
                'id' => (string) $link->id,
                'attributes' => [
                    'img_list' => array_values(array_filter(explode(',', (string) $link->img_list))),
                    'uid' => (int) $link->user_id,
                    'cover_width' => (int) ($link->cover_width ?? 0),
                    'cover_height' => (int) ($link->cover_height ?? 0),
                    'like_count' => (int) ($link->like_count ?? 0),
                    'view_count' => (int) ($link->view_count ?? 0),
                    'exchange_count' => (int) ($link->exchange_count ?? 0),
                    'is_my_like' => $isMyLike,
                    'status' => (int) $link->status,
                    'created_time' => (int) $link->created_time,
                    'sitename' => (string) $link->sitename,
                    'siteurl' => (string) $link->siteurl,
                    'sitelogourl' => (string) $link->sitelogourl,
                ],
                'relationships' => [
                    'user' => [
                        'data' => $user ? [
                            'type' => 'users',
                            'id' => (string) $user->id,
                        ] : null,
                    ],
                ],
            ];

            if ($user && ! isset($includedUserIds[$user->id])) {
                $includedUserIds[$user->id] = true;
                $included[] = [
                    'type' => 'users',
                    'id' => (string) $user->id,
                    'attributes' => [
                        'username' => (string) $user->username,
                        'displayName' => (string) ($user->display_name ?: $user->username),
                        'avatarUrl' => $user->avatar_url,
                        'avatarSrcset' => $user->avatar_url_2x,
                        'hasUploadedAvatar' => (bool) $user->avatar_url,
                    ],
                ];
            }
        }

        $nextOffset = $offset + $limit;
        $prevOffset = max(0, $offset - $limit);

        return new JsonResponse([
            'data' => $data,
            'included' => $included,
            'meta' => [
                'page' => [
                    'offset' => $offset,
                    'limit' => $limit,
                    'total' => $total,
                ],
            ],
            'links' => [
                'next' => $nextOffset < $total ? $this->pageUrl($request, $nextOffset, $limit, $sort) : null,
                'prev' => $offset > 0 ? $this->pageUrl($request, $prevOffset, $limit, $sort) : null,
            ],
        ], 200, ['Content-Type' => 'application/vnd.api+json']);
    }

    private function pageUrl(ServerRequestInterface $request, int $offset, int $limit, string $sort): string
    {
        $query = $request->getQueryParams();
        $query['page']['offset'] = $offset;
        $query['page']['limit'] = $limit;
        $query['sort'] = $sort;

        return (string) $request->getUri()->withQuery(http_build_query($query));
    }
}
