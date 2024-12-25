module.exports = {
  content: [
    "./public/**/*.php",      // Escanea todos los archivos PHP dentro de la carpeta 'public'
    "./Views/**/*.php",       // Escanea los archivos PHP en 'Views' (que parece ser donde generas las vistas)
    "./assets/js/**/*.js",    // Escanea los archivos JS en la carpeta 'assets/js'
    "./Core/**/*.php",        // Escanea lógica PHP dentro de 'Core'
    "./Routes/**/*.php",      // Escanea las rutas que podrían incluir clases o nombres dinámicos
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};
