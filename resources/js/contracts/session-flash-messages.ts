
export interface FlashMessageConfig {
    message: string;
    timeout?: number;
    color?: string;
}

export type ServerFlashMessage = string | null | FlashMessageConfig;

export interface ServerFlashMessages {
    success: ServerFlashMessage;
    warning: ServerFlashMessage;
    error: ServerFlashMessage;
    info: ServerFlashMessage;
}
