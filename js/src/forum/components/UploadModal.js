import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';

export default class UploadModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);

    this.state = vnode.attrs.state;
    this.siteName = '';
    this.siteUrl = '';
    this.file = null;
    this.previewUrl = null;
  }

  title() {
    return app.translator.trans('nodeloc-friend-link.forum.title.share_my_site');
  }

  className() {
    return 'Modal--small FriendLinkUploadModal';
  }

  onremove(vnode) {
    super.onremove(vnode);

    if (this.previewUrl) {
      URL.revokeObjectURL(this.previewUrl);
    }
  }

  content() {
    return (
      <>
        <div className="Modal-body">
          <p className="helpText">{app.translator.trans('nodeloc-friend-link.forum.tips.upload_tips')}</p>
          <div className="Form-group">
            <label>{app.translator.trans('nodeloc-friend-link.forum.modal.site_name')}</label>
            <input
              className="FormControl"
              type="text"
              value={this.siteName}
              oninput={(event) => {
                this.siteName = event.target.value;
              }}
            />
          </div>
          <div className="Form-group">
            <label>{app.translator.trans('nodeloc-friend-link.forum.modal.site_url')}</label>
            <input
              className="FormControl"
              type="url"
              value={this.siteUrl}
              oninput={(event) => {
                this.siteUrl = event.target.value;
              }}
            />
          </div>
          <div className="Form-group">
            <label>{app.translator.trans('nodeloc-friend-link.forum.modal.logo')}</label>
            <input
              className="FormControl"
              type="file"
              accept="image/jpeg,image/png,image/bmp,image/gif"
              onchange={(event) => this.selectFile(event)}
            />
          </div>
          {this.previewUrl && (
            <div className="FriendLinkUploadModal-preview">
              <img src={this.previewUrl} alt={app.translator.trans('nodeloc-friend-link.forum.modal.logo')} />
            </div>
          )}
        </div>
        <div className="Modal-footer">
          <Button className="Button Button--primary" loading={this.loading} onclick={() => this.submit()}>
            {app.translator.trans('nodeloc-friend-link.forum.modal.submit_button')}
          </Button>
          <Button className="Button" disabled={this.loading} onclick={() => this.hide()}>
            {app.translator.trans('nodeloc-friend-link.forum.modal.cancel_button')}
          </Button>
        </div>
      </>
    );
  }

  selectFile(event) {
    const file = event.target.files?.[0];

    if (!file) {
      return;
    }

    if (this.previewUrl) {
      URL.revokeObjectURL(this.previewUrl);
    }

    this.file = file;
    this.previewUrl = URL.createObjectURL(file);
    m.redraw();
  }

  submit() {
    if (this.loading) return;

    if (!this.siteName.trim() || !this.siteUrl.trim() || !this.file) {
      app.alerts.show({ type: 'error' }, app.translator.trans('nodeloc-friend-link.forum.alerts.required_fields'));
      return;
    }

    const body = new FormData();
    body.append('sitelogo', this.file);
    body.append('sitename', this.siteName.trim());
    body.append('siteurl', this.siteUrl.trim());

    this.loading = true;

    app
      .request({
        method: 'POST',
        url: `${app.forum.attribute('apiUrl')}/nodeloc/friend_link/add`,
        serialize: (raw) => raw,
        body,
      })
      .then(() => {
        app.alerts.show({ type: 'success' }, app.translator.trans('nodeloc-friend-link.forum.alerts.submit_success'));
        this.state?.refresh();
        this.hide();
      })
      .finally(() => this.loaded());
  }
}
