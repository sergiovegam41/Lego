
  export function activeMenu() {

      document.addEventListener('DOMContentLoaded', () => {

        addEventForToggle();

        // const parentItems = document.querySelectorAll('.nav-link:has(.childs)');
    
        // parentItems.forEach(item => {
        //     item.addEventListener('click', function(e) {
        //         // Prevenir que el click se propague si hay un botón de cierre
        //         if (!e.target.closest('.close-btn')) {
        //             // Toggle de la clase active en el elemento padre
        //             this.classList.toggle('active');
                    
        //             // Encontrar el elemento .childs dentro de este item
        //             const childContainer = this.querySelector('.childs');
        //             if (childContainer) {
        //                 childContainer.classList.toggle('active');
        //             }
        //         }
        //     });
        // });


        const toggleButton = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('theme');
      
        // Aplica el tema guardado al cargar la página
        if (currentTheme === 'dark') {
          document.querySelector('body').classList.add('dark');
        }
      
        toggleButton.addEventListener('click', () => {

          const isDarkMode = document.querySelector('body').classList.toggle('dark');
          localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
          document.querySelector('#theme-toggle ion-icon').name= isDarkMode ? 'sunny-outline':'moon-outline'
          // moon-outline
          // sunny-outline

        });

        // Add click handlers for parent menu items
        document.querySelectorAll('.menu-parent').forEach(parent => {
          parent.querySelector('a').addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle active class
            parent.classList.toggle('active');
            
            // Close other open menus
            document.querySelectorAll('.menu-parent.active').forEach(item => {
              if (item !== parent) {
                item.classList.remove('active');
              }
            });
          });
        });
      });
    
        
  }

  function addEventForToggle(){

    const body = document.querySelector('body'),
    sidebar = body.querySelector('nav'),
    toggle = body.querySelector(".toggle"),
    searchBtn = body.querySelector(".search-box"),
    sidebarShade = document.querySelector('#content-sidebar-shade');

    toggle.addEventListener("click" , () => {
        sidebar.classList.toggle("close");

        if(sidebar.classList.contains("close")){
            // Cuando el sidebar está cerrado
            // contentViewer.style.width = '95%';
            sidebarShade.style= " min-width: 100px";
        } else {
            // Cuando el sidebar está abierto
            // contentViewer.style.width = '86.4%';
            sidebarShade.style= " min-width: 300px";
        }
    })

    searchBtn.addEventListener("click" , () => {
        sidebar.classList.remove("close");
        
        // Al abrir el sidebar con el botón de búsqueda
        // contentViewer.style.width = '86.4%';
        sidebarShade.style= " min-width: 300px";
    })
  }




 export function toggleSubMenu(element) {
    const submenu = element.nextElementSibling;
    const icon = element.querySelector("ion-icon");

    if (submenu.style.display === "block") {
        submenu.style.display = "none";
        icon.setAttribute("name", "chevron-forward-outline");
    } else {
        submenu.style.display = "block";
        icon.setAttribute("name", "chevron-down-outline");
    }
}
