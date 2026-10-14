/**
 * Behavior of [data-lego="table"]: search, sorting and pagination change the
 * query of this same screen (replaceState, no new history entry, decision C8)
 * and reload it from the server. Links to other screens are left to the shell.
 */
export function mount(table, ctx) {
  const { signal } = ctx;
  const base = () => new URL(ctx.url, location.origin).pathname;

  table.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return;
    }
    const url = new URL(link.href, location.origin);
    if (url.pathname !== base()) {
      return;
    }
    event.preventDefault();
    event.stopPropagation();
    ctx.navigate(url.pathname + url.search, { replace: true });
  }, { signal });

  for (const form of table.querySelectorAll('form[method="get"]')) {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const params = new URLSearchParams();
      for (const [name, value] of new FormData(form)) {
        if (value !== '') {
          params.set(name, value);
        }
      }
      const query = params.toString();
      ctx.navigate(base() + (query ? '?' + query : ''), { replace: true });
    }, { signal });
  }
}
