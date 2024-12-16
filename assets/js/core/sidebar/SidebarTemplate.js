
import { HOST_NAME, MENU_LIST } from './../config.js';

export class Sidebar extends HTMLElement {

  constructor(){

      let FINAL_MENU_LIST = "";


      let i = 0;
      MENU_LIST.forEach(element => {
          FINAL_MENU_LIST += `

          <li id="drag" class="nav-link" data-id="${i}">
              <a href="${element.url}">
                  <ion-icon class ='icon' name="${element.icon}"></ion-icon>
                  <span class="text nav-text">${element.name}</span>
              </a>
          </li>

      `

      i+=1;
      });

      super();
      const template = document.createElement('template');
      template.innerHTML = `     
  
      <link rel="stylesheet" href="${ HOST_NAME + '/assets/css/core/sidebar/SidebarStyle.css' }">
      <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>


      
      <nav class="sidebar">
      <header>
          <div class="image-text">
              <span class="image">
                  <img class="user-image" src="/assets/images/logo.png" alt="">
                  <!-- <p>Sergio Vega</p> -->
              </span>

              <div class="text logo-text">
                  <span class="name">Lego</span>
                  <span class="profession">Freamework</span>
              </div>
              
          </div>

          <i class='bx bx-chevron-right toggle'></i>
      </header>

      <div class="menu-bar">


          <hr>
  
          <div class="menu" id="sidebar_menu">

              <li class="search-box">
                  <ion-icon class ='icon' name="search-outline"></ion-icon>
                  <input type="text" placeholder="Search" id="search-menu">
              </li>

              <ul class="menu-links" id="menu-links">
                 

                ${ FINAL_MENU_LIST }


              </ul>
          </div>

          <div class="bottom-content">

                        <li class="" id="theme-toggle">
                  <a >
                      <ion-icon class ='icon'  name="moon-outline"></ion-icon>
                      <span class="text nav-text">Theme</span>
                  </a>
              </li>
          <hr>
              <li class="">
                  <a href="${   HOST_NAME    }">
                      <ion-icon class ='icon'  name="log-out-outline"></ion-icon>
                      <span class="text nav-text">Logout</span>
                  </a>
              </li>



           

              
          </div>
      </div>

  </nav>
  
 

  

  `;

    document.querySelector('body').appendChild(template.content);


  }
}