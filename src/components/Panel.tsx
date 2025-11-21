import {PluginDocumentSettingPanel} from '@wordpress/editor';
import useHistory from "../hooks/useHistory";
import {CheckboxControl} from "@wordpress/components";

export default function PermalinkHistoryPanel() {
	const [history, setHistory] = useHistory();
	console.log('history', history);
	if (history.length === 0) {
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
			{history.map(item => {
				return (
					<CheckboxControl
						key={item.id}
						label={item.permalink}
						checked={item.remove != "true"}
						onChange={() => {
							setHistory(history.map(it => {
								const copy = {...it}
								if (it.id == item.id) {
									if (item.remove == "true") {
										delete copy.remove;
									} else {
										copy.remove = "true"
									}
								}
								return copy;
							}))
						}}
					/>
				)
			})}
			<p className="description" style={{marginTop: 22}}>Unchecked items will be permanently deleted after
				saving.</p>
		</PluginDocumentSettingPanel>
	)
}
