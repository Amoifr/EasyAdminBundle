import * as basicLightbox from 'basiclightbox';

// event delegation on document, so it works regardless of when the script runs
// (blocking in <head>, deferred or async) and for thumbnails added after load
document.addEventListener('click', (event) => {
    const thumbnail = event.target.closest('.ea-lightbox-thumbnail');
    if (null === thumbnail) {
        return;
    }

    // let the browser handle modifier-key clicks (e.g. Cmd/Ctrl-click opens the image in a new tab)
    if (event.metaKey || event.ctrlKey || event.shiftKey) {
        return;
    }

    // if the lightbox content is missing, don't prevent the default behavior,
    // so the link acts as a fallback that opens the image itself
    const content = document.querySelector(thumbnail.dataset.eaLightboxContentSelector);
    if (null === content) {
        return;
    }

    event.preventDefault();

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
