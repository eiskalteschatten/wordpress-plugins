import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { useEffect, useRef } from 'react';

/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

import './web-components';
import ImageCarousel from './image-carousel';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { images } = attributes;
	const carouselRef = useRef(null);

	const onSelectImages = (newImages) => {
		setAttributes({ images: newImages });
	};

	useEffect(() => {
		if (carouselRef.current) {
			new ImageCarousel(carouselRef.current);
		}
	}, [images]);

	return (
		<div { ...useBlockProps() }>
			{images.length > 0 ? (
				<div className='as-image-carousel' ref={carouselRef}>
					{images.map((img) => (
						<figure>
							<img key={img.id} src={img.url} alt={img.alt} loading="lazy" />
							<figcaption>{img.caption}</figcaption>
						</figure>
					))}
				</div>
			) : (
				<p className='as-image-carousel-select-images'>
					<div className='as-image-carousel-select-images-text'>{__('Select images for the carousel:', 'as-image-carousel')}</div>
					<div>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={onSelectImages}
								allowedTypes={['image']}
								multiple
								gallery
								value={images.map((img) => img.id)}
								render={({ open }) => (
									<Button onClick={open} variant='primary'>
										Select Images
									</Button>
								)}
							/>
						</MediaUploadCheck>
					</div>
				</p>
			)}
		</div>
	);
	// return (
	// 	<p { ...useBlockProps() }>
	// 		{ __(
	// 			'Image Carousel – hello from the editor!',
	// 			'as-image-carousel'
	// 		) }
	// 	</p>
	// );
}
