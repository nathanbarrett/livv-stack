import { reactive } from 'vue';

export type SnackbarColor = "success" | "error" | "warning" | "info";
// @see https://github.com/vuetifyjs/vuetify/blob/master/packages/vuetify/src/util/anchor.ts#L8-L14
export type SnackbarLocation = "bottom center" | "bottom left" | "bottom right" | "top center" | "top left" | "top right";
export const snackbarStore = reactive({
    show: false,
    message: '',
    color: 'info' as SnackbarColor,
    location: 'bottom center' as SnackbarLocation,
    showSnackbar: (message: string, color: SnackbarColor, options: GlobalSnackbarOptions) => {
        snackbarStore.message = message;
        snackbarStore.color = color;
        if (options.location) {
            snackbarStore.location = options.location;
        }
        snackbarStore.show = true;
    },
    error: (message: string, options: GlobalSnackbarOptions) => snackbarStore.showSnackbar(message, 'error', options),
    success: (message: string, options: GlobalSnackbarOptions) => snackbarStore.showSnackbar(message, 'success', options),
    info: (message: string, options: GlobalSnackbarOptions) => snackbarStore.showSnackbar(message, 'info', options),
    warning: (message: string, options: GlobalSnackbarOptions) => snackbarStore.showSnackbar(message, 'warning', options),
});

export interface GlobalSnackbarOptions {
    location?: SnackbarLocation;
}

export function error(message: string, options: GlobalSnackbarOptions = {}): void {
    snackbarStore.error(message, options);
}

export function success(message: string, options: GlobalSnackbarOptions = {}): void {
    snackbarStore.success(message, options);
}

export function info(message: string, options: GlobalSnackbarOptions = {}): void {
    snackbarStore.info(message, options);
}

export function warning(message: string, options: GlobalSnackbarOptions = {}): void {
    snackbarStore.warning(message, options);
}
