import * as basicLightbox from 'basiclightbox';

// event delegation on document, so it works regardless of when the script runs
// (blocking in <head>, deferred or async) and for thumbnails added after load
document.addEventListener('click', (event) => {
    const thumbnail = event.target.closest('.ea-lightbox-thumbnail');
    if (null === thumbnail) {
        return;
    }

    event.preventDefault();

    const content = document.querySelector(thumbnail.dataset.eaLightboxContentSelector);
    if (null === content) {
        return;
    }

    const onKeyDown = (keyEvent) => {
        if ('Escape' === keyEvent.key) {
            lightbox.close();
        }
    };

    // basiclightbox has no built-in keyboard handling, so bind the Escape
    // listener only while the lightbox is open and remove it on close
    const lightbox = basicLightbox.create(content.innerHTML, {
        onShow: () => document.addEventListener('keydown', onKeyDown),
        onClose: () => document.removeEventListener('keydown', onKeyDown),
    });

    lightbox.show();
});
