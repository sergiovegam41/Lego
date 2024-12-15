export const HOST_NAME = `${window.location.protocol}//${window.location.host}`;
    
export const MENU_LIST = [
   {
       "name" : "Home",
       "icon" : "home-outline",
       "url" : HOST_NAME + '/resourses/home' 
   },

    {
        "name" : "Departamentos",
        "icon" : "map-outline",
        "url"  : HOST_NAME + '/resourses/cruds/essays' 
    },
    {
        "name" : "Usuarios",
        "icon" : "person-circle-outline",
        "url"  : HOST_NAME + '/resourses/cruds/users/' 
    },

];