
let sidebarResizing = false;
        let resizeStartX = 0;
        let resizeStartWidth = 0;
        
        function startSidebarResize(e) {
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar || sidebar.classList.contains('close')) return;
            
            sidebarResizing = true;342
            resizeStartX = e.clientX;
            resizeStartWidth = sidebar.offsetWidth;
            
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            
            // Add visual feedback
            const handle = e.target;
            handle.style.width = '8px !important';
            
            e.preventDefault();
            e.stopPropagation();
        }
        
        document.addEventListener('mousemove', function(e) {
            if (!sidebarResizing) return;
            
            const sidebar = document.querySelector('.sidebar');
            const contentShade = document.getElementById('content-sidebar-shade');
            if (!sidebar) return;
            
            const newWidth = resizeStartWidth + (e.clientX - resizeStartX);
            
            // Constrain between 200px and 400px
            if (newWidth >= 200 && newWidth <= 400) {
                sidebar.style.width = newWidth + 'px';
                
                // Update CSS variable for other elements
                const widthRem = newWidth / 16;
                document.documentElement.style.setProperty('--sidebar-width', widthRem + 'rem');
                
                // Update content shade if it exists
                if (contentShade) {
                    contentShade.style.minWidth = newWidth + 'px';
                }
            }
        });
        
        document.addEventListener('mouseup', function() {
            if (sidebarResizing) {
                sidebarResizing = false;
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                
                // Reset handle appearance
                const handle = document.querySelector('.sidebar-resize-handle');
           
                
                // Save the new width using unified storage manager
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && window.storageManager) {
                    window.storageManager.setSidebarWidth(sidebar.offsetWidth);
                } else if (sidebar) {
                    // Fallback to localStorage if storage manager not available
                    localStorage.setItem('lego_sidebar_width', sidebar.offsetWidth);
                }
            }
        });
        
        // Load saved width on page load
            let savedWidth;
            
            // Try to get width from unified storage manager first
            if (window.storageManager) {
                savedWidth = window.storageManager.getSidebarWidth();
            } else {
                // Fallback to localStorage
                savedWidth = localStorage.getItem('lego_sidebar_width');
            }
            
            if (savedWidth && savedWidth >= 200 && savedWidth <= 400) {
                const sidebar = document.querySelector('.sidebar');
                const contentShade = document.getElementById('content-sidebar-shade');
                
                if (sidebar) {
                    const widthRem = savedWidth / 16;
                    document.documentElement.style.setProperty('--sidebar-width', widthRem + 'rem');
                    sidebar.style.width = savedWidth + 'px';
                    
                    if (contentShade) {
                        contentShade.style.minWidth = savedWidth + 'px';
                    }
                }
            }
        
        // Hide handle when sidebar is collapsed
        function updateResizeHandleVisibility() {
            const sidebar = document.querySelector('.sidebar');
            const handle = document.querySelector('.sidebar-resize-handle');
            
            if (sidebar && handle) {
                if (sidebar.classList.contains('close')) {
                    handle.style.display = 'none';
                } else {
                    handle.style.display = 'block';
                }
            }
        }
        
        // Watch for sidebar toggle
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    updateResizeHandleVisibility();
                }
            });
        });
        
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                observer.observe(sidebar, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }

        // Agregar event listener al resize handle
        const resizeHandle = document.querySelector('.sidebar-resize-handle');
        if (resizeHandle) {
            resizeHandle.addEventListener('mousedown', startSidebarResize);
        } else {
            console.warn('No se encontró el resize handle');
        }