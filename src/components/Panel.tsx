import React from 'react';
import {PluginDocumentSettingPanel} from '@wordpress/editor';
import useHistory from '../hooks/useHistory';
import {CheckboxControl} from '@wordpress/components';

export default function PermalinkHistoryPanel() {
	const [ history, setHistory ] = useHistory();

	// legacy support: if history is not an array but an object with numeric keys, convert it to an array
	const historyArray = Array.isArray( history ) ? history : Object.values( history );

	if ( 0 === historyArray.length ) {
		return <PluginDocumentSettingPanel
			name="permalink-history"
			title="Permalink History"
		><p>No permalink history available for this content.</p></PluginDocumentSettingPanel>;
	}
	return (
		<PluginDocumentSettingPanel
			name="permalink-history"
			title="Permalink History"
		>
			<p>These links were previously used for this content:</p>
			{historyArray.map( item => {
				return (
					<CheckboxControl
						key={item.id}
						label={item.permalink}
						checked={'true' != item.remove}
						onChange={() => {
							setHistory( historyArray.map( it => {
								const copy = {...it};
								if ( it.id == item.id ) {
									if ( 'true' == item.remove ) {
										delete copy.remove;
									} else {
										copy.remove = 'true';
									}
								}
								return copy;
							}) );
						}}
					/>
				);
			})}
			<p className="description" style={{marginTop: 22}}>Unchecked items will be permanently deleted after
				saving.</p>
		</PluginDocumentSettingPanel>
	);
}
