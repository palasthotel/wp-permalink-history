import {PluginDocumentSettingPanel} from '@wordpress/editor';
import useHistory from "../hooks/useHistory";
import {CheckboxControl} from "@wordpress/components";

export default function PermalinkHistoryPanel() {
	const [history, setHistory] = useHistory();

	console.log(history);

	// legacy support: if history is not an array but an object with numeric keys, convert it to an array
	const historyArray = Array.isArray(history) ? history : Object.values(history);

	if (historyArray.length === 0) {
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
			{historyArray.map(item => {
				return (
					<CheckboxControl
						key={item.id}
						label={item.permalink}
						checked={item.remove != "true"}
						onChange={() => {
							setHistory(historyArray.map(it => {
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
