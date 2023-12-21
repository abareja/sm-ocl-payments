import forEach from 'lodash/forEach';

export const initModal = (modal) => {
    const closeModal = modal.querySelectorAll('[data-ocl-modal-close]');

    forEach( closeModal, close => {
        close.addEventListener("click", e => {
            e.preventDefault();
            modal.classList.remove('is-open');
        });
    })
}

export const toggleModal = (button) => {
    const modal = document.querySelector( button.getAttribute("href") );
    if( modal ) {
        button.addEventListener("click", event => {
            event.preventDefault();
            modal.classList.add('is-open');
        });
    }
}