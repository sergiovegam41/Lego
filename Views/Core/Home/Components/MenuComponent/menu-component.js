let context = {CONTEXT}

console.log(context)

// ===== SIDEBAR RESIZE FUNCTIONALITY =====
console.log('Script loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    setTimeout(function() {
        console.log('Starting resize setup...');
        setupResize();
    }, 1000);
});

function setupResize() {
    console.log('Looking for elements...');
    
    const sidebar = document.querySelector('.sidebar');
    const handle = document.querySelector('.sidebar-resize-handle');
    
    console.log('Found elements:', {
        sidebar: sidebar,
        handle: handle,
        sidebarExists: !!sidebar,
        handleExists: !!handle
    });
    
    if (!sidebar) {
        console.error('SIDEBAR NOT FOUND!');
        return;
    }
    
    if (!handle) {
        console.error('HANDLE NOT FOUND!');
        return;
    }
    
    console.log('Both elements found, setting up events...');
    console.log('Handle position:', handle.getBoundingClientRect());
    console.log('Handle computed style:', window.getComputedStyle(handle));
    
    // Test if element is actually clickable
    handle.onclick = function(e) {
        console.log('ONCLICK TRIGGERED!');
        alert('Handle clicked via onclick!');
        e.stopPropagation();
        return false;
    };
    
    // Try different event approaches
    handle.onmousedown = function(e) {
        console.log('ONMOUSEDOWN TRIGGERED!');
        alert('Mousedown via onmousedown!');
        e.stopPropagation();
        return false;
    };
    
    // Check what's actually under the mouse
    document.addEventListener('click', function(e) {
        const elementUnderMouse = document.elementFromPoint(e.clientX, e.clientY);
        console.log('Clicked element:', elementUnderMouse);
        
        if (elementUnderMouse === handle) {
            console.log('CLICKED ON HANDLE!');
            alert('Clicked on handle detected!');
        }
    });
    
    // Force pointer events
    handle.style.pointerEvents = 'all';
    handle.style.userSelect = 'none';
    
    let isResizing = false;
    
    // Simple resize logic
    let startX = 0;
    let startWidth = 0;
    
    function startResize(e) {
        console.log('Starting resize...');
        isResizing = true;
        startX = e.clientX;
        startWidth = sidebar.offsetWidth;
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
    }
    
    function doResize(e) {
        if (!isResizing) return;
        
        const newWidth = startWidth + (e.clientX - startX);
        if (newWidth >= 200 && newWidth <= 500) {
            sidebar.style.width = newWidth + 'px';
            console.log('Resizing to:', newWidth);
        }
    }
    
    function endResize() {
        if (isResizing) {
            console.log('Ending resize');
            isResizing = false;
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        }
    }
    
    // Try multiple event binding methods
    if (handle.addEventListener) {
        handle.addEventListener('mousedown', startResize, true);
    }
    
    document.addEventListener('mousemove', doResize);
    document.addEventListener('mouseup', endResize);
    
    console.log('Setup complete!');
}