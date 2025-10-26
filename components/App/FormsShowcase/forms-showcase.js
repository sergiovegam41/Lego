/**
 * FormsShowcaseComponent - Lógica de demostración
 *
 * RESPONSABILIDADES:
 * - Manejar envíos de formularios demo
 * - Mostrar resultados de validación
 * - Ejemplos de uso de APIs
 */


console.log('🔵 [FormsShowcase] Inißcializando...');
(function() {

    // Manejar formulario de contacto
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('lego:form-submit', async (e) => {
            console.log('📧 [Formulario de Contacto] Datos:', e.detail.data);

            // Simular envío async
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Mostrar éxito
            window.LegoForm.showSuccess('contact-form', '¡Mensaje enviado exitosamente! Te responderemos pronto.');

            // Resetear formulario después de 3 segundos
            setTimeout(() => {
                window.LegoForm.reset('contact-form');
            }, 3000);
        });
    }

    // Manejar formulario de registro
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('lego:form-submit', async (e) => {
            console.log('👤 [Formulario de Registro] Datos:', e.detail.data);

            // Simular envío async
            await new Promise(resolve => setTimeout(resolve, 2000));

            // Mostrar éxito
            window.LegoForm.showSuccess('register-form', '¡Cuenta creada exitosamente! Bienvenido a Lego.');

            // Resetear formulario después de 3 segundos
            setTimeout(() => {
                window.LegoForm.reset('register-form');
            }, 3000);
        });
    }

    // Demostrar eventos de componentes individuales
    document.addEventListener('lego:input-change', (e) => {
        console.log('📝 [Input Change]', e.detail);
    });

    document.addEventListener('lego:select-change', (e) => {
        console.log('🔽 [Select Change]', e.detail);
    });

    document.addEventListener('lego:checkbox-change', (e) => {
        console.log('☑️ [Checkbox Change]', e.detail);
    });

    document.addEventListener('lego:radio-change', (e) => {
        console.log('🔘 [Radio Change]', e.detail);
    });

    document.addEventListener('lego:textarea-change', (e) => {
        console.log('📄 [TextArea Change]', e.detail);
    });

    document.addEventListener('lego:button-click', (e) => {
        console.log('🔘 [Button Click]', e.detail);
    });

    // Ejemplo de uso programático de APIs
    console.log('🎯 Ejemplos de APIs disponibles:');
    console.log('- window.LegoInputText.getValue("input-id")');
    console.log('- window.LegoInputText.setValue("input-id", "valor")');
    console.log('- window.LegoInputText.setError("input-id", "mensaje")');
    console.log('- window.LegoSelect.getValue("select-id")');
    console.log('- window.LegoCheckbox.isChecked("checkbox-id")');
    console.log('- window.LegoRadio.getSelectedValue("group-name")');
    console.log('- window.LegoButton.setLoading("button-id", true)');
    console.log('- window.LegoForm.validate("form-id")');
    console.log('- window.LegoForm.getData("form-id")');

    console.log('✅ [FormsShowcase] Inicialización completa');
})();
