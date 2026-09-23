import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';

export default class HideModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);
    this.showId = vnode.attrs.show_id;
    this.state = vnode.attrs.state;
  }

  title() {
    return app.translator.trans('nodeloc-friend-link.forum.modal.hide_title');
  }

  className() {
    return 'FriendLinkActionModal Modal--small';
  }

  content() {
    return (
      <div className="Modal-footer">
        <Button className="Button Button--primary" loading={this.loading} onclick={() => this.hideLink()}>
          {app.translator.trans('nodeloc-friend-link.forum.modal.confirm_button')}
        </Button>
        <Button className="Button" disabled={this.loading} onclick={() => this.hide()}>
          {app.translator.trans('nodeloc-friend-link.forum.modal.cancel_button')}
        </Button>
      </div>
    );
  }

  hideLink() {
    if (this.loading) return;

    this.loading = true;

    app
      .request({
        method: 'POST',
        url: `${app.forum.attribute('apiUrl')}/nodeloc/friend_link/hide`,
        body: { show_id: this.showId },
      })
      .then(() => {
        app.alerts.show({ type: 'success' }, app.translator.trans('nodeloc-friend-link.forum.alerts.hide_success'));
        this.state?.refresh();
        this.hide();
      })
      .finally(() => this.loaded());
  }
}
