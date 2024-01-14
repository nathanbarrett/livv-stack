<script setup lang="ts">
import Layout from "./Layout.vue";
import AvailableComponents from "@js/components/AvailableComponents.vue";
import { computed, ref } from "vue";
import { User } from "@js/contracts/models";
import { usePage } from "@inertiajs/vue3";

const message = ref<string>("Hello LIVV Stack!");
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
      <AvailableComponents />
    </v-container>
  </Layout>
</template>
