import { User } from "./models";
// import { usePage } from "@inertiajs/vue3";
import { Page, PageProps } from "@inertiajs/core";
import {ServerFlashMessage} from "@js/contracts/session-flash-messages";
// import { get } from "lodash";

/** Keep AppPageProps in sync with HandleInertiaRequests.php */
export interface AppPageProps extends PageProps {
    appName: string;
    stripePublicKey: string;
    csrfToken: string;
    "auth.user": User|null;
    "flash.success": ServerFlashMessage;
    "flash.error": ServerFlashMessage;
    "flash.info": ServerFlashMessage;
    passwordResetToken: string|null;
    passwordResetEmail: string|null;
}

// export function getAppProp<K extends keyof AppPageProps>(prop: K): AppPageProps[K] {
//     return get((usePage() as Page<AppPageProps>).props, prop);
// }
