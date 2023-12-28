import "parsleyjs";
import "parsleyjs/dist/i18n/pl";
import "parsleyjs/dist/i18n/en";

window.ParsleyValidator.setLocale(oclVars.lang);

const initModals = async () => {
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

const initParsley = async () => {
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

const initFoldable = async () => {
    const elements = document.querySelectorAll('[data-ocl-foldable]');

    if(elements.length === 0) return;

    elements.forEach(element => {
        const handle = element.querySelector('[data-ocl-foldable-handle]');
        const content = element.querySelector('[data-ocl-foldable-content]');

        if(!handle || !content) return;

        handle.addEventListener('click', e => {
            e.preventDefault();

            $(content).slideToggle();
            element.classList.toggle('is-open');
        }); 
    });
}
initFoldable();

const initDiscounts = async () => {
    const forms = document.querySelectorAll('[data-ocl-order-form]');

    if(forms.length === 0) return;

    forms.forEach(form => {
        const codeInput = form.querySelector('input[name="discount-code"]');
        const amountInput = form.querySelector('input[name="amount"]');
        const crcInput = form.querySelector('input[name="crc"]');
        const md5SumInput = form.querySelector('input[name="md5sum"]');
        const applyBtn = form.querySelector('[data-ocl-discount-submit]');
        const resultUrlInput = form.querySelector('input[name="result_url"]');

        applyBtn.addEventListener('click', e => {
            e.preventDefault();

            if(codeInput.value === "" || amountInput.value === "" || crcInput === "") return;

            $.post(oclVars.ajaxURL, {
                action: 'check-discount',
                code: codeInput.value,
                amount: amountInput.value,
                crc: crcInput.value
            }).done(response => {
                if(response.success) {
                    amountInput.value = response.data.amount;
                    md5SumInput.value = response.data.md5sum;

                    const discountTerm = response.data.discountTerm;

                    if(discountTerm) {
                        const newResultUrl = new URL(resultUrlInput.value);
                        newResultUrl.searchParams.set('discount', discountTerm);
                        resultUrlInput.value = newResultUrl;
                    }
                }
            }).catch(() => {

            });
        })
    }); 
}
initDiscounts();