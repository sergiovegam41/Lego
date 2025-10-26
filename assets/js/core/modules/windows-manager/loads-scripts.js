

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
            console.error(`Error loading js component at ${scriptUrl}:`, error);
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
 * Ejecuta código de forma segura, aislado en su propio contexto
 * @param {string} code - Código JavaScript a ejecutar
 * @param {string} moduleName - Nombre del módulo para logging
 */
function executeCodeSafely(code, moduleName) {
    try {
        // Crear un contexto aislado para el módulo
        const moduleFunction = new Function(`
            try {
                // Scope aislado del módulo
                (function() {
                    ${code}
                })();
            } catch (moduleError) {
                console.error('🚨 Error in module ${moduleName}:', moduleError);
                console.warn('⚠️  Module ${moduleName} failed but other modules will continue');
                // No re-lanzar el error para no romper otros módulos
            }
        `);
        
        moduleFunction();
        
    } catch (criticalError) {
        console.error(`💥 Critical error loading module ${moduleName}:`, criticalError);
        console.warn('⚠️  Module failed at execution level, skipping...');
    }
}

/**
 * Carga y ejecuta scripts con argumentos y contexto
 * @param {Object} scripts - Objeto con data (scripts y argumentos) y contexto
 * @returns {Promise<void[]>} Promise que resuelve cuando todos los scripts están cargados
 */
export async function _loadModulesWithArguments(scripts) {

    console.log("🚀 _loadModulesWithArguments", scripts);
    if (!scripts?.data?.length) return;

    const loadScriptWithContext = async (scriptData) => {
        const { path, arg } = scriptData[0];
        
        try {
            // console.log(`🧱 Loading module: ${path}`);
            const response = await fetch(path);
            
            if (!response.ok) {
                throw new Error(`Failed to fetch ${path}: ${response.status} ${response.statusText}`);
            }
            
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
            
            // Ejecutar código en un contexto aislado
            executeCodeSafely(processedCode, path);
            // console.log(`✅ Module loaded successfully: ${path}`);
            
            return { success: true, path };
            
        } catch (error) {
            console.error(`❌ Error loading module ${path}:`, error);
            // No propagamos el error - continuamos con otros módulos
            return { error: error.message, path };
        }
    };

    // Usar Promise.allSettled para que si un módulo falla, otros continúen
    const results = await Promise.allSettled(scripts.data.map(loadScriptWithContext));
    
    // Log resumen de carga
    const successful = results.filter(r => r.status === 'fulfilled' && r.value?.success).length;
    const failed = results.filter(r => r.status === 'rejected' || r.value?.error).length;
    
    // console.log(`📊 Module loading summary: ${successful} successful, ${failed} failed`);
    
    return results;
}
