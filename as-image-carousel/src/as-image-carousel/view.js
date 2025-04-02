/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

import './web-components';
import { moveSlides } from './web-components/image-carousel-nav-button';

class ImageCarousel {
	constructor(element) {
		this.element = element;
		this.isDragging = false;
		this.startX = 0;
		this.scrollLeft = 0;
		this.firstImageWidth = 0;
		this.element.dataset.index = 0;
		this.figures = this.element.querySelectorAll('figure');

		if (!this.figures?.length) {
			throw new Error('No figures could be found in the image carousel!');
		}

		// Wait for the first image to load before resizing
		const firstImage = this.figures[0].querySelector('img');
		if (firstImage.complete) {
			this.resizeImages();
			this.updateHtml();
		}
		else {
			firstImage.addEventListener('load', () => {
				this.resizeImages();
				this.updateHtml();
			});
		}

		window.addEventListener('resize', this.resizeImages.bind(this));
		this.element.addEventListener('slideChange', this.updateDots.bind(this));
		this.element.addEventListener('mousedown', this.handleMouseDown.bind(this));
		this.element.addEventListener('mouseup', this.handleMouseUpLeave.bind(this));
		this.element.addEventListener('mouseleave', this.handleMouseUpLeave.bind(this));
		this.element.addEventListener('mousemove', this.handleMouseMove.bind(this));
		this.element.addEventListener('touchstart', this.handleTouchStart.bind(this));
		this.element.addEventListener('touchmove', this.handleTouchMove.bind(this));
		this.element.addEventListener('touchend', this.handleTouchEnd.bind(this));
	}

	destroy() {
		window.removeEventListener('resize', this.resizeImages.bind(this));
		this.element.removeEventListener('slideChange', this.updateDots.bind(this));
		this.element.removeEventListener('mousedown', this.handleMouseDown.bind(this));
		this.element.removeEventListener('mouseup', this.handleMouseUpLeave.bind(this));
		this.element.removeEventListener('mouseleave', this.handleMouseUpLeave.bind(this));
		this.element.removeEventListener('mousemove', this.handleMouseMove.bind(this));
		this.element.removeEventListener('touchstart', this.handleTouchStart.bind(this));
		this.element.removeEventListener('touchmove', this.handleTouchMove.bind(this));
		this.element.removeEventListener('touchend', this.handleTouchEnd.bind(this));
	}

	resizeImages() {
		const firstImage = this.figures[0].querySelector('img');
		const imageHeight = firstImage.clientHeight;
		this.firstImageWidth = firstImage.clientWidth;

		if (imageHeight === 0) {
			throw new Error('The image height is 0!');
		}

		for (const figure of Array.from(this.figures)) {
			const image = figure.querySelector('img');
			image.style.height = `${imageHeight}px`;
			image.style.width = '100%';
		}
	}

	updateHtml() {
		this.element.innerHTML = `
			<image-carousel-lightbox-button></image-carousel-lightbox-button>
			<image-carousel-nav-button data-variant="prev"></image-carousel-nav-button>
			<div class="carousel-images-scroller">
				<div class="carousel-images">
					${Array.from(this.figures).map((figure) => figure.outerHTML).join('')}
				</div>
			</div>
			<image-carousel-nav-button data-variant="next"></image-carousel-nav-button>
			<div class="carousel-dots">
				${this.getDots()}
			</div>
		`;
	}

	handleMouseDown(event) {
		event.preventDefault();
		const scroller = this.element.querySelector('.carousel-images-scroller');
		scroller.style.cursor = 'grabbing';
		this.isDragging = true;
		this.startX = event.pageX - this.element.offsetLeft;
		this.scrollLeft = scroller.scrollLeft;
	}

	handleMouseUpLeave() {
		this.isDragging = false;
		const scroller = this.element.querySelector('.carousel-images-scroller');
		scroller.style.cursor = 'grab';
		this.snapToImage();
	}

	handleMouseMove(event) {
		if (!this.isDragging) {
			return;
		}

		event.preventDefault();
		const x = event.pageX - this.element.offsetLeft;
		const scrollSpeed = (x - this.startX) * 1.75;
		const scroller = this.element.querySelector('.carousel-images-scroller');
		scroller.scrollTo({ left: this.scrollLeft - scrollSpeed, behavior: 'auto' });
	}

	handleTouchStart(event) {
		this.isDragging = true;
		this.startX = event.touches[0].pageX - this.element.offsetLeft;
		const scroller = this.element.querySelector('.carousel-images-scroller');
		this.scrollLeft = scroller.scrollLeft;
	}

	handleTouchMove(event) {
		if (!this.isDragging) {
			return;
		}

		event.preventDefault();
		const x = event.touches[0].pageX - this.element.offsetLeft;
		const scrollSpeed = (x - this.startX) * 1.75;
		const scroller = this.element.querySelector('.carousel-images-scroller');
		scroller.scrollTo({ left: this.scrollLeft - scrollSpeed, behavior: 'auto' });
	}

	handleTouchEnd() {
		this.isDragging = false;
		this.snapToImage();
	}

	snapToImage() {
		const scroller = this.element.querySelector('.carousel-images-scroller');
		const scrollLeft = scroller.scrollLeft;
		const index = Math.round(scrollLeft / this.firstImageWidth);
		moveSlides(index, this.element);
	}

	getDots() {
		let html = '';

		if (this.figures.length > 5) {
			html += `${Number(this.element.dataset.index) + 1}&nbsp;/&nbsp;${this.figures.length}`;
		}
		else {
			for (let i = 0; i < this.figures.length; i++) {
				if (i === Number(this.element.dataset.index)) {
					html += `<span class="active"></span>`;
					continue;
				}

				html += `<span></span>`;
			}
		}

		return html;
	}

	updateDots() {
		const dots = this.element.querySelector('.carousel-dots');
		dots.innerHTML = this.getDots();
	}
}

// Initialize all carousels with the class "as-image-carousel"
document.addEventListener('DOMContentLoaded', () => {
	const carousels = document.querySelectorAll('.as-image-carousel');
	carousels.forEach((carousel) => new ImageCarousel(carousel));
	carousels.forEach((carousel) => console.log(carousel));
});
