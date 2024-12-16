export let HOST_NAME = `${window.location.protocol}//${window.location.host}`;
    
    export let MENU_LIST = [

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

        {
            "name" : "Configuracion",
            "icon" : "cog-outline",
            "childs": [
                {
                    "name" : "Usuarios",
                    "icon" : "person-circle-outline",
                    "url"  : HOST_NAME + '/resourses/cruds/users/' 
                },
                {
                    "name" : "Usuarios",
                    "icon" : "person-circle-outline",
                    "url"  : HOST_NAME + '/resourses/cruds/users/' 
                },
            ]
            
        },

    ];