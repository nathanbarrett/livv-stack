import { reactive } from 'vue';

export type SnackbarColor = "success" | "error" | "warning" | "info";

export const snackbarStore = reactive({
    show: false,
    message: '' satisfies string,
    color: 'info' satisfies SnackbarColor,
    showSnackbar: (message: string, color: SnackbarColor) => {
        snackbarStore.message = message;
        snackbarStore.color = color;
        snackbarStore.show = true;
    },
    error: (message: string) => snackbarStore.showSnackbar(message, 'error'),
    success: (message: string) => snackbarStore.showSnackbar(message, 'success'),
    info: (message: string) => snackbarStore.showSnackbar(message, 'info'),
    warning: (message: string) => snackbarStore.showSnackbar(message, 'warning'),
});

export function error(message: string): void {
    snackbarStore.error(message);
}

export function success(message: string): void {
    snackbarStore.success(message);
}

export function info(message: string): void {
    snackbarStore.info(message);
}

export function warning(message: string): void {
    snackbarStore.warning(message);
}
