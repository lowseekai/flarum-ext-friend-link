import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import LinkButton from 'flarum/common/components/LinkButton';

export default function addSidebarNav() {
  extend(IndexPage.prototype, 'navItems', function (items) {
    items.add(
      'friendlink',
      <LinkButton icon="fas fa-camera-retro" href={
        app.route('friendlink')
      }>
        {app.translator.trans(`nodeloc-friend-link.forum.title.page_title`)}
      </LinkButton>,
      15
    );

    return items;
  });
}
