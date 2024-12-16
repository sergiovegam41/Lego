import { Sidebar } from './SidebarTemplate.js';
import { MENU_LIST } from './../config.js';
// import Sortable from 'sortablejs';
// import Sortable from 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js';
import 'https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js';



  export function ready() {

    
  console.log("siee")



      //deff web componet
      window.customElements.define('side-bar',Sidebar);

      //deff events to toggle sidebar
      addEventForToggle();

      //This line add funtion search moduls of aplications
      AddSearchFunction();



      let lista = document.getElementById('menu-links');

      console.log("Aui")
      console.log(lista)
      // console.log(Sortable)

      Sortable.create(lista, {
        animation: 150,
        chosenClass: "seleccionado",
        // ghostClass: "fantasma"
        dragClass: "drag",
      
        onEnd: () => {
          console.log('Se inserto un elemento');
        },
        group: "lista-personas",
        store: {
          // Guardamos el orden de la lista
          set: (sortable) => {
            const orden = sortable.toArray();
            localStorage.setItem(sortable.options.group.name, orden.join('|'));
          },
      
          // Obtenemos el orden de la lista
          get: (sortable) => {
            const orden = localStorage.getItem(sortable.options.group.name);
            return orden ? orden.split('|') : [];
          }
        }
      });
    
        
  }

  function AddSearchFunction(){

      document.querySelector('#search-menu').addEventListener("change", function(event){
        console.log(event.target.value)

        let search = MENU_LIST.filter(item => item.name.includes((event.target.value.toLowerCase()).replace(/^\w/, (c) => c.toUpperCase())) );
        console.log(search);

        voidMenu();

        fillMenubySearch(search);
        

      } );

      
  }

  function fillMenubySearch(search){

    let FINAL_MENU_LIST_SEARCH = [];
    search.forEach(element => {
      FINAL_MENU_LIST_SEARCH += `

      <li class="nav-link">
          <a href="${element.url}">
              <ion-icon class ='icon' name="${element.icon}"></ion-icon>
              <span class="text nav-text">${element.name}</span>
          </a>
      </li>
  
      `
    });

    let list = document.getElementById('menu-links');
    list.innerHTML = search.length == 0? "<p style='font-size: 15px; '>" + selectRamdomExcuse()+"</p>" :FINAL_MENU_LIST_SEARCH

  }


  function selectRamdomExcuse(){
    var numExcuse = Math.floor((Math.random() * 10) + 1); 
    var numFace = Math.floor((Math.random() * 10) + 1);

    let listExcuses = [
      "No pudimos encontrar lo que buscas",
      "Lo que buscas no lo encontraras aqui",
      "No enconramos nada..",
      "Sigue intentando",
      "Intenta ser mas franco",
      "Al parecer no esta eso que buscas",
      "Aqui no esta",
      "Nada...",
      "Vacio... como tu corazon",
      "Dice ella que no esta",
    ];

    let listFaces = [
      '^_^',
      'O.O',
      '*^____^*',
      '`(*>﹏<*)′',
      '^_-',
      '(‾◡◝)',
      ':|',
      '(*^_^*)',
      '＞﹏＜',
      '(^///^)',
    ];

    return listExcuses[numExcuse-1] + " <br> " + listFaces[numFace-1]
  }

  function voidMenu(){
    let ul = document.getElementById('menu-links');

    while (ul.firstChild){
      ul.removeChild(ul.firstChild);
    };
    
  }


  function addEventForToggle() {
    const body = document.querySelector('body'),
    sidebar = body.querySelector('nav'),
    toggle = body.querySelector(".toggle"),
    searchBtn = body.querySelector(".search-box"),
    contentViewer = document.querySelector('#principal-content-viwer'),
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
            sidebarShade.style= " min-width: 260px";
        }
    })

    searchBtn.addEventListener("click" , () => {
        sidebar.classList.remove("close");
        
        // Al abrir el sidebar con el botón de búsqueda
        // contentViewer.style.width = '86.4%';
        sidebarShade.style= " min-width: 100px";
    })
  }




