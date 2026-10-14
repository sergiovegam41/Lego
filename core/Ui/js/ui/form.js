/**
 * Behavior of [data-lego="form"]: submits through the JSON action API, the
 * same path an AI agent or the CLI uses, instead of a full page POST.
 *
 * Attributes set by RecordForm:
 *   data-lego-action  action name, e.g. clients_update
 *   data-lego-record  record id, for record actions
 *   data-lego-after   "list" (go back to the list and close) or "reload"
 *   data-lego-list    URL of the module list
 *   data-lego-dirty   track unsaved changes on this form
 *
 * Without JavaScript the same form posts to the server and works as well.
 */
import { EVENTS } from 'lego';
import { confirm, notice, toast } from 'lego/ui/dialog.js';

export function mount(form, ctx) {
  const { signal } = ctx;

  if (form.hasAttribute('data-lego-dirty')) {
    form.addEventListener('input', () => ctx.setDirty(true), { signal });
  }

  for (const closer of form.querySelectorAll('[data-lego-close]')) {
    closer.addEventListener('click', (event) => {
      event.preventDefault();
      ctx.close();
    }, { signal });
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = event.submitter ?? form.querySelector('[type="submit"]');
    button?.setAttribute('aria-busy', 'true');
    try {
      await submit(form, ctx);
    } finally {
      button?.removeAttribute('aria-busy');
    }
  }, { signal });
}

async function submit(form, ctx) {
  clearErrors(form);
  const input = readInput(form);
  if (form.dataset.legoRecord) {
    input.id = Number(form.dataset.legoRecord);
  }

  const action = form.dataset.legoAction;
  let result = await ctx.api(action, input);

  if (result.status === 'confirmation_required') {
    if (!(await confirm(result.confirmation.message))) {
      return;
    }
    result = await ctx.api(action, { ...input, confirmation_token: result.confirmation.token });
  }

  if (result.status === 'ok') {
    ctx.setDirty(false);
    ctx.emit(EVENTS.DATA_CHANGED, { module: form.dataset.legoModule, list: form.dataset.legoList, action, from: ctx.key });
    // An action may return a message the user must read (e.g. a new password).
    if (typeof result.data?.message === 'string') {
      await notice(result.data.message);
    }
    if (form.dataset.legoAfter === 'list') {
      await ctx.navigate(form.dataset.legoList);
      await ctx.close({ force: true });
    } else {
      if (typeof result.data?.message !== 'string') {
        toast('Hecho.');
      }
      await ctx.reload();
    }
    return;
  }

  showErrors(form, result.error ?? { message: 'No se pudo completar la acción.' });
}

/** Form fields as an object; repeated "name[]" fields become arrays without empty values. */
function readInput(form) {
  const data = new FormData(form);
  const input = {};
  for (const name of new Set(data.keys())) {
    if (name === '_csrf') {
      continue;
    }
    if (name.endsWith('[]')) {
      input[name.slice(0, -2)] = data.getAll(name).filter((value) => value !== '');
    } else {
      input[name] = data.getAll(name).at(-1);
    }
  }
  return input;
}

function clearErrors(form) {
  form.querySelectorAll('[data-lego-error]').forEach((element) => element.remove());
  form.querySelectorAll('[aria-invalid]').forEach((element) => {
    element.removeAttribute('aria-invalid');
    element.removeAttribute('aria-describedby');
  });
}

function showErrors(form, error) {
  const unplaced = [];
  for (const [name, problems] of Object.entries(error.fields ?? {})) {
    const container = form.querySelector(`[data-field="${CSS.escape(name)}"]`);
    const message = problems.map((problem) => problem.message).join(' ');
    if (!container) {
      unplaced.push(message);
      continue;
    }
    const control = container.querySelector('input:not([type="hidden"]), select, textarea');
    const paragraph = document.createElement('p');
    paragraph.className = 'lego-field__error';
    paragraph.dataset.legoError = '';
    paragraph.id = `${control?.id ?? name}-error`;
    paragraph.textContent = message;
    container.append(paragraph);
    control?.setAttribute('aria-invalid', 'true');
    control?.setAttribute('aria-describedby', paragraph.id);
  }

  const alert = document.createElement('p');
  alert.className = 'lego-records__alert';
  alert.setAttribute('role', 'alert');
  alert.dataset.legoError = '';
  alert.textContent = [error.message, ...unplaced].filter(Boolean).join(' ');
  form.prepend(alert);
  form.querySelector('[aria-invalid="true"]')?.focus();
}
