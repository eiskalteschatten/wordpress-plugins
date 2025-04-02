/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/as-image-carousel/image-carousel/image-carousel-lightbox-button.js":
/*!********************************************************************************!*\
  !*** ./src/as-image-carousel/image-carousel/image-carousel-lightbox-button.js ***!
  \********************************************************************************/
/***/ (() => {

class ImageCarouselButton extends HTMLElement {
  constructor() {
    super();
  }
  connectedCallback() {
    this.innerHTML = '<i class="material-icons">open_in_full</i>';
    this.onclick = this.handleClick;
  }
  disconnectedCallback() {
    this.onclick = undefined;
  }
  handleClick() {
    const parent = this.parentElement;
    if (!parent) {
      throw new Error('Parent image-carousel element not found!');
    }
    const index = Number(parent.dataset.index);
    const figure = parent.querySelectorAll('figure')[index];
    const caption = figure.querySelector('figcaption')?.textContent;
    const imageSrc = figure.querySelector('img').src;
    openLightbox(imageSrc, caption);
  }
}
customElements.define('image-carousel-lightbox-button', ImageCarouselButton);

/***/ }),

/***/ "./src/as-image-carousel/image-carousel/image-carousel-nav-button.js":
/*!***************************************************************************!*\
  !*** ./src/as-image-carousel/image-carousel/image-carousel-nav-button.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   moveSlides: () => (/* binding */ moveSlides)
/* harmony export */ });
class ImageCarouselNavButton extends HTMLElement {
  constructor() {
    super();
  }
  connectedCallback() {
    if (this.dataset.variant === 'prev') {
      this.innerHTML = '<i class="material-icons">arrow_back_ios</i>';
    } else if (this.dataset.variant === 'next') {
      this.innerHTML = '<i class="material-icons">arrow_forward_ios</i>';
    }
    this.onclick = this.handleClick;
  }
  disconnectedCallback() {
    this.onclick = undefined;
  }
  handleClick() {
    const parent = this.parentElement;
    if (!parent) {
      throw new Error('Parent image-carousel element not found!');
    }
    const index = Number(parent.dataset.index);
    if (this.dataset.variant === 'prev') {
      moveSlides(index - 1, parent);
    } else if (this.dataset.variant === 'next') {
      moveSlides(index + 1, parent);
    }
  }
}
customElements.define('image-carousel-nav-button', ImageCarouselNavButton);
function moveSlides(index, imageCarousel) {
  if (!imageCarousel) {
    throw new Error('image-carousel element not found!');
  }
  const images = imageCarousel.querySelector('.carousel-images');
  const imageWidth = images.firstElementChild.clientWidth;
  const maxIndex = images.children.length - 1;
  if (index < 0) {
    index = maxIndex;
  } else if (index > maxIndex) {
    index = 0;
  }
  imageCarousel.dataset.index = Number(index);
  const scroller = imageCarousel.querySelector('.carousel-images-scroller');
  scroller.scrollTo({
    left: index * imageWidth,
    behavior: 'smooth'
  });
  const event = new CustomEvent('slideChange', {
    detail: {
      index
    },
    bubbles: true,
    cancelable: true
  });
  imageCarousel.dispatchEvent(event);
}

/***/ }),

/***/ "./src/as-image-carousel/image-carousel/image-carousel.js":
/*!****************************************************************!*\
  !*** ./src/as-image-carousel/image-carousel/image-carousel.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _image_carousel_nav_button_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./image-carousel-nav-button.js */ "./src/as-image-carousel/image-carousel/image-carousel-nav-button.js");

class ImageCarousel extends HTMLElement {
  isDragging = false;
  startX = 0;
  scrollLeft = 0;
  firstImageWidth = 0;
  constructor() {
    super();
  }
  connectedCallback() {
    this.dataset.index = 0;
    this.figures = this.querySelectorAll('figure');
    if (!this.figures?.length) {
      throw new Error('No figures could be found in the image carousel!');
    }

    // Wait for the first image to load before resizing
    const firstImage = this.figures[0].querySelectorAll('img')[0];
    if (firstImage.complete) {
      this.resizeImages();
      this.updateHtml();
    } else {
      firstImage.addEventListener('load', () => {
        this.resizeImages();
        this.updateHtml();
      });
    }
    window.addEventListener('resize', this.resizeImages.bind(this));
    this.addEventListener('slideChange', this.updateDots.bind(this));
    this.addEventListener('mousedown', this.handleMouseDown.bind(this));
    this.addEventListener('mouseup', this.handleMouseUpLeave.bind(this));
    this.addEventListener('mouseleave', this.handleMouseUpLeave.bind(this));
    this.addEventListener('mousemove', this.handleMouseMove.bind(this));
    this.addEventListener('touchstart', this.handleTouchStart.bind(this));
    this.addEventListener('touchmove', this.handleTouchMove.bind(this));
    this.addEventListener('touchend', this.handleTouchEnd.bind(this));
  }
  disconnectedCallback() {
    window.removeEventListener('resize', this.resizeImages.bind(this));
    this.removeEventListener('slideChange', this.updateDots.bind(this));
    this.removeEventListener('mousedown', this.handleMouseDown.bind(this));
    this.removeEventListener('mouseup', this.handleMouseUpLeave.bind(this));
    this.removeEventListener('mouseleave', this.handleMouseUpLeave.bind(this));
    this.removeEventListener('mousemove', this.handleMouseMove.bind(this));
    this.removeEventListener('touchstart', this.handleTouchStart.bind(this));
    this.removeEventListener('touchmove', this.handleTouchMove.bind(this));
    this.removeEventListener('touchend', this.handleTouchEnd.bind(this));
  }
  resizeImages() {
    const firstImage = this.figures[0].querySelectorAll('img')[0];
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
    this.innerHTML = `
      <image-carousel-lightbox-button></image-carousel-lightbox-button>
      <image-carousel-nav-button data-variant="prev"></image-carousel-nav-button>
      <div class="carousel-images-scroller">
        <div class="carousel-images">
          ${Array.from(this.figures).map(figure => figure.outerHTML).join('')}
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
    const scroller = this.querySelector('.carousel-images-scroller');
    scroller.style.cursor = 'grabbing';
    this.isDragging = true;
    this.startX = event.pageX - this.offsetLeft;
    this.scrollLeft = scroller.scrollLeft;
  }
  handleMouseUpLeave() {
    this.isDragging = false;
    const scroller = this.querySelector('.carousel-images-scroller');
    scroller.style.cursor = 'grab';
    this.snapToImage();
  }
  handleMouseMove(event) {
    if (!this.isDragging) {
      return;
    }
    event.preventDefault();
    const x = event.pageX - this.offsetLeft;
    const scrollSpeed = (x - this.startX) * 1.75;
    const scroller = this.querySelector('.carousel-images-scroller');
    scroller.scrollTo({
      left: this.scrollLeft - scrollSpeed,
      behavior: 'auto'
    });
  }
  handleTouchStart(event) {
    this.isDragging = true;
    this.startX = event.touches[0].pageX - this.offsetLeft;
    const scroller = this.querySelector('.carousel-images-scroller');
    this.scrollLeft = scroller.scrollLeft;
  }
  handleTouchMove(event) {
    if (!this.isDragging) {
      return;
    }
    event.preventDefault();
    const x = event.touches[0].pageX - this.offsetLeft;
    const scrollSpeed = (x - this.startX) * 1.75;
    const scroller = this.querySelector('.carousel-images-scroller');
    scroller.scrollTo({
      left: this.scrollLeft - scrollSpeed,
      behavior: 'auto'
    });
  }
  handleTouchEnd() {
    this.isDragging = false;
    this.snapToImage();
  }
  snapToImage() {
    const scroller = this.querySelector('.carousel-images-scroller');
    const scrollLeft = scroller.scrollLeft;
    const index = Math.round(scrollLeft / this.firstImageWidth);
    (0,_image_carousel_nav_button_js__WEBPACK_IMPORTED_MODULE_0__.moveSlides)(index, this);
  }
  getDots() {
    let html = '';
    if (this.figures.length > 5) {
      html += `${Number(this.dataset.index) + 1}&nbsp;/&nbsp;${this.figures.length}`;
    } else {
      for (let i = 0; i < this.figures.length; i++) {
        if (i === Number(this.dataset.index)) {
          html += `<span class="active"></span>`;
          continue;
        }
        html += `<span></span>`;
      }
    }
    return html;
  }
  updateDots() {
    const dots = this.querySelector('.carousel-dots');
    dots.innerHTML = this.getDots();
  }
}
customElements.define('image-carousel', ImageCarousel);

/***/ }),

/***/ "./src/as-image-carousel/image-carousel/index.js":
/*!*******************************************************!*\
  !*** ./src/as-image-carousel/image-carousel/index.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   openLightbox: () => (/* binding */ openLightbox)
/* harmony export */ });
/* harmony import */ var _image_carousel_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./image-carousel.js */ "./src/as-image-carousel/image-carousel/image-carousel.js");
/* harmony import */ var _image_carousel_nav_button_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./image-carousel-nav-button.js */ "./src/as-image-carousel/image-carousel/image-carousel-nav-button.js");
/* harmony import */ var _image_carousel_lightbox_button_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./image-carousel-lightbox-button.js */ "./src/as-image-carousel/image-carousel/image-carousel-lightbox-button.js");
/* harmony import */ var _image_carousel_lightbox_button_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_image_carousel_lightbox_button_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _light_box_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./light-box.js */ "./src/as-image-carousel/image-carousel/light-box.js");
/* harmony import */ var _light_box_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_light_box_js__WEBPACK_IMPORTED_MODULE_3__);




function openLightbox(imageSrc, caption = '') {
  const lightbox = document.createElement('light-box');
  lightbox.dataset.imageSrc = imageSrc;
  lightbox.dataset.caption = caption || '';
  document.body.appendChild(lightbox);
}

/***/ }),

/***/ "./src/as-image-carousel/image-carousel/light-box.js":
/*!***********************************************************!*\
  !*** ./src/as-image-carousel/image-carousel/light-box.js ***!
  \***********************************************************/
/***/ (() => {

class LightBox extends HTMLElement {
  connectedCallback() {
    const imageSrc = this.dataset.imageSrc;
    const caption = this.dataset.caption || '';
    this.innerHTML = `
      <img src="${imageSrc}" alt="${caption}" class="lightbox-image">

      <button class="close-button">
        <span class="material-icons">close</span>
      </button>

      <div class="caption">${caption}</div>
    `;
    this.lightboxImage = this.querySelector('img');
    this.closeButton = this.querySelector('.close-button');
    this.closeButton.onclick = this.close.bind(this);
  }
  close() {
    this.remove();
  }
}
customElements.define('light-box', LightBox);

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!***************************************!*\
  !*** ./src/as-image-carousel/view.js ***!
  \***************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _image_carousel__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./image-carousel */ "./src/as-image-carousel/image-carousel/index.js");
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


})();

/******/ })()
;
//# sourceMappingURL=view.js.map