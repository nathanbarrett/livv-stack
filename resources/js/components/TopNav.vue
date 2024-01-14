<script setup lang="ts">
import { computed } from "vue";
import { useTheme } from "vuetify";
import { User } from "@js/contracts/models";
import { usePage, router } from "@inertiajs/vue3";

const theme = useTheme();
const user = computed<User|null>(() => usePage().props.auth.user);
const appName = computed<string>(() => usePage().props.appName as string);
const isDarkTheme = computed<boolean>(() => theme.global.name.value === "LivvDarkTheme");

function toggleTheme(): void {
    const currentTheme = theme.global.name.value;
    theme.global.name.value = currentTheme === "LivvDarkTheme" ? "LivvLightTheme" : "LivvDarkTheme";
}
</script>

<template>
  <v-app-bar color="primary">
    <v-app-bar-nav-icon
      id="appBarIcon"
      icon="mdi-menu"
    />
    <v-menu
      activator="#appBarIcon"
      :close-on-content-click="false"
    >
      <v-list>
        <v-list-item
          prepend-icon="mdi-theme-light-dark"
          @click="toggleTheme"
        >
          Switch to {{ isDarkTheme ? "light" : "dark" }} theme
        </v-list-item>
        <v-list-item>Go to about</v-list-item>
        <v-list-item>Go to contact</v-list-item>
      </v-list>
    </v-menu>
    <v-app-bar-title>{{ appName }}</v-app-bar-title>
    <v-spacer />
    <v-btn
      v-if="!user"
      variant="outlined"
      size="small"
      @click="router.visit('/register')"
    >
      Register
    </v-btn>
    <v-btn
      v-if="!user"
      id="loginButton"
      variant="plain"
      size="small"
    >
      Login
    </v-btn>
    <v-btn
      v-if="user"
      variant="plain"
      size="small"
      href="/logout"
    >
      Logout
    </v-btn>
  </v-app-bar>
</template>

<style scoped lang="css">

</style>
