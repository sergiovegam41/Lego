
function loadModules(scripts ) {

scripts.forEach(script => {
  fetch(script).then(response => {
    if (response.ok) {
      response.text().then(scriptText => {
      eval(scriptText);
      });
    } else {
      // Manejar el error de carga del script.
    }
    });
});

}

function loadModulesWithArguments( scripts ) {

scripts.data.forEach(script => {

  fetch(script[0].path).then(response => {
    if (response.ok) {
      
      response.text().then(scriptText => {

        eval(scriptText.replace("{CONTEXT}", JSON.stringify({
          "context": scripts.context,
          "arg": script[0].arg
        })));

      });

    } 
  });
});

}

