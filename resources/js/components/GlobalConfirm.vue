<script setup lang="ts">
import { confirmStore } from "@js/common/confirm";
</script>

<template>
  <v-dialog
    v-model="confirmStore.show"
    persistent
    max-width="500"
  >
    <v-card>
      <v-card-title
        v-if="confirmStore.title"
        class="headline"
      >
        {{ confirmStore.title }}
      </v-card-title>
      <v-card-text>
        {{ confirmStore.message }}
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn
          variant="outlined"
          :color="confirmStore.cancelButtonColor || 'default'"
          @click="confirmStore.cancel()"
        >
          {{ confirmStore.cancelButtonText || 'Cancel' }}
        </v-btn>
        <v-btn
          variant="outlined"
          :color="confirmStore.confirmButtonColor || 'info'"
          @click="confirmStore.confirm()"
        >
          {{ confirmStore.confirmButtonText || 'OK' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped lang="css">

</style>
