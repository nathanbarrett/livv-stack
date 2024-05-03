<script setup lang="ts">
import Layout from "./Layout.vue";
import AvailableComponents from "@js/components/demo/ThemeShowcase.vue";
import { computed, ref } from "vue";
import { User } from "@js/contracts/models";
import { usePage } from "@inertiajs/vue3";
import SnackbarDemo from "@js/components/demo/SnackbarDemo.vue";
import ConfirmDialogDemo from "@js/components/demo/ConfirmDialogDemo.vue";
import SessionFlashNotificationsDemo from "@js/components/demo/SessionFlashNotificationsDemo.vue";

const message = ref<string>("Welcome to the LIVV Stack!");
const user = computed<User|null>(() => usePage().props.auth.user);
</script>

<template>
  <Layout>
    <v-container>
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="12"
          class="d-flex flex-column align-center justify-center mt-10"
        >
          <h2>{{ message }}</h2>
          <h4 v-if="user">
            You are logged in as {{ user.email }}
          </h4>
          <h4 v-else>
            You are not logged in
          </h4>
        </v-col>
      </v-row>
        <v-row>
            <v-col cols="12" class="text-center">
                <h3>Built In Features</h3>
            </v-col>
            <v-col cols="12" md="6">
                <SnackbarDemo />
                <SessionFlashNotificationsDemo class="mt-10" />
            </v-col>
            <v-col cols="12" md="6">
                <ConfirmDialogDemo />
            </v-col>
        </v-row>
      <AvailableComponents />
    </v-container>
  </Layout>
</template>
