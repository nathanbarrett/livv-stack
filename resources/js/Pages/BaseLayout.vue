<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue';
import LoginDialog from "@js/components/LoginDialog.vue";
import ResetPasswordDialog from "@js/components/ResetPasswordDialog.vue";
import SessionFlashMessages from "@js/components/SessionFlashMessages.vue";
import GlobalSnackbar from "@js/components/GlobalSnackbar.vue";
import GlobalConfirm from "@js/components/GlobalConfirm.vue";

const appName = computed<string>(() => usePage().props.appName as string);
const csrfToken = computed<string>(() => usePage().props.csrfToken as string);

</script>

<template>
    <Head>
        <meta
            name="csrf-token"
            :content="csrfToken"
        >
        <link rel="icon" href="/favicon.png"
            type="image/png"
        >
        <title>{{ appName }}</title>
    </Head>
    <v-app>
        <slot />
        <LoginDialog />
        <ResetPasswordDialog />
        <SessionFlashMessages />
        <GlobalSnackbar />
        <GlobalConfirm />
    </v-app>
</template>

<style scoped lang="css">

</style>
