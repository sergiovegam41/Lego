/**
 * Breadcrumb Component
 *
 * Manages breadcrumb navigation updates based on ModuleStore state
 */

if (typeof window.legoBreadcrumb === 'undefined') {
    window.legoBreadcrumb = {
        /**
         * Update breadcrumb items
         * @param {Array} items - Array of {label, href} objects
         */
        update: function(items) {
            const breadcrumb = document.getElementById('lego-breadcrumb');
            if (!breadcrumb) return;

            const list = breadcrumb.querySelector('.lego-breadcrumb__list');
            if (!list) return;

            // Clear existing items
            list.innerHTML = '';

            // Render new items
            items.forEach((item, index) => {
                const isLast = (index === items.length - 1);

                if (isLast) {
                    // Last item - active state
                    const span = document.createElement('span');
                    span.className = 'lego-breadcrumb__item lego-breadcrumb__item--active';
                    span.textContent = item.label;
                    list.appendChild(span);
                } else {
                    // Link item
                    const link = document.createElement('a');
                    link.className = 'lego-breadcrumb__item lego-breadcrumb__item--link';
                    link.href = item.href || '#';
                    link.textContent = item.label;
                    list.appendChild(link);

                    // Separator
                    const separator = document.createElement('span');
                    separator.className = 'lego-breadcrumb__separator';
                    separator.textContent = '/';
                    list.appendChild(separator);
                }
            });
        }
    };
}
