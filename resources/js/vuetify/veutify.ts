import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { aliases, mdi } from 'vuetify/iconsets/mdi'
import { LivvDarkTheme, LivvLightTheme } from "@js/vuetify/available-themes";
export const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'LivvLightTheme',
        themes: {
            LivvDarkTheme,
            LivvLightTheme,
        }
    },
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
});
