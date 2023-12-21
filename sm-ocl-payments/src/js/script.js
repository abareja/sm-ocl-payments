import "parsleyjs";
import "parsleyjs/dist/i18n/pl";
import "parsleyjs/dist/i18n/en";

window.ParsleyValidator.setLocale(oclVars.lang);

const initModals = async  () => {
  const modals = document.querySelectorAll('[data-ocl-modal]');
  const toggleModals = document.querySelectorAll('[data-ocl-toggle-modal]');

  if( modals.length !== 0 && toggleModals.length !== 0 ) {
      import(/* webpackChunkName: "modal" */ "./modal").then(modalScript => {
          modals.forEach(modal => {
              modalScript.initModal(modal);
          });

          toggleModals.forEach(toggleModal => {
              modalScript.toggleModal(toggleModal);
          });
      });
  }
}
initModals();

const initParsley = () => {
    const orderForms = document.querySelectorAll('[data-ocl-order-form]');
    if( orderForms.length !== 0 ){
        orderForms.forEach(form => {
            const parsley = $(form).parsley();

            form.addEventListener( "submit", event => {
                event.preventDefault();
                if( parsley.isValid()) {
                    form.submit();
                }
            });
        });
    }
}
initParsley();