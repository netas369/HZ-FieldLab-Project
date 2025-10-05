// frontend/eslint.config.js
import js from "@eslint/js"
import pluginVue from "eslint-plugin-vue"
import prettier from "eslint-config-prettier"

export default [
    js.configs.recommended,
    ...pluginVue.configs["flat/recommended"],
    prettier,

    {
        files: ["**/*.vue", "**/*.js", "**/*.ts"],
        languageOptions: {
            globals: {
                window: "readonly",
                document: "readonly",
                console: "readonly"
            }
        },
        rules: {
            "no-unused-vars": ["warn", { argsIgnorePattern: "^_" }],
            "no-console": "warn",
            "vue/multi-word-component-names": "off",
            "vue/no-mutating-props": "warn",
            "vue/no-v-html": "off"
        }
    }
]
