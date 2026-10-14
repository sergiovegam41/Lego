/**
 * Native <dialog> confirmation and a small toast. No library, no window.alert.
 */
export function confirm(message, { confirmLabel = 'Confirmar', cancelLabel = 'Cancelar', danger = true } = {}) {
  return new Promise((resolve) => {
    const dialog = document.createElement('dialog');
    dialog.className = 'lego-dialog';
    dialog.dataset.legoDialog = '';

    const text = document.createElement('p');
    text.textContent = message;

    const buttons = document.createElement('div');
    buttons.className = 'lego-dialog__buttons';
    const cancel = document.createElement('button');
    cancel.type = 'button';
    cancel.className = 'lego-button';
    cancel.textContent = cancelLabel;
    const accept = document.createElement('button');
    accept.type = 'button';
    accept.className = danger ? 'lego-button lego-button--danger' : 'lego-button lego-button--primary';
    accept.textContent = confirmLabel;
    accept.dataset.legoConfirm = '';
    buttons.append(cancel, accept);
    dialog.append(text, buttons);

    const finish = (value) => {
      dialog.close();
      dialog.remove();
      resolve(value);
    };
    cancel.addEventListener('click', () => finish(false));
    accept.addEventListener('click', () => finish(true));
    dialog.addEventListener('cancel', (event) => {
      event.preventDefault();
      finish(false);
    });

    document.body.append(dialog);
    dialog.showModal();
    cancel.focus();
  });
}

export function toast(message, { timeout = 2500 } = {}) {
  const element = document.createElement('div');
  element.className = 'lego-toast';
  element.setAttribute('role', 'status');
  element.textContent = message;
  document.body.append(element);
  setTimeout(() => element.remove(), timeout);
}
