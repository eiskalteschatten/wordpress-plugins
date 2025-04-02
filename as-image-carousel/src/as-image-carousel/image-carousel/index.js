import './image-carousel.js';
import './image-carousel-nav-button.js';
import './image-carousel-lightbox-button.js';
import './light-box.js';

export function openLightbox(imageSrc, caption = '') {
  const lightbox = document.createElement('light-box');
  lightbox.dataset.imageSrc = imageSrc;
  lightbox.dataset.caption = caption || '';
  document.body.appendChild(lightbox);
}
