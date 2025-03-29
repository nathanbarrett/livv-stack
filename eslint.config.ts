import eslint from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier';
import eslintPluginVue from 'eslint-plugin-vue';
import globals from 'globals';
import typescriptEslint from 'typescript-eslint';

export default typescriptEslint.config(
    { ignores: ['*.d.ts', '**/coverage', '**/dist'] },
    {
        extends: [
            eslint.configs.recommended,
            ...typescriptEslint.configs.recommended,
            ...eslintPluginVue.configs['flat/recommended'],
        ],
        files: ['**/*.{ts,vue}'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: globals.browser,
            parserOptions: {
                parser: typescriptEslint.parser,
            },
        },
        rules: {
            'vue/no-unused-vars': 'error',
            'vue/multi-word-component-names': 'off',
        },
    },
    eslintConfigPrettier
);

// module.exports = {
//     extends: [
//         'eslint:recommended',
//         'plugin:vue/vue3-recommended',
//         'plugin:@typescript-eslint/recommended',
//     ],
//     parser: 'vue-eslint-parser',
//     parserOptions: {
//         parser: '@typescript-eslint/parser',
//         sourceType: 'module'
//     },
//     plugins: ['@typescript-eslint'],
//     rules: {
//         'vue/no-unused-vars': 'error',
//         'vue/multi-word-component-names': 'off',
//     }
// }
