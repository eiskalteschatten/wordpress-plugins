function openLightbox(imageSrc, caption = '') {
  const lightbox = document.createElement('light-box');
  lightbox.dataset.imageSrc = imageSrc;
  lightbox.dataset.caption = caption || '';
  document.body.appendChild(lightbox);
}
