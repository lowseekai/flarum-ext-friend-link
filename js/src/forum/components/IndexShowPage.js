import app from 'flarum/forum/app';
import Page from 'flarum/common/components/Page';
import PageStructure from 'flarum/forum/components/PageStructure';
import IndexSidebar from 'flarum/forum/components/IndexSidebar';
import Button from 'flarum/common/components/Button';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import Placeholder from 'flarum/common/components/Placeholder';
import username from 'flarum/common/helpers/username';
import FilterMenuItem from './FilterMenuItem';
import UploadModal from './UploadModal';
import HideModal from './HideModal';
import ApproveModal from './ApproveModal';
import DeleteModal from './DeleteModal';

export default class IndexShowPage extends Page {
  bodyClass = 'App--index';

  openLoginModal() {
    return app.modal.show(() => import('flarum/forum/components/LogInModal'));
  }

  oninit(vnode) {
    super.oninit(vnode);

    app.setTitle(app.translator.trans('nodeloc-friend-link.forum.title.page_title'));

    app.friendLinkListState.refreshParams(
      {
        filter: {},
        sort: '-created_time',
      },
      1
    );
  }

  view() {
    const state = app.friendLinkListState;
    const isLoading = state.isInitialLoading() || state.isLoadingNext();

    return (
      <PageStructure className="IndexPage" sidebar={() => <IndexSidebar />}>
        <div className="IndexPage-toolbar">
          <ul className="IndexPage-toolbar-view">
            <li>
              <FilterMenuItem state={state} />
            </li>
          </ul>
          <ul className="IndexPage-toolbar-action">
            <li>
              <Button
                className="Button Button--icon"
                icon="fas fa-sync"
                title={app.translator.trans('nodeloc-friend-link.forum.button.refresh')}
                aria-label={app.translator.trans('nodeloc-friend-link.forum.button.refresh')}
                onclick={() => state.refresh()}
              />
            </li>
            <li>
              <Button
                className="Button Button--primary"
                icon="fas fa-plus"
                onclick={() => {
                  if (!app.session.user) {
                    this.openLoginModal();
                    return;
                  }

                  app.modal.show(UploadModal, { state });
                }}
              >
                {app.translator.trans('nodeloc-friend-link.forum.button.share_my_site')}
              </Button>
            </li>
          </ul>
        </div>

        {state.isEmpty() && !isLoading ? (
          <Placeholder text={app.translator.trans('nodeloc-friend-link.forum.empty')} />
        ) : (
          <>
            <ul className="FriendLink-SiteList" aria-busy={isLoading}>
              {state.getPages().map((page) => page.items.map((item) => this.itemView(item, state)))}
            </ul>
            <div className="SupportSearchList-loadMore friendLink-more">
              {isLoading ? (
                <LoadingIndicator />
              ) : state.hasNext() ? (
                <Button className="Button" onclick={() => state.loadNext()}>
                  {app.translator.trans('nodeloc-friend-link.forum.button.load_more')}
                </Button>
              ) : null}
            </div>
          </>
        )}
      </PageStructure>
    );
  }

  itemView(item, state) {
    const user = item.user();
    const currentUser = app.session.user;
    const isAdmin = !!currentUser?.isAdmin();
    const isOwner = !!currentUser && !!user && currentUser.id() === user.id();

    if (!isAdmin && !isOwner && !item.status()) {
      return null;
    }

    return (
      <li className="FriendLink-SiteList-item" key={item.id()} id={`card-${item.id()}`}>
        <div className="FriendLink-SiteList-logo">
          <a href={item.siteurl()} target="_blank" rel="noopener noreferrer">
            <img className="Sitelogo" loading="lazy" src={item.sitelogourl()} alt={item.sitename()} />
          </a>
        </div>
        <div className="FriendLink-SiteList-site">
          <a href={item.siteurl()} target="_blank" rel="noopener noreferrer">
            {item.sitename()}
          </a>
        </div>
        {user && (
          <div className="FriendLink-SiteList-user">
            <span className="username">
              <a href={app.route('user', { username: user.username() })}>{username(user)}</a>
            </span>
          </div>
        )}
        {isAdmin && item.status() === 2 && (
          <div className="FriendLink-SiteList-status">{app.translator.trans('nodeloc-friend-link.forum.status.pending')}</div>
        )}
        <div className="action-buttons">
          {this.likeButton(item, state)}
          {this.deleteButton(item, state)}
          {item.status() === 2 && isAdmin ? this.approveButton(item, state) : this.hideButton(item, state)}
        </div>
      </li>
    );
  }

  likeButton(item, state) {
    return (
      <Button
        className="Button like"
        icon={item.is_my_like() ? 'fas fa-thumbs-up' : 'far fa-thumbs-up'}
        aria-label={app.translator.trans('nodeloc-friend-link.forum.button.like')}
        onclick={() => {
          if (!app.session.user) {
            this.openLoginModal();
            return;
          }

          app
            .request({
              method: 'POST',
              url: `${app.forum.attribute('apiUrl')}/nodeloc/friend_link/like`,
              body: { show_id: item.id() },
            })
            .then((response) => {
              if (response.status) {
                item.pushAttributes({
                  is_my_like: true,
                  like_count: item.like_count() + 1,
                });
                m.redraw();
              }
            });
        }}
      >
        {this.likeStatus(item.like_count())}
      </Button>
    );
  }

  likeStatus(count) {
    if (count >= 1000) {
      return `${Math.floor(count / 100) / 10}k`;
    }

    return count || '';
  }

  deleteButton(item, state) {
    const currentUser = app.session.user;
    const isAllowed = currentUser && (currentUser.isAdmin() || currentUser.id() === item.uid());

    if (!isAllowed) {
      return null;
    }

    return (
      <Button
        className="Button bulk"
        icon="fas fa-trash"
        aria-label={app.translator.trans('nodeloc-friend-link.forum.button.delete')}
        onclick={() => app.modal.show(DeleteModal, { show_id: item.id(), state })}
      />
    );
  }

  approveButton(item, state) {
    if (!app.session.user?.isAdmin()) {
      return null;
    }

    return (
      <Button
        className="Button bulk"
        icon="fas fa-check"
        aria-label={app.translator.trans('nodeloc-friend-link.forum.button.approve')}
        onclick={() => app.modal.show(ApproveModal, { show_id: item.id(), state })}
      />
    );
  }

  hideButton(item, state) {
    if (!app.session.user?.isAdmin()) {
      return null;
    }

    return (
      <Button
        className="Button bulk"
        icon="fas fa-eye-slash"
        aria-label={app.translator.trans('nodeloc-friend-link.forum.button.hide')}
        onclick={() => app.modal.show(HideModal, { show_id: item.id(), state })}
      />
    );
  }
}
