<script setup lang="ts">
import { snackbarStore } from "@js/common/snackbar";

</script>

<template>
  <v-snackbar
    v-model="snackbarStore.show"
    :color="snackbarStore.color"
    :location="snackbarStore.location"
    :multi-line="true"
  >
    {{ snackbarStore.message }}
  </v-snackbar>
</template>
