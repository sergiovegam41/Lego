/**
 * Lego shell: navigation between screens without reloading the page.
 *
 * Contract (doc/auditoria-2026-10-13/module-navigation.md, decisions D8, C1-C9):
 * - The URL names the active screen. Its path is the instance key
 *   ("/clients/5" -> "clients/5") and its query is that instance's state.
 * - Every screen is a <section data-lego-screen> rendered by the server,
 *   as part of the page (first load, F5) or as a fragment from /component/...
 * - Open screens stay mounted (hidden) so scroll, filters and half-written
 *   forms survive switching tabs. After F5 only the active one comes back.
 * - Behaviors are mounted by attribute: [data-lego="table"] loads
 *   lego/ui/table.js and calls mount(element, ctx).
 * - fetchFragment() and api() are the only functions that call fetch.
 */

export const bus = new EventTarget();

export const EVENTS = Object.freeze({
  MOUNTED: 'lego:screen-mounted',
  ACTIVATED: 'lego:screen-activated',
  UNMOUNTED: 'lego:screen-unmounted',
  DATA_CHANGED: 'lego:data-changed',
});

export const MAX_OPEN = 10;

export class LegoHttpError extends Error {
  constructor(status, body) {
    super(`HTTP ${status}`);
    this.status = status;
    this.body = body;
  }
}

/** @type {Map<string, Instance>} */
const open = new Map();
const recent = [];
let active = null;
let navigation = 0;
let outlet = null;
let tabs = null;

/** '/clients/5?page=2' -> 'clients/5'; '/' -> 'home' */
export function keyFromUrl(url) {
  const path = new URL(url, location.origin).pathname.replace(/^\/component(?=\/|$)/, '').replace(/^\/+|\/+$/g, '');
  return path === '' ? 'home' : path;
}

/** '/clients/5?page=2' -> '/component/clients/5?page=2' */
export function fragmentUrl(url) {
  const u = new URL(url, location.origin);
  return '/component' + (u.pathname === '/' ? '' : u.pathname) + u.search;
}

export function activeKey() {
  return active;
}

export function instances() {
  return [...open.values()].map(({ key, url, title, dirty, state }) => ({ key, url, title, dirty, state }));
}

export function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function toLogin() {
  location.assign('/login?next=' + encodeURIComponent(location.pathname + location.search));
}

/** Fetches a screen fragment. 401 goes to the login; other errors throw LegoHttpError with the HTML. */
export async function fetchFragment(url, { signal } = {}) {
  const response = await fetch(url, { headers: { Accept: 'text/html' }, credentials: 'same-origin', signal });
  if (response.status === 401) {
    toLogin();
    throw new LegoHttpError(401, '');
  }
  const html = await response.text();
  if (!response.ok) {
    throw new LegoHttpError(response.status, html);
  }
  return html;
}

/**
 * Runs an action through POST /api/actions/{name}. Returns the response
 * envelope with `http` added; only 401 and network failures throw.
 */
export async function api(action, input = {}, { signal } = {}) {
  const response = await fetch('/api/actions/' + encodeURIComponent(action), {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-Token': csrfToken() },
    body: JSON.stringify(input),
    signal,
  });
  if (response.status === 401) {
    toLogin();
    throw new LegoHttpError(401, null);
  }
  const body = await response.json().catch(() => ({ status: 'error', error: { code: 'invalid_response', message: 'Respuesta inesperada del servidor.' } }));
  return { ...body, http: response.status };
}

/** The only way to navigate: opens or activates the screen of a URL. */
export async function openModule(url, { replace = false, history = true } = {}) {
  const target = new URL(url, location.origin);
  const key = keyFromUrl(target.href);
  const canonical = key === 'home' ? '/' : target.pathname + target.search;
  const token = ++navigation;

  let instance = open.get(key);
  if (!instance) {
    instance = createInstance(key, canonical);
  } else if (instance.url !== canonical) {
    instance.url = canonical;
    reload(instance);
  }

  if (history) {
    const sameKey = active === key;
    window.history[replace || sameKey ? 'replaceState' : 'pushState']({ key }, '', canonical);
  }

  await instance.ready;
  if (token !== navigation) {
    return instance;
  }
  activate(key, { focus: true });
  enforceLimit();
  return instance;
}

/** Closes a screen. Asks first when it has unsaved changes. */
export async function closeModule(key = active, { force = false } = {}) {
  const instance = open.get(key);
  if (!instance) {
    return true;
  }
  if (instance.dirty && !force) {
    const { confirm } = await import('lego/ui/dialog.js');
    if (!(await confirm('Hay cambios sin guardar. ¿Cerrar igualmente?', { confirmLabel: 'Cerrar' }))) {
      return false;
    }
  }

  unmount(instance);
  instance.root.remove();
  open.delete(key);
  forget(key);
  bus.dispatchEvent(new CustomEvent(EVENTS.UNMOUNTED, { detail: { key } }));

  if (active === key) {
    active = null;
    const next = open.get(recent.at(-1)) ?? null;
    await openModule(next ? next.url : '/', { replace: true });
  } else {
    renderTabs();
  }
  return true;
}

export async function reloadModule(key = active) {
  const instance = open.get(key);
  if (instance) {
    await reload(instance);
  }
}

function createInstance(key, url) {
  const root = document.createElement('section');
  root.className = 'lego-screen';
  root.dataset.state = 'loading';
  root.hidden = true;
  outlet.append(root);

  const instance = { key, url, title: 'Cargando…', root, dirty: false, state: 'loading', controller: null, cleanups: [], ready: null };
  open.set(key, instance);
  instance.ready = load(instance);
  renderTabs();
  return instance;
}

async function load(instance) {
  const controller = new AbortController();
  instance.controller = controller;
  let html;
  let failed = false;
  try {
    html = await fetchFragment(fragmentUrl(instance.url), { signal: controller.signal });
  } catch (error) {
    if (controller.signal.aborted || error.status === 401) {
      return;
    }
    html = error instanceof LegoHttpError && error.body ? error.body : '<section class="lego-error"><h1>Sin conexión</h1><p>No se pudo cargar la pantalla.</p></section>';
    failed = true;
  }
  if (controller.signal.aborted) {
    return;
  }
  place(instance, html, failed);
}

/** Inserts fragment HTML into the instance root and mounts its behaviors. */
function place(instance, html, failed) {
  const template = document.createElement('template');
  template.innerHTML = html;
  for (const link of template.content.querySelectorAll('link[rel="stylesheet"]')) {
    if (!document.head.querySelector(`link[rel="stylesheet"][href="${CSS.escape(link.getAttribute('href'))}"]`)) {
      document.head.append(link.cloneNode());
    }
    link.remove();
  }
  template.content.querySelectorAll('script:not([type="application/json"])').forEach((script) => script.remove());

  const screen = template.content.querySelector('[data-lego-screen]');
  const root = screen ?? document.createElement('section');
  if (!screen) {
    root.className = 'lego-screen';
    root.append(template.content);
    const retry = document.createElement('button');
    retry.type = 'button';
    retry.className = 'lego-button';
    retry.textContent = 'Reintentar';
    retry.addEventListener('click', () => reloadModule(instance.key));
    root.append(retry);
  }

  root.hidden = instance.root.hidden;
  instance.root.replaceWith(root);
  instance.root = root;
  instance.state = failed ? 'error' : 'ready';
  root.dataset.state = instance.state;
  instance.title = root.dataset.legoTitle ?? (failed ? 'Error' : 'Lego');
  mount(instance);
  renderTabs();
}

function mount(instance) {
  const controller = instance.controller ?? new AbortController();
  instance.controller = controller;
  const { signal } = controller;
  const ctx = {
    key: instance.key,
    get url() {
      return instance.url;
    },
    signal,
    props: readProps(instance.root),
    navigate: (url, options) => openModule(url, options),
    close: (options) => closeModule(instance.key, options),
    reload: () => reloadModule(instance.key),
    setDirty(dirty) {
      if (instance.dirty !== dirty) {
        instance.dirty = dirty;
        renderTabs();
      }
    },
    on: (event, handler) => bus.addEventListener(event, handler, { signal }),
    emit: (event, detail = {}) => bus.dispatchEvent(new CustomEvent(event, { detail })),
    api: (action, input) => api(action, input, { signal }),
  };

  const behaviors = [...instance.root.querySelectorAll('[data-lego]')];
  if (instance.root.matches('[data-lego]')) {
    behaviors.unshift(instance.root);
  }
  for (const element of behaviors) {
    import(`lego/ui/${element.dataset.lego}.js`).then((behavior) => {
      if (signal.aborted) {
        return;
      }
      const cleanup = behavior.mount(element, ctx);
      if (typeof cleanup === 'function') {
        instance.cleanups.push(cleanup);
      }
    });
  }
  if (instance.root.dataset.legoJs) {
    import(instance.root.dataset.legoJs).then((screen) => {
      if (!signal.aborted) {
        const cleanup = screen.mount?.(instance.root, ctx);
        if (typeof cleanup === 'function') {
          instance.cleanups.push(cleanup);
        }
      }
    });
  }
  bus.dispatchEvent(new CustomEvent(EVENTS.MOUNTED, { detail: { key: instance.key } }));
}

function unmount(instance) {
  instance.controller?.abort();
  instance.controller = null;
  instance.cleanups.splice(0).forEach((cleanup) => cleanup());
}

async function reload(instance) {
  unmount(instance);
  instance.dirty = false;
  instance.ready = load(instance);
  await instance.ready;
}

function readProps(root) {
  const props = {};
  for (const script of root.querySelectorAll('script[type="application/json"][data-lego-actions]')) {
    try {
      props.actions = JSON.parse(script.textContent);
    } catch {
      props.actions = [];
    }
  }
  return props;
}

function activate(key, { focus = false } = {}) {
  const instance = open.get(key);
  if (!instance) {
    return;
  }
  for (const other of open.values()) {
    other.root.hidden = other.key !== key;
  }
  active = key;
  forget(key);
  recent.push(key);
  document.title = `${instance.title} · Lego`;

  const section = key.split('/')[0];
  for (const link of document.querySelectorAll('[data-lego-nav]')) {
    if (keyFromUrl(link.href).split('/')[0] === section) {
      link.setAttribute('aria-current', 'page');
    } else {
      link.removeAttribute('aria-current');
    }
  }

  if (focus) {
    const heading = instance.root.querySelector('h1');
    if (heading) {
      heading.tabIndex = -1;
      heading.focus({ preventScroll: true });
    }
  }
  renderTabs();
  bus.dispatchEvent(new CustomEvent(EVENTS.ACTIVATED, { detail: { key, url: instance.url } }));
}

function forget(key) {
  const index = recent.indexOf(key);
  if (index !== -1) {
    recent.splice(index, 1);
  }
}

function enforceLimit() {
  while (open.size > MAX_OPEN) {
    const victim = recent.find((key) => key !== active && !open.get(key)?.dirty);
    if (!victim) {
      return;
    }
    closeModule(victim, { force: true });
  }
}

function renderTabs() {
  if (!tabs) {
    return;
  }
  tabs.replaceChildren(
    ...[...open.values()].map((instance) => {
      const tab = document.createElement('span');
      tab.className = 'lego-shell__tab';
      tab.dataset.key = instance.key;
      tab.toggleAttribute('data-dirty', instance.dirty);
      if (instance.key === active) {
        tab.setAttribute('aria-current', 'page');
      }
      const link = document.createElement('a');
      link.href = instance.url;
      link.textContent = instance.title;
      const close = document.createElement('button');
      close.type = 'button';
      close.textContent = '×';
      close.setAttribute('aria-label', `Cerrar ${instance.title}`);
      close.dataset.legoCloseTab = instance.key;
      tab.append(link, close);
      return tab;
    }),
  );
}

/** Links that the shell handles: same origin, no modifiers, not core endpoints. */
function shouldIntercept(event, link) {
  if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
    return false;
  }
  if (link.target || link.hasAttribute('download') || link.dataset.legoNative !== undefined) {
    return false;
  }
  const url = new URL(link.href, location.origin);
  return url.origin === location.origin && !/^\/(api|_lego|_modules|login|logout)(\/|$)/.test(url.pathname);
}

function applyTheme(theme) {
  document.documentElement.classList.remove('light', 'dark');
  if (theme === 'light' || theme === 'dark') {
    document.documentElement.classList.add(theme);
  }
}

function storedTheme() {
  try {
    return localStorage.getItem('lego:theme');
  } catch {
    return null;
  }
}

function toggleTheme() {
  const dark = document.documentElement.classList.contains('dark')
    || (!document.documentElement.classList.contains('light') && matchMedia('(prefers-color-scheme: dark)').matches);
  const theme = dark ? 'light' : 'dark';
  applyTheme(theme);
  try {
    localStorage.setItem('lego:theme', theme);
  } catch {
    // Private mode: the choice lasts for this page only.
  }
}

function boot() {
  applyTheme(storedTheme());
  outlet = document.querySelector('[data-lego-outlet]');
  tabs = document.querySelector('[data-lego-tabs]');
  document.querySelector('[data-lego-theme-toggle]')?.addEventListener('click', toggleTheme);
  if (!outlet) {
    return;
  }

  const first = outlet.querySelector('[data-lego-screen]');
  if (first) {
    const key = first.dataset.legoKey;
    const instance = {
      key,
      url: first.dataset.legoUrl ?? location.pathname + location.search,
      title: first.dataset.legoTitle ?? 'Lego',
      root: first,
      dirty: false,
      state: 'ready',
      controller: null,
      cleanups: [],
      ready: Promise.resolve(),
    };
    open.set(key, instance);
    mount(instance);
    window.history.replaceState({ key }, '', instance.url);
    activate(key);
  }

  document.addEventListener('click', (event) => {
    const closeTab = event.target.closest('[data-lego-close-tab]');
    if (closeTab) {
      event.preventDefault();
      closeModule(closeTab.dataset.legoCloseTab);
      return;
    }
    const link = event.target.closest('a[href]');
    if (link && shouldIntercept(event, link)) {
      event.preventDefault();
      openModule(link.href);
    }
  });

  window.addEventListener('popstate', () => openModule(location.href, { history: false }));

  window.addEventListener('beforeunload', (event) => {
    if ([...open.values()].some((instance) => instance.dirty)) {
      event.preventDefault();
    }
  });

  // After a change, open lists of that module show fresh data (detail.list is the list URL).
  bus.addEventListener(EVENTS.DATA_CHANGED, (event) => {
    const listKey = event.detail.list ? keyFromUrl(event.detail.list) : null;
    const instance = listKey ? open.get(listKey) : null;
    if (instance && instance.key !== event.detail.from) {
      reload(instance);
    }
  });

  document.documentElement.dataset.legoReady = '';
}

boot();
