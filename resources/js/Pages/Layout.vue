<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue';
import TopNav from "@js/components/TopNav.vue";
import SessionFlashMessages from "@js/components/SessionFlashMessages.vue";
import LoginDialog from "@js/components/LoginDialog.vue";
import ResetPasswordDialog from "@js/components/ResetPasswordDialog.vue";
import GlobalSnackbar from "@js/components/GlobalSnackbar.vue";
import GlobalConfirm from "@js/components/GlobalConfirm.vue";

const appName = computed<string>(() => usePage().props.appName as string);
const csrfToken = computed<string>(() => usePage().props.csrfToken as string);


</script>

<template>
  <div>
    <Head>
      <meta
        name="csrf-token"
        :content="csrfToken"
      >
      <link
        rel="icon"
        href="/favicon.png"
        type="image/png"
      >
      <title>{{ appName }}</title>
    </Head>
    <v-app>
      <TopNav />
      <div class="app-body">
        <slot />
      </div>
      <LoginDialog />
      <ResetPasswordDialog />
      <SessionFlashMessages />
      <GlobalSnackbar />
      <GlobalConfirm />
    </v-app>
  </div>
</template>

<style lang="css" scoped>
.app-body {
    margin-top: 64px;
}
</style>
