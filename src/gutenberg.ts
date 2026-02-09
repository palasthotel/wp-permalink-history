import {registerPlugin} from '@wordpress/plugins';
import PermalinkHistoryPanel from './components/Panel';

registerPlugin( 'permalink-history', {
	render: PermalinkHistoryPanel
});
