import { reactive } from "vue";

export type ConfirmResolver = (value: boolean) => void;

/**
 * Colors can be any valid CSS hex or rgb color OR one of the following:
 * primary, secondary, success, info, warning, error, default
 */
export interface ConfirmOptions {
    title?: string;
    message: string;
    confirmButtonText?: string;
    confirmButtonColor?: string;
    cancelButtonText?: string;
    cancelButtonColor?: string;
}
export const confirmStore = reactive({
    show: false,
    title: '' satisfies string,
    message: '' satisfies string,
    cancelButtonText: 'Cancel' satisfies string,
    cancelButtonColor: 'default' satisfies string,
    confirmButtonText: 'OK' satisfies string,
    confirmButtonColor: 'info' satisfies string,
    resolver: null as ConfirmResolver|null,
    showConfirm: (message: string, title: string = '') => {
        confirmStore.title = title;
        confirmStore.message = message;
        confirmStore.show = true;
    },
    hideConfirm: () => {
        if (confirmStore.resolver) {
            confirmStore.resolver(false);
        }
        confirmStore.show = false;
    },
    cancel: () => {
        if (confirmStore.resolver) {
            confirmStore.resolver(false);
        }
        confirmStore.hideConfirm();
    },
    confirm: () => {
        if (confirmStore.resolver) {
            confirmStore.resolver(true);
        }
        confirmStore.hideConfirm();
    },
    awaitConfirm: (resolve: ConfirmResolver, options: ConfirmOptions) => {
        confirmStore.resolver = resolve;
        if (options.confirmButtonText) {
            confirmStore.confirmButtonText = options.confirmButtonText;
        }
        if (options.confirmButtonColor) {
            confirmStore.confirmButtonColor = options.confirmButtonColor;
        }
        if (options.cancelButtonText) {
            confirmStore.cancelButtonText = options.cancelButtonText;
        }
        if (options.cancelButtonColor) {
            confirmStore.cancelButtonColor = options.cancelButtonColor;
        }
        confirmStore.showConfirm(options.message, options.title || '');
    }
});

export function confirmDialog(confirmOptions: ConfirmOptions): Promise<boolean> {
    return new Promise((resolve) => {
        confirmStore.awaitConfirm(resolve, confirmOptions);
    })
}
