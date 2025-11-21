import {useSelect} from "@wordpress/data";
import {useEntityProp} from "@wordpress/core-data";

type PermalinkHistory = {
	id: number
	permalink: string
	remove?: "true"
}[]

type EntityPropPermalinkHistory = [
	PermalinkHistory,
	(history: PermalinkHistory) => void
]

export default function useHistory() {

	const postType = useSelect(
		// @ts-expect-error types are nicht verfügbar
		(select) => select('core/editor').getCurrentPostType(),
		[]
	) as string | undefined;

	const postTypeObject = useSelect(
		// @ts-expect-error types sind nicht verfügbar
		(select) => (postType ? select('core').getPostType(postType) : null),
		[postType]
	);

	const [history, setHistory] = (useEntityProp(
		'postType',
		postType || 'post',
		'permalink_history'
	) as unknown) as EntityPropPermalinkHistory;

	if (!postType || !postTypeObject?.viewable) {
		return [[], () => {}] as EntityPropPermalinkHistory;
	}

	return [history, setHistory];
}
