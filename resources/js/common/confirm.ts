import { reactive } from "vue";

export type ConfirmResolver = (value: boolean) => void;

export interface ConfirmOptions {
    title?: string;
    message: string;
    confirmButtonText?: string;
    cancelButtonText?: string;
}
export const confirmStore = reactive({
    show: false,
    title: '' satisfies string,
    message: '' satisfies string,
    cancelButtonText: 'Cancel' satisfies string,
    confirmButtonText: 'OK' satisfies string,
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
        if (options.cancelButtonText) {
            confirmStore.cancelButtonText = options.cancelButtonText;
        }
        confirmStore.showConfirm(options.message, options.title || '');
    }
});

export function confirmDialog(confirmOptions: ConfirmOptions): Promise<boolean> {
    return new Promise((resolve) => {
        confirmStore.awaitConfirm(resolve, confirmOptions);
    })
}
