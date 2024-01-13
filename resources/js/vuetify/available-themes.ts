import { ThemeDefinition } from "vuetify";
import colors from 'vuetify/util/colors';

export const LivvDarkTheme: ThemeDefinition = {
    dark: true,
    colors: {
        background: colors.grey.darken3,
        surface: colors.grey.darken4,
        primary: colors.indigo.darken1,
        secondary: colors.amber.darken1,
        info: colors.lightBlue.lighten1,
        accent: colors.teal.darken2,
    }
}

export const LivvLightTheme: ThemeDefinition = {
    dark: false,
    colors: {
        background: colors.grey.lighten3,
        surface: colors.grey.lighten4,
        primary: colors.indigo.lighten1,
        secondary: colors.amber.lighten1,
        info: colors.lightBlue.lighten1,
        accent: colors.teal.lighten2,
    }
}
