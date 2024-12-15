

/**
 * Carga y ejecuta scripts dinámicamente
 * @param {string[]} scripts - Array de URLs de scripts
 * @returns {Promise<void[]>} Promise que resuelve cuando todos los scripts están cargados
 */
export async function _loadModules(scripts) {
    if (!scripts?.length) return;

    const loadScript = async (scriptUrl) => {
        try {
            const response = await fetch(scriptUrl);
            const code = await response.text();
            return executeCode(code);
        } catch (error) {
            console.error(`Error loading module at ${scriptUrl}:`, error);
            throw error;
        }
    };

    return Promise.all(scripts.map(loadScript));
}

/**
 * Transforma código JSX a JavaScript
 * @param {string} code - Código JSX
 * @returns {string} Código JavaScript transformado
 */
function transformJSX(code) {
    if (typeof Babel === 'undefined') {
        throw new Error('Babel is not loaded');
    }

    try {
        const result = Babel.transform(code, {
            presets: ['react'],
            filename: 'module.jsx',
            sourceType: 'script'
        });

        return result.code;
    } catch (error) {
        console.error('Error transforming JSX:', error);
        throw error;
    }
}

/**
 * Ejecuta código JavaScript en un contexto seguro
 * @param {string} code - Código a ejecutar
 * @returns {any} Resultado de la ejecución
 */
function executeCode(code) {
    try {
        // Crear un contexto con exports
        const contextCode = `
           
            ${code}
        
        `;
        return (new Function(contextCode))();
    } catch (error) {
        console.error('Error executing code:', error);
        throw error;
    }
}

/**
 * Carga y ejecuta scripts con argumentos y contexto
 * @param {Object} scripts - Objeto con data (scripts y argumentos) y contexto
 * @returns {Promise<void[]>} Promise que resuelve cuando todos los scripts están cargados
 */
export async function _loadModulesWithArguments(scripts) {
    if (!scripts?.data?.length) return;

    const loadScriptWithContext = async (scriptData) => {
        try {
            const { path, arg } = scriptData[0];
            const response = await fetch(path);
            const code = await response.text();
            
            // Si es JSX, transformarlo primero
            let processedCode = path.endsWith('.jsx') 
                ? transformJSX(code)
                : code;
            
            // Reemplazar {CONTEXT} con los datos reales
            const contextData = JSON.stringify({
                context: scripts.context,
                arg
            });
            
            processedCode = processedCode.replace('{CONTEXT}', contextData);
            
            // Ejecutar el código
            return executeCode(processedCode);
        } catch (error) {
            console.error('Error loading module with arguments:', error);
            throw error;
        }
    };

    return Promise.all(scripts.data.map(loadScriptWithContext));
}
