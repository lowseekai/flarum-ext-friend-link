import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Dropdown from 'flarum/common/components/Dropdown';
import Button from 'flarum/common/components/Button';

export default class FilterMenuItem extends Component {
  view() {
    const options = [
      ['recent', '-created_time'],
      ['score', '-like_count'],
    ];
    const selected = this.attrs.state.getSort() === '-like_count' ? 'score' : 'recent';

    return (
      <Dropdown buttonClassName="Button" label={app.translator.trans(`nodeloc-friend-link.forum.filter.${selected}_label`)}>
        {options.map(([label, sort]) => (
          <Button
            icon={selected === label ? 'fas fa-check' : true}
            active={selected === label}
            onclick={() => {
              const state = this.attrs.state;
              state.refreshParams({ ...state.getParams(), sort }, 1);
            }}
          >
            {app.translator.trans(`nodeloc-friend-link.forum.filter.${label}_label`)}
          </Button>
        ))}
      </Dropdown>
    );
  }
}
