#!/usr/bin/env node

/**
 * Validador de Theming - Script de validación
 *
 * FILOSOFÍA LEGO:
 * Detecta automáticamente errores de theming en archivos CSS.
 *
 * ERRORES COMUNES QUE DETECTA:
 * ❌ @media (prefers-color-scheme: dark) - incorrecto
 * ✅ html.dark - correcto
 *
 * ❌ body.dark - incorrecto (debe ser html.dark)
 * ✅ html.dark - correcto
 *
 * ❌ Colores hardcodeados sin variables
 * ✅ var(--color-primary) - correcto
 *
 * USO:
 * node scripts/validate-theming.js
 * npm run validate:theming
 */

const fs = require('fs');
const path = require('path');
const { glob } = require('glob');

// ═══════════════════════════════════════════════════════════════════
// CONFIGURACIÓN
// ═══════════════════════════════════════════════════════════════════

const RULES = {
    // Regla 1: No usar @media prefers-color-scheme
    NO_MEDIA_PREFERS_COLOR_SCHEME: {
        pattern: /@media\s*\(\s*prefers-color-scheme\s*:/gi,
        message: 'No usar @media (prefers-color-scheme). Usar html.dark y html.light en su lugar.',
        severity: 'error'
    },

    // Regla 2: No usar body.dark/body.light
    NO_BODY_THEME_CLASS: {
        pattern: /body\.(dark|light)/gi,
        message: 'No usar body.dark/body.light. Usar html.dark y html.light en su lugar.',
        severity: 'error'
    },

    // Regla 3: Advertir sobre colores hardcodeados
    HARDCODED_COLORS: {
        pattern: /(background|color|border(-color)?)\s*:\s*(#[0-9a-fA-F]{3,6}|rgb|rgba|hsl|hsla)\(/gi,
        message: 'Considerar usar variables CSS (--color-*) en lugar de colores hardcodeados.',
        severity: 'warning'
    },

    // Regla 4: Verificar que existan ambos modos (dark y light)
    MISSING_THEME_MODE: {
        checkFunction: (content) => {
            const hasDark = /html\.dark/gi.test(content);
            const hasLight = /html\.light/gi.test(content);

            if (hasDark && !hasLight) {
                return 'Falta definición de html.light (solo tiene html.dark)';
            }
            if (hasLight && !hasDark) {
                return 'Falta definición de html.dark (solo tiene html.light)';
            }
            return null;
        },
        severity: 'warning'
    }
};

// ═══════════════════════════════════════════════════════════════════
// VALIDACIÓN
// ═══════════════════════════════════════════════════════════════════

function validateFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const errors = [];
    const warnings = [];

    // Validar reglas con patrón
    Object.entries(RULES).forEach(([ruleName, rule]) => {
        if (rule.pattern) {
            const matches = content.matchAll(rule.pattern);
            for (const match of matches) {
                const lines = content.substring(0, match.index).split('\n');
                const lineNumber = lines.length;
                const columnNumber = lines[lines.length - 1].length + 1;

                const issue = {
                    file: filePath,
                    line: lineNumber,
                    column: columnNumber,
                    rule: ruleName,
                    message: rule.message,
                    snippet: match[0]
                };

                if (rule.severity === 'error') {
                    errors.push(issue);
                } else {
                    warnings.push(issue);
                }
            }
        }

        // Validar reglas con función custom
        if (rule.checkFunction) {
            const result = rule.checkFunction(content);
            if (result) {
                const issue = {
                    file: filePath,
                    line: 1,
                    column: 1,
                    rule: ruleName,
                    message: result,
                    snippet: ''
                };

                if (rule.severity === 'error') {
                    errors.push(issue);
                } else {
                    warnings.push(issue);
                }
            }
        }
    });

    return { errors, warnings };
}

// ═══════════════════════════════════════════════════════════════════
// FORMATEO DE RESULTADOS
// ═══════════════════════════════════════════════════════════════════

function formatIssue(issue, type) {
    const emoji = type === 'error' ? '❌' : '⚠️';
    const color = type === 'error' ? '\x1b[31m' : '\x1b[33m';
    const reset = '\x1b[0m';

    let output = `${color}${emoji} ${issue.file}:${issue.line}:${issue.column}${reset}\n`;
    output += `   ${issue.message}\n`;
    if (issue.snippet) {
        output += `   ${color}Encontrado: ${issue.snippet}${reset}\n`;
    }
    output += `   Regla: ${issue.rule}\n`;

    return output;
}

// ═══════════════════════════════════════════════════════════════════
// MAIN
// ═══════════════════════════════════════════════════════════════════

async function main() {

    // Buscar todos los archivos CSS
    const cssFiles = await glob('**/*.css', {
        ignore: ['node_modules/**', 'vendor/**', 'dist/**', 'build/**'],
        cwd: process.cwd()
    });

    let totalErrors = 0;
    let totalWarnings = 0;

    // Validar cada archivo
    for (const file of cssFiles) {
        const { errors, warnings } = validateFile(file);

        if (errors.length > 0 || warnings.length > 0) {
            errors.forEach(error => {
                console.log(formatIssue(error, 'error'));
                totalErrors++;
            });

            warnings.forEach(warning => {
                console.log(formatIssue(warning, 'warning'));
                totalWarnings++;
            });
        }
    }

    // Resumen
}

// Ejecutar
if (require.main === module) {
    main().catch(error => {
        console.error('Error ejecutando validador:', error);
        process.exit(1);
    });
}

module.exports = { validateFile, RULES };
