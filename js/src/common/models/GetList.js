import Model from 'flarum/common/Model';

export default class GetList extends Model {
  img_list() {
    return Model.attribute('img_list').call(this) || [];
  }

  created_time() {
    return Model.attribute('created_time').call(this) || 0;
  }

  user() {
    return Model.hasOne('user').call(this);
  }

  uid() {
    return Model.attribute('uid').call(this);
  }

  width() {
    return Model.attribute('cover_width').call(this);
  }

  height() {
    return Model.attribute('cover_height').call(this);
  }

  like_count() {
    return Model.attribute('like_count').call(this) || 0;
  }

  view_count() {
    return Model.attribute('view_count').call(this) || 0;
  }

  exchange_count() {
    return Model.attribute('exchange_count').call(this) || 0;
  }

  is_my_like() {
    return !!Model.attribute('is_my_like').call(this);
  }

  status() {
    return Model.attribute('status').call(this) || 0;
  }

  sitename() {
    return Model.attribute('sitename').call(this) || '';
  }

  siteurl() {
    return Model.attribute('siteurl').call(this) || '';
  }

  sitelogourl() {
    return Model.attribute('sitelogourl').call(this) || '';
  }

  apiEndpoint() {
    return '/friend_link_list' + (this.exists ? '/' + this.id() : '');
  }
}
