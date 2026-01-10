<script setup lang="ts">
import { computed } from 'vue'
import AppLayout from '@js/Pages/AppLayout.vue'
import AiChatBox from '@js/components/chat/AiChatBox.vue'
import { usePage } from '@inertiajs/vue3'
import type { AppPageProps } from '@js/contracts/inertia'

const page = usePage<AppPageProps>()
const user = computed(() => page.props.auth.user)
</script>

<template>
  <AppLayout>
    <v-container
      fluid
      class="pa-0"
    >
      <template v-if="!user">
        <div class="d-flex justify-center align-center fill-height">
          <v-alert
            type="warning"
            variant="tonal"
            class="ma-4"
          >
            Please log in to access AI Chat.
          </v-alert>
        </div>
      </template>

      <AiChatBox v-else />
    </v-container>
  </AppLayout>
</template>

<style scoped>
.fill-height {
  height: calc(100vh - 64px);
  min-height: 500px;
}
</style>
