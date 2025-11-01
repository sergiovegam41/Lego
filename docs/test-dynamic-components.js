/**
 * Script de Pruebas - Sistema de Componentes Dinámicos
 *
 * INSTRUCCIONES:
 * 1. Abrir la consola del navegador (F12)
 * 2. Copiar y pegar este script completo
 * 3. Ejecutar: await testDynamicComponents()
 * 4. Ver resultados en consola
 */

async function testDynamicComponents() {
    console.log('\n===========================================');
    console.log('🧪 PRUEBAS - Sistema de Componentes Dinámicos');
    console.log('===========================================\n');

    const results = {
        passed: 0,
        failed: 0,
        tests: []
    };

    // Helper para registrar resultados
    function logTest(name, passed, details) {
        const icon = passed ? '✅' : '❌';
        console.log(`${icon} ${name}`);
        if (details) {
            console.log('  Detalles:', details);
        }
        results.tests.push({ name, passed, details });
        if (passed) results.passed++;
        else results.failed++;
    }

    // ========================================
    // NIVEL 1: VERIFICACIÓN DE INICIALIZACIÓN
    // ========================================
    console.log('\n📦 Nivel 1: Verificación de Inicialización\n');

    // Test 1.1: window.lego existe
    const test1_1 = typeof window.lego !== 'undefined';
    logTest(
        'Test 1.1: window.lego existe',
        test1_1,
        { exists: test1_1, type: typeof window.lego }
    );

    // Test 1.2: window.lego.components existe
    const test1_2 = typeof window.lego?.components !== 'undefined';
    logTest(
        'Test 1.2: window.lego.components existe',
        test1_2,
        { exists: test1_2, type: typeof window.lego?.components }
    );

    // Test 1.3: window.lego.components tiene métodos correctos
    const test1_3 = test1_2 &&
        typeof window.lego.components.get === 'function' &&
        typeof window.lego.components.renderBatch === 'function' &&
        typeof window.lego.components.listComponents === 'function';
    logTest(
        'Test 1.3: Métodos disponibles (get, renderBatch, listComponents)',
        test1_3,
        {
            hasGet: typeof window.lego?.components?.get === 'function',
            hasRenderBatch: typeof window.lego?.components?.renderBatch === 'function',
            hasListComponents: typeof window.lego?.components?.listComponents === 'function'
        }
    );

    if (!test1_2) {
        console.log('\n❌ ERROR CRÍTICO: window.lego.components no existe');
        console.log('Verificar que base-lego-framework.js se cargó correctamente');
        return results;
    }

    // ========================================
    // NIVEL 2: PRUEBAS DE API (Backend)
    // ========================================
    console.log('\n🌐 Nivel 2: Pruebas de API (Backend)\n');

    // Test 2.1: GET /api/components/list
    try {
        console.log('Ejecutando: GET /api/components/list...');
        const components = await window.lego.components.listComponents();
        const test2_1 = Array.isArray(components) && components.length > 0;
        logTest(
            'Test 2.1: Listar componentes registrados',
            test2_1,
            { components, count: components.length }
        );

        if (!components.includes('icon-button')) {
            logTest(
                'Test 2.1b: icon-button está registrado',
                false,
                { registered: components }
            );
        } else {
            logTest(
                'Test 2.1b: icon-button está registrado',
                true,
                { found: true }
            );
        }
    } catch (error) {
        logTest(
            'Test 2.1: Listar componentes registrados',
            false,
            { error: error.message, stack: error.stack }
        );
    }

    // Test 2.2: Renderizado único
    try {
        console.log('Ejecutando: Renderizado único...');
        const html = await window.lego.components
            .get('icon-button')
            .params({
                icon: 'create-outline',
                variant: 'primary',
                title: 'Test Button'
            });

        const test2_2 = typeof html === 'string' && html.includes('button');
        logTest(
            'Test 2.2: Renderizado único',
            test2_2,
            {
                type: typeof html,
                length: html?.length,
                isHTML: html?.includes('button'),
                sample: html?.substring(0, 100)
            }
        );
    } catch (error) {
        logTest(
            'Test 2.2: Renderizado único',
            false,
            { error: error.message }
        );
    }

    // Test 2.3: Renderizado batch
    try {
        console.log('Ejecutando: Renderizado batch...');
        const buttons = await window.lego.components
            .get('icon-button')
            .params([
                { icon: 'create-outline', variant: 'primary', title: 'Editar' },
                { icon: 'trash-outline', variant: 'danger', title: 'Eliminar' },
                { icon: 'eye-outline', variant: 'ghost', title: 'Ver' }
            ]);

        const test2_3 = Array.isArray(buttons) &&
                        buttons.length === 3 &&
                        buttons.every(b => typeof b === 'string' && b.includes('button'));

        logTest(
            'Test 2.3: Renderizado batch (3 botones)',
            test2_3,
            {
                isArray: Array.isArray(buttons),
                count: buttons?.length,
                expected: 3,
                allStrings: buttons?.every(b => typeof b === 'string'),
                allHaveButton: buttons?.every(b => b.includes('button')),
                samples: buttons?.map(b => b.substring(0, 50))
            }
        );
    } catch (error) {
        logTest(
            'Test 2.3: Renderizado batch',
            false,
            { error: error.message }
        );
    }

    // Test 2.4: Preservación de orden
    try {
        console.log('Ejecutando: Test de orden...');
        const buttons = await window.lego.components
            .get('icon-button')
            .params([
                { icon: 'create-outline', title: 'Primero' },
                { icon: 'trash-outline', title: 'Segundo' },
                { icon: 'eye-outline', title: 'Tercero' }
            ]);

        const test2_4 = buttons[0].includes('create-outline') &&
                        buttons[1].includes('trash-outline') &&
                        buttons[2].includes('eye-outline');

        logTest(
            'Test 2.4: Preservación de orden en batch',
            test2_4,
            {
                order1: buttons[0]?.includes('create-outline'),
                order2: buttons[1]?.includes('trash-outline'),
                order3: buttons[2]?.includes('eye-outline')
            }
        );
    } catch (error) {
        logTest(
            'Test 2.4: Preservación de orden',
            false,
            { error: error.message }
        );
    }

    // ========================================
    // NIVEL 3: PRUEBAS DE INTEGRACIÓN
    // ========================================
    console.log('\n🔗 Nivel 3: Pruebas de Integración\n');

    // Test 3.1: Renderizar botones en DOM
    try {
        console.log('Ejecutando: Renderizado en DOM...');
        const testContainer = document.createElement('div');
        testContainer.id = 'test-dynamic-components-container';
        testContainer.style.cssText = 'position:fixed;top:10px;right:10px;padding:16px;background:white;border:2px solid #4f46e5;border-radius:8px;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.15);';

        const title = document.createElement('h3');
        title.textContent = 'Test Dynamic Components';
        title.style.cssText = 'margin:0 0 12px 0;color:#1f2937;font-size:14px;font-weight:600;';
        testContainer.appendChild(title);

        const buttonsContainer = document.createElement('div');
        buttonsContainer.style.cssText = 'display:flex;gap:8px;';

        const buttons = await window.lego.components
            .get('icon-button')
            .params([
                { icon: 'create-outline', variant: 'primary', title: 'Editar Test' },
                { icon: 'trash-outline', variant: 'danger', title: 'Eliminar Test' },
                { icon: 'eye-outline', variant: 'ghost', title: 'Ver Test' }
            ]);

        buttonsContainer.innerHTML = buttons.join('');
        testContainer.appendChild(buttonsContainer);

        const closeBtn = document.createElement('button');
        closeBtn.textContent = 'Cerrar Test';
        closeBtn.style.cssText = 'margin-top:12px;padding:6px 12px;background:#ef4444;color:white;border:none;border-radius:4px;cursor:pointer;font-size:12px;width:100%;';
        closeBtn.onclick = () => testContainer.remove();
        testContainer.appendChild(closeBtn);

        document.body.appendChild(testContainer);

        const test3_1 = buttonsContainer.children.length === 3;
        logTest(
            'Test 3.1: Renderizar botones en DOM',
            test3_1,
            {
                rendered: test3_1,
                buttonsCount: buttonsContainer.children.length,
                location: 'Esquina superior derecha (visible 5 segundos)'
            }
        );

        // Auto-remover después de 5 segundos
        setTimeout(() => testContainer.remove(), 5000);

    } catch (error) {
        logTest(
            'Test 3.1: Renderizar en DOM',
            false,
            { error: error.message }
        );
    }

    // Test 3.2: Verificar estilos CSS de IconButton
    try {
        const testBtn = document.createElement('div');
        testBtn.innerHTML = await window.lego.components
            .get('icon-button')
            .params({ icon: 'create-outline', variant: 'primary' });

        document.body.appendChild(testBtn);
        const button = testBtn.querySelector('button');
        const styles = window.getComputedStyle(button);

        const test3_2 = button.classList.contains('lego-icon-button') &&
                        styles.display !== 'none';

        logTest(
            'Test 3.2: Estilos CSS aplicados',
            test3_2,
            {
                hasClass: button.classList.contains('lego-icon-button'),
                display: styles.display,
                classList: Array.from(button.classList)
            }
        );

        document.body.removeChild(testBtn);
    } catch (error) {
        logTest(
            'Test 3.2: Verificar estilos CSS',
            false,
            { error: error.message }
        );
    }

    // ========================================
    // RESUMEN FINAL
    // ========================================
    console.log('\n===========================================');
    console.log('📊 RESUMEN DE PRUEBAS');
    console.log('===========================================\n');

    console.log(`✅ Pruebas exitosas: ${results.passed}`);
    console.log(`❌ Pruebas fallidas: ${results.failed}`);
    console.log(`📝 Total: ${results.tests.length}`);

    const successRate = ((results.passed / results.tests.length) * 100).toFixed(1);
    console.log(`\n📈 Tasa de éxito: ${successRate}%`);

    if (results.failed > 0) {
        console.log('\n⚠️  Pruebas fallidas:');
        results.tests
            .filter(t => !t.passed)
            .forEach(t => console.log(`  - ${t.name}`));
    }

    console.log('\n===========================================\n');

    return results;
}

// Información de uso
console.log('\n📘 Para ejecutar las pruebas, ejecuta en la consola:\n');
console.log('  await testDynamicComponents()\n');
