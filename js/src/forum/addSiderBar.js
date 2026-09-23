import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import LinkButton from 'flarum/common/components/LinkButton';

export default function addSidebarNav() {
  extend(IndexSidebar.prototype, 'navItems', function (items) {
    items.add(
      'friendlink',
      <LinkButton icon="fas fa-camera-retro" href={app.route('friendlink')}>
        {app.translator.trans('nodeloc-friend-link.forum.title.page_title')}
      </LinkButton>,
      15
    );
  });
}
