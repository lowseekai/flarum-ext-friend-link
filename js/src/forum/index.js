import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import addSidebarNav from './addSiderBar';
import IndexShowPage from './components/IndexShowPage';
import FriendLinkListState from './states/FriendLinkListState';
import GetList from '../common/models/GetList';
import LikeNotification from './notification/LikeNotification';

app.initializers.add('nodeloc/flarum-ext-friend-link', () => {
  app.routes.friendlink = {
    path: '/friendlink',
    component: IndexShowPage,
  };

  app.notificationComponents.friendLinkLiked = LikeNotification;
  app.store.models.friendLinkList = GetList;
  app.friendLinkListState = new FriendLinkListState();

  addSidebarNav();

  // Flarum 2 registers NotificationGrid through the frontend registry.
  extend('flarum/forum/components/NotificationGrid', 'notificationTypes', (items) => {
    items.add('friendLinkLiked', {
      name: 'friendLinkLiked',
      icon: 'fas fa-camera-retro',
      label: app.translator.trans('nodeloc-friend-link.forum.notification.label'),
    });
  });
});
