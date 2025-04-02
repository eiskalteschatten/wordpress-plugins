/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./src/as-image-carousel/block.json":
/*!******************************************!*\
  !*** ./src/as-image-carousel/block.json ***!
  \******************************************/
/***/ ((module) => {

"use strict";
module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"as-image-carousel/as-image-carousel","version":"0.1.0","title":"Alex\'s Image Carousel","category":"media","icon":"images-alt","description":"Create an image carousel","example":{},"supports":{"html":false},"textdomain":"as-image-carousel","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","viewScript":"file:./view.js","attributes":{"images":{"type":"array","default":[]}}}');

/***/ }),

/***/ "./src/as-image-carousel/edit.js":
/*!***************************************!*\
  !*** ./src/as-image-carousel/edit.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./editor.scss */ "./src/as-image-carousel/editor.scss");
/* harmony import */ var _image_carousel__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./image-carousel */ "./src/as-image-carousel/image-carousel/index.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__);



/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */


/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */



/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */

function Edit({
  attributes,
  setAttributes
}) {
  const {
    images
  } = attributes;
  const onSelectImages = newImages => {
    setAttributes({
      images: newImages
    });
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("div", {
    ...(0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useBlockProps)(),
    children: images.length > 0 ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("image-carousel", {
      children: images.map(img => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)("figure", {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("img", {
          src: img.url,
          alt: img.alt,
          loading: "lazy"
        }, img.id), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("figcaption", {
          children: img.caption
        })]
      }))
    }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)("p", {
      className: "as-image-carousel-select-images",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("div", {
        className: "as-image-carousel-select-images-text",
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Select images for the carousel:', 'as-image-carousel')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)("div", {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.MediaUploadCheck, {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.MediaUpload, {
            onSelect: onSelectImages,
            allowedTypes: ['image'],
            multiple: true,
            gallery: true,
            value: images.map(img => img.id),
            render: ({
              open
            }) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
              onClick: open,
              variant: "primary",
              children: "Select Images"
            })
          })
        })
      })]
    })
  });
  // return (
  // 	<p { ...useBlockProps() }>
  // 		{ __(
  // 			'Image Carousel – hello from the editor!',
  // 			'as-image-carousel'
  // 		) }
  // 	</p>
  // );
}

/***/ }),

/***/ "./src/as-image-carousel/editor.scss":
/*!*******************************************!*\
  !*** ./src/as-image-carousel/editor.scss ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

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

/***/ }),

/***/ "./src/as-image-carousel/index.js":
/*!****************************************!*\
  !*** ./src/as-image-carousel/index.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/as-image-carousel/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/as-image-carousel/edit.js");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./save */ "./src/as-image-carousel/save.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./src/as-image-carousel/block.json");
/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */


/**
 * Internal dependencies
 */




/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_4__.name, {
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"],
  /**
   * @see ./save.js
   */
  save: _save__WEBPACK_IMPORTED_MODULE_3__["default"],
  attributes: _block_json__WEBPACK_IMPORTED_MODULE_4__.attributes
});

/***/ }),

/***/ "./src/as-image-carousel/save.js":
/*!***************************************!*\
  !*** ./src/as-image-carousel/save.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ save)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__);
/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */


/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */

function save({
  attributes
}) {
  const {
    images
  } = attributes;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("p", {
    ..._wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useBlockProps.save(),
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("image-carousel", {
      children: images.map(img => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("figure", {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("img", {
          src: img.url,
          alt: img.alt,
          loading: "lazy"
        }, img.id), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("figcaption", {
          children: img.caption
        })]
      }))
    })
  });
}

/***/ }),

/***/ "./src/as-image-carousel/style.scss":
/*!******************************************!*\
  !*** ./src/as-image-carousel/style.scss ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

"use strict";
module.exports = window["ReactJSXRuntime"];

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
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"as-image-carousel/index": 0,
/******/ 			"as-image-carousel/style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkas_image_carousel"] = globalThis["webpackChunkas_image_carousel"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["as-image-carousel/style-index"], () => (__webpack_require__("./src/as-image-carousel/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map