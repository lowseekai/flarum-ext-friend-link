import PaginatedListState from 'flarum/common/states/PaginatedListState';

export default class FriendLinkListState extends PaginatedListState {
  constructor(params, page = 1) {
    super(params, page, 12);
  }

  get type() {
    return 'friendLinkList';
  }
}
