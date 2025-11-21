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
		// @ts-expect-error types are not available
		(select) => select('core/editor').getCurrentPostType(),
		[]
	) as string;

	if (!postType) {
		return [[], () => {}] as [
			PermalinkHistory,
			(history: PermalinkHistory) => void
		];
	}

	const postTypeObject = useSelect(
		// @ts-expect-error types are not available
		(select) => (postType ? select("core").getPostType(postType) : null),
		[postType]
	);

	if (!postTypeObject || !postTypeObject.viewable) {
		return [[], () => {}] as [
			PermalinkHistory,
			(history: PermalinkHistory) => void
		];
	}

	// @ts-expect-error types are not available
	return useEntityProp('postType', postType, 'permalink_history') as EntityPropPermalinkHistory;
}
